<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class ShopAuthController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    public function login(LoginRequest $request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = $this->userRepository->getByEmail($email);
        if (!$user || !$user->isShop()) {
            return response()->json([
                'error' => 'Shop not found or not verified'
            ])->setStatusCode(404);
        }

        if (Hash::check($password, $user->password)) {
            return LoginResource::make( (object)
                [
                    'token' => Crypt::encrypt($user->createToken('token')->plainTextToken),
                    'id' => $user->shops()->first()->id,
                ]
            )->response()->setStatusCode(201);
        }

        return response()->json([
            'error' => 'Invalid credentials'
        ], 401);
    }

    public function verify(int $id, String $token)
    {
        $user = $this->userRepository->getById($id);

        if (!hash_equals($token, sha1($user->email)) || $user->email_verified_at) {
            return  view('emails.shop.verifystatus', ['status' => 'reject', 'path_link' => url(env('APP_SHOP_URL') . 'dashboard/shop')]);
        }

        $user->email_verified_at = now();
        $user->role = 'SHOP';
        $user->save();

        return view('emails.shop.verifystatus', ['status' => 'success', 'path_link' => url(env('APP_SHOP_URL') . 'dashboard/shop')]);
    }
}
