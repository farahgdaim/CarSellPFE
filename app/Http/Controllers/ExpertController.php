<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Log;

class ExpertController extends Controller
{
    /**
     * L'utilisateur connecté fait une demande pour devenir expert en fournissant ses preuves.
     */
    public function requestExpertRole(Request $request)
    {
        $user = auth()->user();
        Log::info("ExpertController: requestExpertRole called.");
        Log::info("User authenticated: " . json_encode($user));

        // Vérifier si l'utilisateur est déjà expert
        if ($user->role === 'expert') {
            Log::info("User is already expert.");
            return response()->json(['message' => 'Vous êtes déjà un expert.'], 400);
        }
        // Vérifier si l'utilisateur a déjà fait une demande
        if ($user->expertRequested) {
            Log::info("User already requested expert.");
            return response()->json(['message' => 'Une demande est déjà en cours.'], 400);
        }

        Log::info("Validating request input.");
        // Validation des données entrantes
        $validatedData = $request->validate([
            'certification'    => 'required|file|mimes:pdf|max:2048',
            'domaineExpertise' => 'required|string',
            'anneesExperience' => 'required|integer|min:0'
        ]);
        Log::info("Request validated: " . json_encode($validatedData));

        // Récupérer le fichier PDF
        $file = $request->file('certification');
        Log::info("Certification file received: " . $file->getClientOriginalName());
        
        // Stocker le fichier dans le dossier 'certifications' dans storage/app/public
        $path = $file->store('certifications', 'public');
        Log::info("File stored at path: " . $path);

        // Mise à jour des informations de l'utilisateur
        $user->certifications    = [$path];
        $user->domaineExpertise  = $request->input('domaineExpertise');
        $user->anneesExperience  = $request->input('anneesExperience');
        $user->expertRequested   = true;
        Log::info("User data before save: " . json_encode($user->toArray()));

        // Enregistrement en base de données
        $result = $user->save();
        Log::info("User save result: " . json_encode($result));

        // Rafraîchir l'objet pour être sûr d'avoir les dernières données
        $user->refresh();
        Log::info("User data after refresh: " . json_encode($user->toArray()));

        return response()->json([
            'message' => 'Demande pour devenir expert envoyée avec succès.',
            'user'    => $user
        ]);
    }


    /**
     * (ADMIN) Valide la demande d'un utilisateur pour devenir expert.
     */
    public function acceptExpertRole($userId)
    {
        $utilisateur = Utilisateur::find($userId);
        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        if (!$utilisateur->expertRequested) {
            return response()->json(['message' => 'Aucune demande en cours pour cet utilisateur'], 400);
        }

        // Changement de rôle et réinitialisation de la demande
        $utilisateur->role = 'expert';
        $utilisateur->expertRequested = false;
        $utilisateur->save();

        return response()->json([
            'message'     => 'Demande validée. Cet utilisateur est désormais un expert.',
            'utilisateur' => $utilisateur
        ]);
    }

    /**
     * (ADMIN) Rejette la demande d'un utilisateur pour devenir expert.
     */
    public function rejectExpertRole($userId)
    {
        $utilisateur = Utilisateur::find($userId);
        if (!$utilisateur) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        if (!$utilisateur->expertRequested) {
            return response()->json(['message' => 'Aucune demande en cours pour cet utilisateur'], 400);
        }

        $utilisateur->expertRequested = false;
        $utilisateur->save();

        return response()->json([
            'message'     => 'Demande rejetée. Cet utilisateur reste un simple user.',
            'utilisateur' => $utilisateur
        ]);
    }

    /**
     * Exemple d’action réservée aux experts (ex : accepter une évaluation).
     */
    public function acceptEvaluation(Request $request)
    {
        $user = auth()->user();

        // Vérification stricte du rôle
        if ($user->role !== 'expert') {
            return response()->json(['message' => 'Action réservée aux experts.'], 403);
        }

        // Logique de traitement de l'évaluation (exemple)
        $evaluationId = $request->input('evaluationId');
        // Traitement de l'évaluation...
        
        return response()->json(['message' => 'Évaluation acceptée par l’expert.']);
    }
}
