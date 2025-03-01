<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Expert;
use Illuminate\Support\Facades\Log;

class ExpertController extends Controller
{
    /**
     * L'utilisateur connecté fait une demande pour devenir expert
     * en fournissant son fichier PDF de certification, son domaine d'expertise et ses années d'expérience.
     * Un document est créé dans la collection "experts".
     */
    public function requestExpertRole(Request $request)
    {
        $user = auth()->user();
        Log::info("ExpertController: requestExpertRole called.");
        Log::info("User authenticated: " . json_encode($user));

        // Vérifier si une demande existe déjà pour cet utilisateur
        $existingExpert = Expert::where('ref_id_utilisateur', $user->_id)->first();
        if ($existingExpert) {
            return response()->json(['message' => 'Une demande est déjà en cours ou vous êtes déjà expert.'], 400);
        }

        // Validation des données entrantes
        $validatedData = $request->validate([
            'certification'    => 'required|file|mimes:pdf|max:2048',
            'domaineExpertise' => 'required|string',
            'anneesExperience' => 'required|integer|min:0'
        ]);
        Log::info("Request validated: " . json_encode($validatedData));

        // Récupération et stockage du fichier PDF
        $file = $request->file('certification');
        Log::info("Certification file received: " . $file->getClientOriginalName());
        $path = $file->store('certifications', 'public');
        Log::info("File stored at path: " . $path);

        // Création d'un document Expert dans la collection "experts"
        $expert = Expert::create([
            'ref_id_utilisateur' => $user->_id,
            'certifications'     => [$path],
            'domaineExpertise'   => $request->input('domaineExpertise'),
            'anneesExperience'   => $request->input('anneesExperience'),
            'status'             => 'pending'
        ]);
        Log::info("Expert record created: " . json_encode($expert));

        return response()->json([
            'message' => 'Demande pour devenir expert envoyée avec succès.',
            'expert'  => $expert
        ]);
    }

    /**
     * (ADMIN) Valide la demande d'expertise.
     * Met à jour le document Expert (status = "approved") et met à jour le rôle de l'utilisateur associé.
     * Envoie une notification à l'utilisateur.
     */
    public function acceptExpertRole(Request $request, $expertId)
    {
        $expert = Expert::find($expertId);
        if (!$expert) {
            return response()->json(['message' => 'Demande d\'expert non trouvée'], 404);
        }
        if ($expert->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n\'est plus en attente'], 400);
        }

        $admin = auth('admin')->user();

        $expert->status = 'approved';
        $expert->ref_id_admin = $admin ? $admin->_id : null;
        $expert->save();

        $user = Utilisateur::find($expert->ref_id_utilisateur);
        if ($user) {
            $user->role = 'expert';
            $user->ref_id_admin = $admin ? $admin->_id : null;
            $user->save();
            $user->addNotification("Votre demande pour devenir expert a été approuvée.");
        }

        return response()->json([
            'message' => 'Demande validée. Cet utilisateur est désormais un expert.',
            'expert'  => $expert
        ]);
    }

    /**
     * (ADMIN) Rejette la demande d'expertise.
     * Met à jour le document Expert (status = "rejected") et envoie une notification à l'utilisateur.
     */
    public function rejectExpertRole(Request $request, $expertId)
    {
        $expert = Expert::find($expertId);
        if (!$expert) {
            return response()->json(['message' => 'Demande d\'expert non trouvée'], 404);
        }
        if ($expert->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n\'est plus en attente'], 400);
        }

        $admin = auth('admin')->user();
        $expert->status = 'rejected';
        $expert->ref_id_admin = $admin ? $admin->_id : null;
        $expert->save();

        $user = Utilisateur::find($expert->ref_id_utilisateur);
        if ($user) {
            $user->addNotification("Votre demande pour devenir expert a été rejetée.");
        }

        return response()->json([
            'message' => 'Demande rejetée.',
            'expert'  => $expert
        ]);
    }

    /**
     * (ADMIN) Liste toutes les demandes d'expertise en attente.
     */
    public function listPendingExpertRequests()
    {
        $pendingExperts = Expert::where('status', 'pending')->get();
        return response()->json($pendingExperts);
    }

    /**
     * Exemple d'action réservée aux experts approuvés (ex : accepter une évaluation).
     */
    public function acceptEvaluation(Request $request)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur possède un document Expert approuvé
        $expert = Expert::where('ref_id_utilisateur', $user->_id)
                        ->where('status', 'approved')
                        ->first();
        if (!$expert) {
            return response()->json(['message' => 'Action réservée aux experts approuvés.'], 403);
        }

        $evaluationId = $request->input('evaluationId');
        // Traitement de l'évaluation...
        return response()->json(['message' => 'Évaluation acceptée par l’expert.']);
    }
    
    /**
     * Optionnel : Si vous souhaitez ajouter une action pour rejeter une évaluation directement depuis cet endpoint.
     */
    public function rejectEvaluation(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'expert') {
            return response()->json(['message' => 'Action réservée aux experts.'], 403);
        }

        // Logique de rejet d'évaluation si nécessaire (sinon, utilisez EvaluationController pour cela)
        return response()->json(['message' => 'Rejet de l’évaluation non implémenté ici.']);
    }
}
