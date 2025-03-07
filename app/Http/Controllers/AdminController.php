<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Annonce;
use App\Models\Utilisateur;
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
            'token_type' => 'bearer',
            'expires_in' => 3600
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

    public function getReportedAnnonces()
    {
        $reportedAnnonces = Annonce::where('is_reported', true)->get();
        return response()->json([
            'status' => 200,
            'data' => $reportedAnnonces
        ]);
    }

    public function validateAnnonce($annonceId)
    {
        $annonce = Annonce::find($annonceId);
        if(!$annonce){
            return response()->json([
                'status' => 404,
                'data' => 'annonce introuvable'
            ]);
        }
        $annonce->reported_by = [];
        $annonce->is_reported = false;
        $annonce->supervise_par = auth()->_id;  
        $annonce->save();
        return response()->json([
            'status' => 200,
            'data' => 'annonce validée (signalements retirés)'
        ]);
    }

    public function deleteReportedAnnonce($annonceId){
        $annonce = Annonce::find($annonceId);
        if(!$annonce){
            return response()->json([
                'status' => 404,
                'data' => 'annonce introuvable'
            ]);
        }
        $userId = $annonce->Ref_user_id;
        $annonce->delete();
        $this->warnUser($userId);
        return response()->json([
            'status' => 200,
            'data' => 'annonce supprimée (signalement retiré)'
        ]);
    }

    public function warnUser($userId)
    {
        $user = Utilisateur::find($userId);
        if(!$user){
            return response()->json([
                'status'=>404,
                'data' => 'utilisateur introuvable'
            ]);
        $currentWarnings = $user->warnings_count ?? 0;
        $user->warnings_count = $currentWarnings + 1;
        $user->save();
        $user->addNotification("Votre annonce a été signalée trop souvent. Vous avez maintenant ". $user->warnings_count. " signalement(s).");
        }
        if($user->warnings_count >=3){
            $user->delete();
            return response()->json([
                'status'=>403,
                'data' => 'Utilisateur supprimé (3 signalements détectés)'
            ]);
        }
        return response()->json([
            'status'=>200,
            'data' => 'Utilisateur averti (warning #' . $user->warnings_count . ')'
        ]);
    }
}