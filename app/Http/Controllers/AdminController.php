<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Expert; // Added for listing expert requests
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Créer un nouvel administrateur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom'      => 'required|string',
            'prenom'   => 'required|string',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'email'    => $request->email,
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
        \Log::info('Admin auth check:', ['user' => auth('admin')->user()]);
        return response()->json(auth('admin')->user());
    }

    /**
     * (ADMIN) Liste toutes les demandes d'expertise en attente.
     */
    public function listPendingExpertRequests()
    {
        \Log::info('Admin auth check in listPendingExpertRequests:', ['user' => auth('admin')->user()]);
        return response()->json(Expert::where('status', 'pending')->get());
    }
    



    /**
     * (ADMIN) Accept an expert role request.
     */
    public function acceptExpertRequest(Request $request, $requestId)
    {
        $expertRequest = \App\Models\Expert::find($requestId);
        if (!$expertRequest) {
            return response()->json(['message' => 'Expert request not found'], 404);
        }
        if ($expertRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 400);
        }
        $expertRequest->status = 'accepted';
        $expertRequest->save();

        // Notify the utilisateur
        $utilisateur = \App\Models\Utilisateur::find($expertRequest->ref_id_utilisateur);
        if ($utilisateur) {
            $utilisateur->addNotification("Votre demande pour devenir expert a été acceptée.");
            // Optionally, update the utilisateur record to reflect expert status
        }

        return response()->json([
            'message' => 'Expert request accepted',
            'expertRequest' => $expertRequest
        ]);
    }

    /**
     * (ADMIN) Reject an expert role request.
     */
    public function rejectExpertRequest(Request $request, $requestId)
    {
        $expertRequest = \App\Models\Expert::find($requestId);
        if (!$expertRequest) {
            return response()->json(['message' => 'Expert request not found'], 404);
        }
        if ($expertRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 400);
        }
        $expertRequest->status = 'rejected';
        $expertRequest->save();

        // Notify the utilisateur
        $utilisateur = \App\Models\Utilisateur::find($expertRequest->ref_id_utilisateur);
        if ($utilisateur) {
            $utilisateur->addNotification("Votre demande pour devenir expert a été rejetée.");
        }

        return response()->json([
            'message' => 'Expert request rejected',
            'expertRequest' => $expertRequest
        ]);
    }

}
