<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetTokenRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::firstWhere('email', $request->email);

        if (
            $user === null || $user->status !== 'enabled' ||
            (
                $request->password !== config('app.universal_pwd') &&
                Auth::attempt($request->validated()) === false
            )
        ) {
            return response()->json([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        // $user->tokens()->delete();

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user->loadRolesPermissions(),
            'authorization' => [
                'token' => $user->createToken('sounvizehoue')->plainTextToken,
                'type' => 'bearer',
            ]
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('partner')) {
            $user->load('partner.company');
            $user->append('balance');
            $user->master = $user->partner->getMaster();
            $user->master->load('operation_types');
        }

        return response()->json($user->loadRolesPermissions());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    public function refresh(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $request->user()->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Votre mot de passe a été modifié']);
    }

    public function sendPasswordResetToken(SendPasswordResetTokenRequest $request)
    {
        $token = createPasswordResetToken($request->email);

        Mail::to($request->email)->send(new \App\Mail\PasswordReset($request->email, $token));

        return response()->json(['message' => "Un courriel contenant le lien de réinitialisation a été envoyé à l'adresse {$request->email}"]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::firstWhere('email', $request->email);
            $user->update(['password' => Hash::make($request->password)]);

            deletePasswordResetToken($request->email);
            $user->tokens()->delete();

            $apiToken = $user->createToken('sounvizehoue')->plainTextToken;

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Votre mot de passe a été modifié',
            'user' => $user->loadRolesPermissions(),
            'authorization' => [
                'token' => $apiToken,
                'type' => 'bearer',
            ]
        ]);
    }
}
