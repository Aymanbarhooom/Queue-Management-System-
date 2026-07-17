<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserService;
use App\Models\Wallet;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Hash the password before database insertion
        $data['password'] = Hash::make($data['password']);

        $user = User::create([
            ...$data,
            'role' => 'user',
        ]);

        Wallet::create(['user_id' => $user->id, 'balance' => 1000]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $data = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->apiResponse($data, 'User created successfully', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($data)) {
            return $this->apiError('Invalid credentials.', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        $data = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->apiResponse($data, 'Login successful', 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete;

        return $this->apiResponse(null, 'Logged out successfully.', 200);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->apiResponse($request->user()->load('wallet'), 'User loaded successfully', 200);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $userDevice = UserService::updateOrCreate(
            ['user_id' => auth()->id()],
            ['fcm_token' => $request->fcm_token],
        );

        return $this->apiResponse($userDevice, 'FCM token updated successfully', 200);
    }

    public function updateImage(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profile_images', 'public');
            $data['image'] = $path;
        }

        $user = User::find(auth()->id());
        $user->image = $data['image'];
        $user->save();

        return $this->apiResponse($user, 'Image updated successfully', 200);
    }

    public function updateProfile(Request $request){
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profile_images', 'public');
            $data['image'] = $path;
        }

        $user = User::find(auth()->id());
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->image = $data['image'];
        $user->save();
        return $this->apiResponse($user, 'User updated successfully', 200);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return $this->apiError('Current password is incorrect.', 422);
        }

        $user->update([
            'password' => $data['new_password'],
        ]);

        return $this->apiResponse(null, 'Password updated successfully', 200);
    }
}
