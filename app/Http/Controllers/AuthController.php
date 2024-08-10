<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token, 
                'token_type' => 'Bearer', 
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid login details'], 401);
            }

            return response()->json([
                'access_token' => $token, 
                'token_type' => 'Bearer', 
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            Log::error('JWTException during login: ' . $e->getMessage());
            return response()->json(['message' => 'Could not create token'], 500);
        } catch (\Exception $e) {
            Log::error('Error during login: ' . $e->getMessage());
            return response()->json(['message' => 'Login failed'], 500);
        }
    }

    public function logout()
    {
        try {
            Auth::guard('api')->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return response()->json(['message' => 'Logout failed'], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = Auth::guard('api')->refresh();

            return response()->json([
                'access_token' => $newToken, 
                'token_type' => 'Bearer', 
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            Log::error('JWTException during token refresh: ' . $e->getMessage());
            return response()->json(['message' => 'Could not refresh token'], 500);
        } catch (\Exception $e) {
            Log::error('Error during token refresh: ' . $e->getMessage());
            return response()->json(['message' => 'Token refresh failed'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = Auth::user();

            if (!Hash::check($validated['old_password'], $user->password)) {
                return response()->json(['message' => 'Old password does not match'], 400);
            }

            $user->password = Hash::make($validated['new_password']);
            $user->save();

            return response()->json(['message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            Log::error('Error changing password: ' . $e->getMessage());
            return response()->json(['message' => 'Password change failed'], 500);
        }
    }

    public function me()
    {
        try {
            return response()->json(auth()->user());
        } catch (\Exception $e) {
            Log::error('Error fetching user info: ' . $e->getMessage());
            return response()->json(['message' => 'Could not retrieve user information'], 500);
        }
    }
    public function destroy()
    {
        try {
            $user = Auth::user();
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json(['message' => 'User deletion failed'], 500);
        }
    }
}
