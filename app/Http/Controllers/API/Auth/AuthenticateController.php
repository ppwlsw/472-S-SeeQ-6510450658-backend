<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthenticateController extends Controller
{

    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function login(LoginRequest $request) {
        $email = $request->email;
        $password = $request->password;
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ])->setStatusCode(404);
        }
        if (Hash::check($password, $user->password)) {
            return response()->json([
                'token' => $user->createToken('token')->plainTextToken
            ]);
        }
        return response()->json([
            'message' => 'Invalid credentials'
        ])->setStatusCode(401);
    }

    public function register(RegisterRequest $request) {
        $user = $this->userRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'phone' => $request->user_phone,
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $file = $request->image;
            $filename = now()->format('Y-m-d H:i:s.u') . '.png';
            $path = 'user_images/'. $user->id .'/'. $filename;
            Storage::disk('s3')->put($path, file_get_contents($file), 'private');
        }
        $this->userRepository->update(['image_url' => $path], $user->id);
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
}
