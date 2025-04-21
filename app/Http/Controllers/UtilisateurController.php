<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Utilisateur;
use App\Models\DemandeEvaluation;
use App\Models\RapportExpertise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash; // For password hashing

class UtilisateurController extends Controller
{
    /**
     * L'utilisateur connecté fait une demande pour devenir expert
     * en fournissant son fichier PDF de certification, son domaine d'expertise et ses années d'expérience.
     */
    public function requestExpertRole(Request $request)
    {
        $user = auth()->user();

        // Check if an expert request already exists for this user.
        $existingExpert = Expert::where('ref_id_utilisateur', $user->_id)->first();
        if ($existingExpert) {
            return response()->json([
                'status' => 400,
                'data'   => 'Une demande est déjà en cours ou vous êtes déjà expert.'
            ]);
        }

        // Validate the incoming request.
        $validatedData = $request->validate([
            'certification'    => 'required|file|mimes:pdf|max:2048', // max 2MB for certification
            'domaineExpertise' => 'required|string',
            'anneesExperience' => 'required|integer|min:0'
        ]);

        // Store the uploaded certification PDF in the "cert" folder.
        $file = $request->file('certification');
        $path = $file->store('cert', 'public');

        // Create the expert request.
        $expert = Expert::create([
            'ref_id_utilisateur' => $user->_id,
            'certifications'     => [$path],
            'domaineExpertise'   => $validatedData['domaineExpertise'],
            'anneesExperience'   => $validatedData['anneesExperience'],
            'status'             => 'pending'
        ]);

        return response()->json([
            'status' => 201,
            'data'   => $expert
        ]);
    }



    /**
     * Créer une demande d'évaluation.
     * Utilisé par l'utilisateur qui demande une évaluation.
     */
    public function requestEvaluation(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ref_id_annonce' => 'required|string',
            'ref_id_expert'  => 'required|string',
        ]);

        $demande = DemandeEvaluation::create([
            'ref_id_annonce'   => $request->input('ref_id_annonce'),
            'ref_id_demandeur' => $user->_id,
            'ref_id_expert'    => $request->input('ref_id_expert'),
            'status'           => 'pending'
        ]);

        return response()->json([
            'status' => 200,
            'data' =>  $demande
        ]);
    }

    public function hasAlreadyRequestedEvaluation(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'id_annonce' => 'required|string',
    ]);

    $annonceId = $request->input('id_annonce');

    $demande = DemandeEvaluation::where('ref_id_demandeur', (string) $user->_id)
                                 ->where('ref_id_annonce', $annonceId)
                                 ->first();

    return response()->json([
        'status' => 200,
        'hasRequested' => $demande !== null
    ]);
}
public function checkRapportStatus($annonceId)
{
    // Recherche de la demande d'évaluation liée à l'annonce
    $demande = DemandeEvaluation::where('ref_id_annonce', $annonceId)->first();

    if (!$demande) {
        return response()->json([
            'status' => 404,
            'data' => [
                'message' => 'Aucune demande trouvée pour cette annonce.'
            ]
        ]);
    }

    if ($demande->status === 'rapport_submitted') {
        return response()->json([
            'status' => 200,
            'data' => [
                'rapport_genere' => true,
                'id_demande_evaluation' => (string) $demande->_id
            ]
        ]);
    } else {
        return response()->json([
            'status' => 200,
            'data' => [
                'rapport_genere' => false
            ]
        ]);
    }
}
public function getRapportInfo($annonceId)
{
    // Récupérer la demande d’évaluation associée à l’annonce
    $demande = DemandeEvaluation::where('ref_id_annonce', $annonceId)->first();

    if (!$demande) {
        return response()->json([
            'status' => 404,
            'message' => 'Aucune demande trouvée pour cette annonce.'
        ]);
    }

    // Vérifier si le rapport est soumis
    if ($demande->status === 'rapport_submitted') {
        // Rechercher le rapport lié à cette demande d’évaluation
        $rapport = RapportExpertise::where('ref_id_eval', $demande->_id)->first();

        if ($rapport) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'rapport_genere' => true,
                    'id_demande_evaluation' => (string) $demande->_id,
                    'id_rapport' => (string) $rapport->_id,
                    'id_expert'=>(string)$rapport->ref_id_expert,
                    'contenu' => $rapport->contenu, // Optionnel : contenu du rapport
                ]
            ]);
        }
    }

    // Rapport non encore généré
    return response()->json([
        'status' => 200,
        'data' => [
            'rapport_genere' => false
        ]
    ]);
}


    /**
     * Met à jour le profil de l'utilisateur connecté.
     *
     * Les champs modifiables sont : nom, prenom, email, telephone et password.
     * Pour le mot de passe, la confirmation doit être envoyée avec le champ "password_confirmation".
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'nom'       => 'sometimes|required|string',
            'prenom'    => 'sometimes|required|string',
            'email'     => 'sometimes|required|email|unique:utilisateurs,email,' . $user->_id . ',_id',
            'telephone' => 'sometimes|nullable|string',
            'password'  => 'sometimes|required|string|min:6|confirmed'
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'status' => 200,
            'data' => $user
        ]);
    }

    public function getAllUsers(){
        $users = Utilisateur::all();
        return response()->json([
            'status' => 200,
            'data' => $users
        ]);
    }
    
    /**
     * Retrieve user information by ID.
     */
    public function getUserById($id)
    {
        $user = Utilisateur::find($id); // Find the user by ID
        if ($user) {
            return response()->json([
                'status' => 200,
                'data' => $user
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ]);
        }
    }


    public function reportRapportExpertsie($rapportId)
    {
        $rapport = RapportExpertise::find($rapportId);

        if (!$rapport) {
            return response()->json([
                'status' => 404,
                'data' => 'Rapport introuvable'
            ]);
        }

        $userId = auth()->id();


        if (!is_array($rapport->reported_by)) {
            $rapport->reported_by = [];
            $rapport->save();
        }

        // Vérifier si l'utilisateur a déjà signalé cette annonce
        if (is_array($rapport->reported_by) && in_array($userId, $rapport->reported_by)) {
            return response()->json([
                'status' => 400,
                'data' => 'Vous avez déjà signalé ce rapport'
            ]);
        } 


        $rapport->push('reported_by', $userId, true); // `true` empêche les doublons

        // 🔹 Mettre à jour `is_reported` et sauvegarder
        $rapport->update(['is_reported' => true]);

        return response()->json(['status' => 200, 'data' => 'rapport signalé avec succès']);
    }

    

}
