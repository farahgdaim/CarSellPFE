<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Créer un nouvel administrateur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Admin créé avec succès', 'admin' => $admin], 201);
    }

    /**
     * Récupérer la liste des admins.
     */
    public function index()
    {
        return response()->json(Admin::all());
    }

    /**
     * Récupérer un admin spécifique.
     */
    public function show($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin non trouvé'], 404);
        }
        return response()->json($admin);
    }

    /**
     * Supprimer un admin.
     */
    public function destroy($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin non trouvé'], 404);
        }
        $admin->delete();
        return response()->json(['message' => 'Admin supprimé avec succès']);
    }

    /**
     * Connexion d'un administrateur.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Tente de générer un token via le guard 'admin'
        if (!$token = auth('admin')->attempt($credentials)) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        return $this->respondWithToken($token);
    }

    

    /**
     * Retourne la structure du token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('admin')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Déconnexion de l'admin.
     */
    public function logout(Request $request)
    {
        auth('admin')->logout();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Récupère les informations de l'admin connecté.
     */
    public function me()
    {
        return response()->json(auth('admin')->user());
    }
}