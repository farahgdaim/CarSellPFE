<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RapportExpertise;
use App\Models\DemandeEvaluation;
use App\Models\Utilisateur;

class RapportExpertiseController extends Controller
{
    /**
     * L'expert soumet son rapport pour une demande d'évaluation.
     * Crée un document dans la collection RapportExpertise.
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
            // ref_id_admin reste null au départ; il pourra être renseigné par la suite en cas de signalement
        ]);

        // Optionnel : Vous pouvez modifier le status de la demande pour indiquer que le rapport a été soumis
        $demande->status = 'rapport_submitted';
        $demande->save();

        // Notification au demandeur
        $demandeur = Utilisateur::find($demande->ref_id_demandeur);
        if ($demandeur) {
            $demandeur->addNotification("Le rapport d’expertise est prêt. Vous pouvez le consulter.");
        }

        return response()->json([
            'message' => 'Rapport soumis avec succès.',
            'rapport' => $rapport
        ]);
    }
}
