<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Mockery\Generator\StringManipulation\Pass\Pass;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'User created successfully.',
            'user' => new UserResource($user)
        ], 201);
    }
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $authenticated = Auth::attempt($data);

        if (!$authenticated) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth-token');

        return response()->json([
            'message' => 'User logged in successfully.',
            'user' => new UserResource($user),
            'token' => $token->plainTextToken
        ], 200);
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        $token->delete();

        return response()->json([
            'message' => 'User logged out successfully.',
        ], 200);
    }
    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ], 200);
    }
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.'
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 200);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully.'
        ], 200);
    }
    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 409);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent successfully.'
        ], 200);
    }
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $email = $request->validated('email');

        Password::sendResetLink([
            'email' => $email
        ]);

        return response()->json([
            'message' => 'Reset password link sent successfully.'
        ], 200);
    }
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->validated(),
            function ($user, $password) {
                $user->update([
                    'password' => $password,
                ]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset failed.'
            ], 400);
        }

        return response()->json([
            'message' => 'Password reset successfully.'
        ], 200);
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        $currentPassword = $request->validated('current_password');

        if (! Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        $user->update([
            'password' => $request->validated('password'),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.'
        ], 200);
    }
}
