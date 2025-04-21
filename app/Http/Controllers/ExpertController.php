<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemandeEvaluation;
use App\Models\RapportExpertise;
use App\Models\Annonce;
use App\Models\Utilisateur;
use App\Models\Expert;
use App\Http\Controllers\NotificationController;


class ExpertController extends Controller
{
    /**
     * Liste des demandes d'évaluation acceptées (status = 'accepted').
     */
    public function listAcceptedEvaluations()
    {
        $expert = $this->authorizeExpert();
        if (!$expert) {
            return response()->json(['status' => 403, 'data' => 'Non autorisé'], 403);
        }

        $demandes = DemandeEvaluation::where('ref_id_expert', $expert->ref_id_utilisateur)
            ->where('status', 'accepted')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $demandes
        ]);
    }

    /**
     * Récupère les détails d'une demande d'évaluation,
     * incluant la demande, l'annonce associée et le demandeur.
     */
    public function getEvaluationDetails($demandeId)
    {
        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json([
                'status' => 404,
                'message' => 'Demande d’évaluation introuvable.'
            ], 404);
        }

        $annonce   = Annonce::find($demande->ref_id_annonce);
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);

        return response()->json([
            'status' => 200,
            'data' => [
                'demande'   => $demande,
                'annonce'   => $annonce,
                'demandeur' => $demandeur
            ]
        ]);
    }
    
    /**
     * Vérifie si l'utilisateur authentifié est un expert avec un statut accepté.
     */
    public function checkExpertStatus()
    {
        // Récupère l'utilisateur authentifié.
        $user = auth()->user();

        // Recherche un enregistrement d'expert pour cet utilisateur.
        $expert = Expert::where('ref_id_utilisateur', $user->_id)->first();

        // Vérifie que l'enregistrement existe et que le statut est "accepted".
        if ($expert && $expert->status === 'accepted') {
            return response()->json(['isExpert' => true], 200);
        } else {
            return response()->json(['isExpert' => false], 200);
        }
    }


    /**
     * Vérifie que l'utilisateur authentifié possède un rôle d'expert accepté.
     */
    protected function authorizeExpert()
    {
        $user = auth()->user();
        $expert = Expert::where('ref_id_utilisateur', $user->_id)->first();
        if (!$expert || $expert->status !== 'accepted') {
            return null;
        }
        return $expert;
    }
    public function getAllExperts()
    {
        // Check if the user is logged in
        if (!auth()->check()) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized. Please log in to proceed.',
            ], 401);
        }

        // Retrieve experts with status "accepted"
        $experts = Expert::where('status', 'accepted')->get();

        return response()->json([
            'status' => 200,
            'data' => $experts
        ]);
    }
    public function getExpertById($id)
    {
        $expert = Expert::find($id);
        if (is_null($expert)) {
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        return response()->json([
            'status' => '200',
            'data' => $expert
        ]);
    }



    /**
     * Pour l'expert : liste des demandes d'évaluation en attente qui lui sont adressées.
     */
    public function listPendingEvaluations(Request $request)
    {
        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'
            ]);
        }

        $demandes = DemandeEvaluation::where('ref_id_expert', $user->_id)
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $demandes
        ]);
    }

    /**
     * L'expert accepte une demande d'évaluation.
     */
    public function acceptEvaluation(Request $request, $demandeId)
    {
        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'
            ]);
        }

        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json([
                'status' => 404,
                'data' => 'Demande d’évaluation non trouvée'
            ]);
        }
        if ($demande->status !== 'pending') {
            return response()->json([
                'status' => 400,
                'data' => 'Cette demande n’est plus en attente'
            ]);
        }
        // Vérifier que la demande appartient bien à l'expert authentifié
        if ($demande->ref_id_expert != $user->_id) {
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé à traiter cette demande'
            ]);
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
            'status' => 200,
            'data' => $demande
        ]);
    }

    /**
     * L'expert rejette une demande d'évaluation.
     */
    public function rejectEvaluation(Request $request, $demandeId)
    {
        $user = auth()->user();
        if (!$this->authorizeExpert()) {
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'
            ]);
        }

        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json([
                'status' => 404,
                'data' => 'Demande d’évaluation non trouvée'
            ]);
        }
        if ($demande->status !== 'pending') {
            return response()->json([
                'status' => 400,
                'data' => 'Cette demande n’est plus en attente'
            ]);
        }
        // Vérifier que la demande appartient bien à l'expert authentifié
        if ($demande->ref_id_expert != $user->_id) {
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé à traiter cette demande'
            ]);
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
            'status' => 200,
            'data' => $demande
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
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé, vous n\'êtes pas un expert.'
            ]);
        }

        $demande = DemandeEvaluation::find($demandeId);
        if (!$demande) {
            return response()->json([
                'status' => 404,
                'data' => 'Demande d’évaluation non trouvée'
            ]);
        }
        // Vérifier que la demande appartient bien à l'expert authentifié
        if ($demande->ref_id_expert != $user->_id) {
            return response()->json([
                'status' => 403,
                'data' => 'Vous n\'êtes pas autorisé à traiter cette demande'
            ]);
        }

        $rapport = RapportExpertise::create([
            'contenu'       => $request->input('contenu'),
            'ref_id_expert' => $demande->ref_id_expert,
            'ref_id_eval'   => $demande->_id,
            'is_repported' => false,
            'status'=>'unpaid',
            'reported_by'=> []
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
            'status' => 200,
            'data' => $rapport
        ]);
    }
}
