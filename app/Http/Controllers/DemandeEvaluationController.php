<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemandeEvaluation;
use App\Models\Utilisateur;

class DemandeEvaluationController extends Controller
{
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
            'message' => 'Demande d’évaluation créée avec succès.',
            'demande' => $demande
        ]);
    }

    /**
     * Pour l'expert : liste des demandes d'évaluation en attente qui lui sont adressées.
     */
    public function listPendingEvaluationsForExpert()
    {
        $user = auth()->user();
        // Ici, on suppose que dans la demande, ref_id_expert contient l'ID de l'expert
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
            $demandeur->addNotification("Votre demande d’évaluation a été acceptée par l’expert.");
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
            $demandeur->addNotification("Votre demande d’évaluation a été rejetée par l’expert.");
        }

        return response()->json([
            'message' => 'Demande d’évaluation rejetée.',
            'demande' => $demande
        ]);
    }
}
