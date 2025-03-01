<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\Utilisateur;
use App\Models\Expert;

class EvaluationController extends Controller
{
    /**
     * Le demandeur crée une demande d’évaluation en choisissant un expert pour une annonce.
     */
    public function requestEvaluation(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'ref_id_annonce' => 'required|string',
            'ref_id_expert'  => 'required|string',
        ]);

        $evaluation = Evaluation::create([
            'ref_id_annonce'   => $request->input('ref_id_annonce'),
            'ref_id_demandeur' => $user->_id,
            'ref_id_expert'    => $request->input('ref_id_expert'),
            'status'           => 'pending'
        ]);

        // Optionnel : ajouter une notification au compte de l'expert

        return response()->json([
            'message' => 'Demande d’évaluation envoyée.',
            'evaluation' => $evaluation
        ]);
    }

    /**
     * L’expert liste toutes les demandes d’évaluation en attente qui lui sont adressées.
     */
    public function listPendingEvaluations()
    {
        $user = auth()->user();
        $expert = Expert::where('ref_id_utilisateur', $user->_id)
                        ->where('status', 'approved')
                        ->first();
        if (!$expert) {
            return response()->json(['message' => 'Action réservée aux experts approuvés.'], 403);
        }

        $evaluations = Evaluation::where('ref_id_expert', $expert->_id)
                                 ->where('status', 'pending')
                                 ->get();
        return response()->json($evaluations);
    }

    /**
     * L’expert accepte une demande d’évaluation.
     */
    public function acceptEvaluation(Request $request, $evaluationId)
    {
        $user = auth()->user();
        $expert = Expert::where('ref_id_utilisateur', $user->_id)
                        ->where('status', 'approved')
                        ->first();
        if (!$expert) {
            return response()->json(['message' => 'Action réservée aux experts approuvés.'], 403);
        }

        $evaluation = Evaluation::find($evaluationId);
        if (!$evaluation) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        if ($evaluation->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n’est plus en attente'], 400);
        }

        $evaluation->status = 'accepted';
        $evaluation->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($evaluation->ref_id_demandeur);
        if ($demandeur) {
            $demandeur->addNotification("Votre demande d’évaluation a été acceptée par l’expert.");
        }

        return response()->json([
            'message' => 'Demande d’évaluation acceptée.',
            'evaluation' => $evaluation
        ]);
    }

    /**
     * L’expert rejette une demande d’évaluation.
     */
    public function rejectEvaluation(Request $request, $evaluationId)
    {
        $user = auth()->user();
        $expert = Expert::where('ref_id_utilisateur', $user->_id)
                        ->where('status', 'approved')
                        ->first();
        if (!$expert) {
            return response()->json(['message' => 'Action réservée aux experts approuvés.'], 403);
        }

        $evaluation = Evaluation::find($evaluationId);
        if (!$evaluation) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }
        if ($evaluation->status !== 'pending') {
            return response()->json(['message' => 'Cette demande n’est plus en attente'], 400);
        }

        $evaluation->status = 'rejected';
        $evaluation->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($evaluation->ref_id_demandeur);
        if ($demandeur) {
            $demandeur->addNotification("Votre demande d’évaluation a été rejetée par l’expert.");
        }

        return response()->json([
            'message' => 'Demande d’évaluation rejetée.',
            'evaluation' => $evaluation
        ]);
    }

    /**
     * L’expert soumet son rapport d’expertise pour une demande.
     */
    public function submitRapport(Request $request, $evaluationId)
    {
        $user = auth()->user();
        $expert = Expert::where('ref_id_utilisateur', $user->_id)
                        ->where('status', 'approved')
                        ->first();
        if (!$expert) {
            return response()->json(['message' => 'Action réservée aux experts approuvés.'], 403);
        }

        $request->validate([
            'rapport' => 'required|string'
        ]);

        $evaluation = Evaluation::find($evaluationId);
        if (!$evaluation) {
            return response()->json(['message' => 'Demande d’évaluation non trouvée'], 404);
        }

        $evaluation->rapport = $request->input('rapport');
        $evaluation->status = 'rapport_submitted';
        $evaluation->save();

        // Notification au demandeur que le rapport est prêt
        $demandeur = Utilisateur::find($evaluation->ref_id_demandeur);
        if ($demandeur) {
            $demandeur->addNotification("Le rapport d’expertise est prêt. Vous pouvez le consulter.");
        }

        return response()->json([
            'message' => 'Rapport soumis avec succès.',
            'evaluation' => $evaluation
        ]);
    }
}
