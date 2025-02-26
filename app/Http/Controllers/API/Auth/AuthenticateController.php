<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class AuthenticateController extends Controller
{

    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function login(LoginRequest $request) {
        $email = strtolower($request->email);
        $password = $request->password;
        $user = $this->userRepository->getByEmail($email);
        if (!$user || !$user->email_verified_at) {
            return response()->json([
                'message' => 'User not found'
            ])->setStatusCode(404);
        }

        if (Hash::check($password, $user->password)) {
            $token = Crypt::encrypt($user->createToken('token')->plainTextToken);
            return response()->json([
                'token' => $token
            ])->setStatusCode(201);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ])->setStatusCode(401);
    }

    public function register(RegisterRequest $request) {
        if ($this->userRepository->isEmailExist($request->email)) {
            return response()->json([
                'message' => 'Email already exists'
            ], 422);
        }

        $user = $this->userRepository->create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = now()->format('Y-m-d_H:i:s.u') . '.png';
            $path = 'user_images/'. $user->id .'/'. $filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
            $uri = str_replace('/', '+', $path);
            $user->update([
                'image_url' => env("APP_URL") . 'api/images/' . $uri
            ]);
        }

        event(new Registered($user));

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function redirectToGoogle(Request $request)
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl()
        ]);
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
                    'email_verified_at' => now(),
                ]
            );
            $token = Crypt::encrypt($user->createToken('token')->plainTextToken);

            return redirect()->away(env("USER_FRONT_URL") . "login?token=" . $token, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Google login failed'], 500);
        }
    }

    public function decrypt(Request $request)
    {
        return response()->json(
            [
                'plain_text' => Crypt::decrypt($request->encrypted)
            ]
        , 201);
    }
}
