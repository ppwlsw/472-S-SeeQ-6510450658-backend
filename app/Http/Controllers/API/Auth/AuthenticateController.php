<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticateController extends Controller
{

    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function login(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();
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

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string',
            'user_phone' => 'required|string|max:15',
            'birth_date' => 'required|date',
        ]);

        $user = $this->userRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'gender' => $request->gender,
            'address' => $request->address,
            'user_phone' => $request->user_phone,
            'birth_date' => $request->birth_date,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }
}
