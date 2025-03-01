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
        return response()->json($utilisateur->paiements);
    }

    /**
     * Ajouter un nouveau paiement.
     * Accepte des champs optionnels pour les références (ex: ref_id_expert, ref_id_annonce, etc.)
     */
    public function store(Request $request)
    {
        $request->validate([
            'montant'        => 'required|numeric',
            'statutPaiement' => 'required|string', // "en_attente", "validé", "refusé"
            'type'           => 'required|string'  // "evaluation" ou "achat"
        ]);

        $paiement = [
            'montant'        => $request->montant,
            'date'           => now(),
            'statutPaiement' => $request->statutPaiement,
            'type'           => $request->type
        ];

        if ($request->type === 'evaluation') {
            $paiement['ref_id_expert'] = $request->input('ref_id_expert');
            $paiement['ref_id_annonce'] = $request->input('ref_id_annonce');
        } elseif ($request->type === 'achat') {
            $paiement['ref_id_buyer'] = $request->input('ref_id_buyer');
            $paiement['ref_id_seller'] = $request->input('ref_id_seller');
            $paiement['ref_id_annonce'] = $request->input('ref_id_annonce');
        }

        $utilisateur = auth()->user();
        $utilisateur->push('paiements', $paiement);

        return response()->json(['message' => 'Paiement enregistré avec succès']);
    }

    /**
     * Mettre à jour le statut d'un paiement.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'index'          => 'required|integer',
            'statutPaiement' => 'required|string|in:en_attente,validé,refusé'
        ]);

        $utilisateur = auth()->user();
        $paiements = $utilisateur->paiements ?? [];

        if (isset($paiements[$request->index])) {
            $paiements[$request->index]['statutPaiement'] = $request->statutPaiement;
            $utilisateur->paiements = $paiements;
            $utilisateur->save();
            return response()->json(['message' => 'Statut du paiement mis à jour']);
        } else {
            return response()->json(['message' => 'Index de paiement invalide'], 400);
        }
    }
}
