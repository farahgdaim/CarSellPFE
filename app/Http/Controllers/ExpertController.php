<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemandeEvaluation;
use App\Models\RapportExpertise;
use App\Models\Utilisateur;

class ExpertController extends Controller
{
    /**
     * Pour l'expert : liste des demandes d'évaluation en attente qui lui sont adressées.
     */
    public function listPendingEvaluations(Request $request)
    {
        $user = auth()->user();
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
        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        if ($demande->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n’est plus en attente'], 400);
        }
        $demande->status = 'accepted';
        $demande->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);
        if ($demandeur) {
            app('App\Http\Controllers\NotificationController')->store(new Request(['contenu' => "Votre demande d’évaluation a été acceptée par l’expert.", 'statut' => 'non_lu']), $demandeur);
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
        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        if ($demande->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n’est plus en attente'], 400);
        }
        $demande->status = 'rejected';
        $demande->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);
        if ($demandeur) {
            app('App\Http\Controllers\NotificationController')->store(new Request(['contenu' => "Votre demande d’évaluation a été rejetée par l’expert.", 'statut' => 'non_lu']), $demandeur);
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

        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
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
            app('App\Http\Controllers\NotificationController')->store(new Request(['contenu' => "Le rapport d’expertise est prêt. Vous pouvez le consulter.", 'statut' => 'non_lu']), $demandeur);
        }

        return response()->json([
            'message' => 'Rapport soumis avec succès.',
            'rapport' => $rapport
        ]);
    }
}
