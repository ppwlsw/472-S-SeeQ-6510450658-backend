<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\DecryptResource;
use App\Http\Resources\LoginResource;
use App\Http\Resources\RedirectResource;
use App\Mail\UserVerificationEmail;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function login(LoginRequest $request)
    {
        $email = strtolower($request->email);
        $password = $request->password;
        $user = $this->userRepository->getByEmail($email);

        if (!$user || (!$user->login_by == 'default') || !$user->email_verified_at) {
            return response()->json([
                'error' => 'User not found or not verified'
            ])->setStatusCode(404);
        }

        if (Hash::check($password, $user->password)) {
            $token = Crypt::encrypt($user->createToken('token')->plainTextToken);
            return LoginResource::make((object)
            [
                'token' => $token,
                'id' => $user->id,
                'role' => $user->role,
            ]
            )->response()->setStatusCode(201);
        }

        return response()->json([
            'error' => 'Invalid credentials'
        ])->setStatusCode(401);
    }

    public function register(RegisterRequest $request)
    {
        if ($this->userRepository->getByEmail(strtolower($request->email))) {
            return response()->json([
                'error' => 'Email already exists'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            $user = $this->userRepository->create([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'CUSTOMER',
            ]);

            Mail::to($user->email)->send(new UserVerificationEmail($user));
        });

        return response()->json(null, 201);
    }

    public function redirectToGoogle(Request $request)
    {
        return RedirectResource::make((object)
        [
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
        ]
        )->response()->setStatusCode(200);
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = $this->userRepository->updateOrCreate(
                [
                    'email' => $googleUser->email
                ],
                [
                    'name' => $googleUser->name,
                    'password' => '',
                    'image_url' => $googleUser->avatar,
                    'login_by' => 'google',
                    'role' => 'CUSTOMER',
                    'email_verified_at' => now(),
                ]
            );
            $token = Crypt::encrypt($user->createToken('token')->plainTextToken);

            return redirect()->away(env("CUSTOMER_FRONTEND_URL") . "/login?token=" . $token, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Google login failed'], 500);
        }
    }

    public function decrypt(Request $request)
    {
        return DecryptResource::make((object)
        [
            'plain_text' => Crypt::decrypt($request->encrypted)
        ]
        )->response()->setStatusCode(201);
    }

    public function verify(User $user, string $token)
    {
        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        if (!hash_equals($token, sha1($user->email)) || $user->email_verified_at) {
            return view('emails.verifystatus', [
                'status' => 'reject',
                'path_link' => url(env("{$user->role}_FRONTEND_URL") . '/login')]);
        }

        $user->email_verified_at = now();
        $user->save();

        return view('emails.verifystatus', ['status' => 'success', 'path_link' => url(env("{$user->role}_FRONTEND_URL") . '/login')]);
    }
}
