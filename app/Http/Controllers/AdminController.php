<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Admin;
use App\Models\Annonce;
use App\Models\Expert; 
use App\Http\Controllers\NotificationController;

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
        $annonce->supervise_par = auth()->id();  
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
    
        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'status' => 404,
                'data' => 'Utilisateur introuvable'
            ]);
        }
    
        // Incrémenter le nombre d'avertissements
        $currentWarnings = $user->warnings_count ?? 0;
        $user->warnings_count = $currentWarnings + 1;
        $user->save();
    
        // Si l'utilisateur atteint 3 avertissements, on supprime son compte
        if ($user->warnings_count >= 3) {
            $user->delete();
            return response()->json([
                'status' => 403,
                'data' => 'Utilisateur supprimé (3 avertissements détectés)'
            ]);
        }
    
        
        $annonce = Annonce::where('user_id', $user->id)->latest()->first();
        $titreAnnonce = $annonce ? $annonce->title : "une annonce";
    
        // Création de la requête pour NotificationController
        $notificationController = new NotificationController();
        $request = new Request();
        $request->merge([
            'user_id' => $user->id,
            'contenu' => "Votre annonce '$titreAnnonce' a été signalée trop souvent. Vous avez maintenant " .
                          $user->warnings_count . " avertissement(s).",
            'statut' => 'non-lu', 
        ]);
    
        // Appeler la méthode store du NotificationController
        return $notificationController->store($request);
    }

    /**
     * (ADMIN) Liste toutes les demandes d'expertise en attente.
     */
    public function listPendingExpertRequests()
    {
        return response()->json(Expert::where('status', 'pending')->get());
    }

    /**
     * (ADMIN) Accept an expert role request.
     */
    public function acceptExpertRequest(Request $request, $requestId)
    {
        $expertRequest = Expert::find($requestId);
        if (!$expertRequest) {
            return response()->json(['message' => 'Expert request not found'], 404);
        }
        if ($expertRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 400);
        }
        $expertRequest->status = 'accepted';
        $expertRequest->save();

        // Notify the utilisateur
        $utilisateur = Utilisateur::find($expertRequest->ref_id_utilisateur);
        if ($utilisateur) {
            $this->notifyUtilisateur($utilisateur, "Votre demande pour devenir expert a été acceptée.");
        }

        return response()->json([
            'message'       => 'Expert request accepted',
            'expertRequest' => $expertRequest
        ]);
    }

    /**
     * (ADMIN) Reject an expert role request.
     */
    public function rejectExpertRequest(Request $request, $requestId)
    {
        $expertRequest = Expert::find($requestId);
        if (!$expertRequest) {
            return response()->json(['message' => 'Expert request not found'], 404);
        }
        if ($expertRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been processed'], 400);
        }
        $expertRequest->status = 'rejected';
        $expertRequest->save();

        // Notify the utilisateur
        $utilisateur = Utilisateur::find($expertRequest->ref_id_utilisateur);
        if ($utilisateur) {
            $this->notifyUtilisateur($utilisateur, "Votre demande pour devenir expert a été rejetée.");
        }

        return response()->json([
            'message'       => 'Expert request rejected',
            'expertRequest' => $expertRequest
        ]);
    }

    /**
     * Private helper method to notify a user.
     */
    private function notifyUtilisateur(Utilisateur $utilisateur, string $message, string $statut = 'non_lu')
    {
        $notification = [
            'ref_id_user' => $utilisateur->_id,
            'contenu'     => $message,
            'date'        => now(),
            'statut'      => $statut,
        ];

        $utilisateur->push('notifications', $notification);
    }
}
