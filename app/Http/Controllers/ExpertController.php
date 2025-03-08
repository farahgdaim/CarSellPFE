<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemandeEvaluation;
use App\Models\RapportExpertise;
use App\Models\Utilisateur;
use App\Models\Expert;
use App\Http\Controllers\NotificationController;


class ExpertController extends Controller
{
    /**
     * Vérifie que l'utilisateur authentifié possède un rôle d'expert accepté.
     */
    protected function authorizeExpert() {
        $user = auth()->user();
        $expert = Expert::where('ref_id_utilisateur', $user->_id)->first();
        if (!$expert || $expert->status !== 'accepted') {
            return null;
        }
        return $expert;
    }
    
    
    /**
     * Pour l'expert : liste des demandes d'évaluation en attente qui lui sont adressées.
     */
    public function listPendingEvaluations(Request $request)
    {
        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'], 403);
        }
        
        $demandes = DemandeEvaluation::where('ref_id_expert', $user->_id)
            ->where('status', 'pending')
            ->get();

        return response()->json($demandes);
    }

    /**
     * L'expert accepte une demande d'évaluation.
     */
    public function acceptEvaluation(Request $request, $demandeId)
    {
        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'], 403);
        }
        
        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        if ($demande->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n’est plus en attente'], 400);
        }
        // Vérifier que la demande appartient bien à l'expert authentifié
        if ($demande->ref_id_expert != $user->_id) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à traiter cette demande'], 403);
        }
        
        $demande->status = 'accepted';
        $demande->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);
        if ($demandeur) {
            $message = "Votre demande d’évaluation a été acceptée par l’expert.";
            NotificationController::notifyUser($demandeur, $message);
        }

        return response()->json([
            'message' => 'Demande d’évaluation acceptée.',
            'demande' => $demande
        ]);
    }

    /**
     * L'expert rejette une demande d'évaluation.
     */
    public function rejectEvaluation(Request $request, $demandeId)
    {
        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'], 403);
        }
        
        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        if ($demande->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n’est plus en attente'], 400);
        }
        // Vérifier que la demande appartient bien à l'expert authentifié
        if ($demande->ref_id_expert != $user->_id) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à traiter cette demande'], 403);
        }
        
        $demande->status = 'rejected';
        $demande->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);
        if ($demandeur) {
            $message = "Votre demande d’évaluation a été rejetée par l’expert.";
            NotificationController::notifyUser($demandeur, $message);
        }

        return response()->json([
            'message' => 'Demande d’évaluation rejetée.',
            'demande' => $demande
        ]);
    }

    /**
     * L'expert soumet son rapport pour une demande d'évaluation.
     */
    public function submitRapport(Request $request, $demandeId)
    {
        $request->validate([
            'contenu' => 'required|string'
        ]);

        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'], 403);
        }
        
        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        // Vérifier que la demande appartient bien à l'expert authentifié
        if ($demande->ref_id_expert != $user->_id) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à traiter cette demande'], 403);
        }
        
        $rapport = RapportExpertise::create([
            'contenu'       => $request->input('contenu'),
            'ref_id_expert' => $demande->ref_id_expert,
            'ref_id_eval'   => $demande->_id,
        ]);

        $demande->status = 'rapport_submitted';
        $demande->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);
        if ($demandeur) {
            $message = "Le rapport d’expertise est prêt. Vous pouvez le consulter.";
            NotificationController::notifyUser($demandeur, $message);
        }

        return response()->json([
            'message' => 'Rapport soumis avec succès.',
            'rapport' => $rapport
        ]);
    }
}
