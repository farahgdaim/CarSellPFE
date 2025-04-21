<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Admin;
use App\Models\Annonce;
use App\Models\Expert; 
use App\Http\Controllers\NotificationController;
use App\Models\RapportExpertise;
use Illuminate\Support\Facades\Hash;
use LDAP\Result;

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

        return response()->json([
            'status' => 201,
            'data' => $admin
        ]);
    }

    /**
     * Récupérer la liste des admins.
     */
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => Admin::all()
        ]);
        
    }

    /**
     * Récupérer un admin spécifique.
     */
    public function show($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json([
                'status' => 404,
                'data' => 'Admin non trouvé'
            ]);
        }
        return response()->json([
            'status' => 200,
            'data' => $admin
        ]);
        
    }

    /**
     * Supprimer un admin.
     */
    public function destroy($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json([
                'status' => 404,
                'data' => 'Admin non trouvé'
            ]);
            
        }
        $admin->delete();
        return response()->json([
            'status' => 200,
            'data' => 'Admin supprimé avec succès'
        ]);
    }

    /**
     * Connexion d'un administrateur.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('admin')->attempt($credentials)) {
            return response()->json([
                'status' => 401,
                'data' => 'Identifiants invalides'
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
                'expires_in' => 3600
            ]
        ]);
        
    }

    /**
     * Déconnexion de l'admin.
     */
    public function logout(Request $request)
    {
        auth('admin')->logout();
        return response()->json([
            'status' => 200,
            'data' => 'Déconnexion réussie'
        ]);

    }

    /**
     * Récupère les informations de l'admin connecté.
     */
    public function me()
    {
        return response()->json([
            'status' => 200,
            'data' => auth('admin')->user()
        ]);

    }


    public function getReportedAnnonces()
    {
        $reportedAnnonces = Annonce::where('is_reported', true)->get();
        return response()->json([
            'status' => 200,
            'data' => $reportedAnnonces
        ]);
    }
    public function validateReport($reportId)
    {
        $rapport = RapportExpertise::find($reportId);
        if(!$rapport){
            return response()->json([
                'status' => 404,
                'data' => 'rapport introuvable'
            ]);
        }
        $rapport->reported_by = [];
       
        $rapport->is_reported = false;
        $rapport->supervise_par = auth()->id();  
        $rapport->save();
        return response()->json([
            'status' => 200,
            'data' => 'rapport validée (signalements retirés)'
        ]);
    }
    public function deleteReportedRapport($reportId){
        $rapport = RapportExpertise::find($reportId);
        if(!$rapport){
            return response()->json([
                'status' => 404,
                'data' => 'rapport introuvable'
            ]);
        }
        $userId = $rapport->ref_id_expert;
        $rapport->delete();
        $this->warnUser($userId);
        return response()->json([
            'status' => 200,
            'data' => 'rapport supprimée '
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
        if (!$user) {
            return response()->json([
                'status' => 404,
                'data' => 'Utilisateur introuvable'
            ]);
        }

        // Incrémenter le nombre d'avertissements
        $user->warnings_count = ($user->warnings_count ?? 0) + 1;
        $user->save();

        // Si l'utilisateur atteint 3 avertissements, on supprime son compte
        if ($user->warnings_count >= 3) {
            Annonce::where('Ref_id_user', $user->id)->delete();
            $user->delete();
            return response()->json([
                'status' => 403,
                'data' => 'Utilisateur supprimé (3 avertissements détectés)'
            ]);
        }

        $annonce = Annonce::where('user_id', $user->id)->latest()->first();
        $titreAnnonce = $annonce ? $annonce->title : "une annonce";

        // Préparer le message de notification
        $message = "Votre annonce '$titreAnnonce' a été signalée trop souvent. Vous avez maintenant " . $user->warnings_count . " avertissement(s).";

        // Appel de la méthode notifyUser centralisée
        NotificationController::notifyUser($user, $message);

        return response()->json([
            'status' => 200,
            'data' => 'Avertissement envoyé à l\'utilisateur'
        ]);
    }




   

public function listPendingExpertRequests()
{
    $experts = Expert::where('status', 'pending')->get();

    // Ajout manuel des données utilisateur
    $expertsWithUserData = $experts->map(function ($expert) {
        $utilisateur = Utilisateur::find($expert->ref_id_utilisateur);

        return [
            '_id'               => $expert->_id,
            'domaineExpertise'  => $expert->domaineExpertise,
            'anneesExperience'  => $expert->anneesExperience,
            'status'            => $expert->status,
            'certifications'    => $expert->certifications,
            'ref_id_utilisateur'=> $expert->ref_id_utilisateur,
            'utilisateur_nom'   => $utilisateur?->nom ?? '',
            'utilisateur_prenom'=> $utilisateur?->prenom ?? '',
            'utilisateur_tel'=>$utilisateur?->telephone ?? '',
            'utilisateur_email' => $utilisateur?->email ?? '',
            'created_at'        => $expert->created_at,
            'updated_at'        => $expert->updated_at,
        ];
    });

    return response()->json([
        'status' => 200,
        'data'   => $expertsWithUserData
    ]);
}


    /**
     * (ADMIN) Accept an expert role request.
     */
    public function acceptExpertRequest(Request $request, $requestId)
    {
        $expertRequest = Expert::find($requestId);
        if (!$expertRequest) {
            return response()->json([
                'status' => 404,
                'data'   => 'Expert request not found'
            ]);
        }
        if ($expertRequest->status !== 'pending') {
            return response()->json([
                'status' => 400,
                'data'   => 'This request has already been processed'
            ]);
        }
        $expertRequest->status = 'accepted';
        $expertRequest->save();

        // Notify the utilisateur
        $utilisateur = Utilisateur::find($expertRequest->ref_id_utilisateur);
        if ($utilisateur) {
            $message = "Votre demande pour devenir expert a été acceptée.";
            NotificationController::notifyUser($utilisateur, $message);
        }

        return response()->json([
            'status' => 200,
            'data'   => $expertRequest
        ]);
    }

    /**
     * (ADMIN) Reject an expert role request.
     */
    public function rejectExpertRequest(Request $request, $requestId)
    {
        $expertRequest = Expert::find($requestId);
        if (!$expertRequest) {
            return response()->json([
                'status' => 404,
                'data'   => 'Expert request not found'
            ]);
        }
        if ($expertRequest->status !== 'pending') {
            return response()->json([
                'status' => 400,
                'data'   => 'This request has already been processed'
            ]);
        }
        
        // Optionally update the status to 'rejected' before deletion (for logging or future reference)
        $expertRequest->status = 'rejected';
        $expertRequest->save();

        // Notify the utilisateur that the request is rejected
        $utilisateur = Utilisateur::find($expertRequest->ref_id_utilisateur);
        if ($utilisateur) {
            $message = "Votre demande pour devenir expert a été rejetée.";
            NotificationController::notifyUser($utilisateur, $message);
        }
        
        // Remove the expert request from the expert collection
        $expertRequest->delete();

        return response()->json([
            'status' => 200,
            'data'   => 'Expert request rejected and removed successfully.'
        ]);
    }

   
    public function getReportedRapport()
    {
        $reportedRapport = RapportExpertise::where('is_reported', true)->get();
        return response()->json([
            'status' => 200,
            'data' => $reportedRapport
        ]);
    }
    public function getReportedRapportById($id){
        $rapport = RapportExpertise::find($id);
        if (is_null($rapport)) {
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        return response()->json([
            'status' => '200',
            'data' => $rapport
        ]);
    }


}
