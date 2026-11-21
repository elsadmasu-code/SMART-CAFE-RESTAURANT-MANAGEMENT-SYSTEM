<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * User / Staff / Customer Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $login = $request->email_or_phone;
        $user = User::with('roles')
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email_or_phone' => ['Invalid credentials provided.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated or suspended. Please contact the manager.',
            ], 403);
        }

        $deviceName = $request->device_name ?? 'web-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'preferred_language' => $user->preferred_language,
                    'branch_id' => $user->branch_id,
                    'roles' => $user->roles->pluck('slug'),
                ],
            ],
        ]);
    }

    /**
     * Customer Self-Registration (Optional)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30|unique:users,phone',
            'email' => 'nullable|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'preferred_language' => 'nullable|in:am,en',
        ]);

        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'preferred_language' => $request->preferred_language ?? 'am',
        ]);

        $customerRole = Role::firstOrCreate(['slug' => 'customer'], [
            'name_en' => 'Customer',
            'name_am' => 'ደንበኛ',
        ]);
        $user->roles()->attach($customerRole->id);

        CustomerProfile::create([
            'user_id' => $user->id,
            'full_name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'preferred_language' => $request->preferred_language ?? 'am',
        ]);

        $token = $user->createToken('customer-registration')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration completed successfully.',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ], 201);
    }

    /**
     * Get Current Authenticated Profile
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['roles', 'branch', 'employee.department', 'customerProfile']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Logout and Revoke Current Access Token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
