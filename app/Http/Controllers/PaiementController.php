<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;

class PaiementController extends Controller
{
    /**
     * Récupérer tous les paiements de l'utilisateur connecté.
     */ 
    public function index()
    {
        $utilisateur = auth()->user();
        return response()->json([
            'paiements' => $utilisateur->paiements
        ]);
    }

    /**
     * Ajouter un nouveau paiement.
     */
    public function store(Request $request)
    {
        $rules = [
            'montant'          => 'required|numeric',
            'statutPaiement'   => 'required|string|in:en_attente,validé,refusé',
            'type'             => 'required|string|in:evaluation,achat',
            'ref_id_annonce'   => 'required_if:type,evaluation|required_if:type,achat',
            'ref_id_expert'    => 'required_if:type,evaluation',
            'ref_id_seller'    => 'required_if:type,achat',
        ];

        $validated = $request->validate($rules);

        $paiement = [
            'montant'         => $validated['montant'],
            'date'            => now(),
            'statutPaiement'  => $validated['statutPaiement'],
            'type'            => $validated['type'],
            'ref_id_payer'    => auth()->id(),
        ];

        if ($validated['type'] === 'evaluation') {
            $paiement['ref_id_expert'] = $validated['ref_id_expert'];
            $paiement['ref_id_annonce'] = $validated['ref_id_annonce'];
        } elseif ($validated['type'] === 'achat') {
            $paiement['ref_id_seller'] = $validated['ref_id_seller'];
            $paiement['ref_id_annonce'] = $validated['ref_id_annonce'];
        }

        $utilisateur = auth()->user();

        try {
            $utilisateur->push('paiements', $paiement);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement du paiement',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Paiement enregistré avec succès',
            'paiement' => $paiement
        ], 201);
    }

    /**
     * Mettre à jour le statut d'un paiement.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'index'          => 'required|integer',
            'statutPaiement' => 'required|string|in:en_attente,validé,refusé',
        ]);

        $utilisateur = auth()->user();
        $paiements = $utilisateur->paiements ?? [];

        if (!isset($paiements[$request->index])) {
            return response()->json([
                'message' => 'Index de paiement invalide'
            ], 400);
        }

        $paiements[$request->index]['statutPaiement'] = $request->statutPaiement;
        $utilisateur->paiements = $paiements;

        try {
            $utilisateur->save();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du paiement',
                'error'   => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Statut du paiement mis à jour',
            'paiements' => $paiements
        ]);
    }
}
