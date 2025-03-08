<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Inscription d'un utilisateur.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string',
            'prenom'    => 'required|string',
            'email'     => 'required|email|unique:utilisateurs,email',
            'password'  => 'required|string|min:6',
            'telephone' => 'required|string',
        ]);

        $utilisateur = Utilisateur::create([
            'nom'             => $request->nom,
            'prenom'          => $request->prenom,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'telephone'       => $request->telephone,
            'dateInscription' => now(),
            'notifications'   => [],
            'paiements'       => []
        ]);

        return response()->json([
            'status' => 201,
            'data' => $utilisateur
        ]);
        
    }

    /**
     * Connexion d'un utilisateur et génération d'un token JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = auth()->attempt($credentials)) {
                return response()->json([
                    'status' => 401,
                    'data' => 'Identifiants invalides'
                ]);

            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 500,
                'data' => 'Impossible de créer le token'
            ]);
                    
        }

        return $this->respondWithToken($token);
    }

    /**
     * Retourne la structure du token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 200,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ]);        
    }
    
    /**
     * Déconnexion (invalidation du token).
     */
    public function logout(Request $request)
    {
        auth()->logout();
        return response()->json([
            'status' => 200,
            'data' => 'Déconnexion réussie'
        ]);

    }

    /**
     * Récupérer l'utilisateur authentifié.
     */
    public function me()
    {
        return response()->json([
            'status' => 200,
            'data' => auth()->user()
        ]);        
    }
}
