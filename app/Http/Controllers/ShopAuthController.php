<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\ShopRepository;
use Illuminate\Support\Facades\Hash;

class ShopAuthController extends Controller
{
    public function __construct(
        private ShopRepository $shopRepository
    ) {}

    public function login(LoginRequest $request)
    {
        $email = $request->email;
        $password = $request->password;

        $shop = $this->shopRepository->getByEmail($email);
        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found'
            ])->setStatusCode(404);
        }

        if (Hash::check($password, $shop->password)) {
            return response()->json([
                'token' => $shop->createToken('shop-token')->plainTextToken
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }
}
