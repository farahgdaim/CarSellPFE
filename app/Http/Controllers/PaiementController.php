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
     */
    public function store(Request $request)
    {
        $request->validate([
            'montant'        => 'required|numeric',
            'statutPaiement' => 'required|string' // ex: "en_attente", "validé", "refusé"
        ]);

        $utilisateur = auth()->user();
        $utilisateur->addPaiement(
            $request->montant,
            $request->statutPaiement
        );

        return response()->json(['message' => 'Paiement enregistré avec succès']);
    }

    /**
     * Mettre à jour le statut d'un paiement (ex: "validé", "refusé").
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'index'          => 'required|integer',
            'statutPaiement' => 'required|string|in:en_attente,validé,refusé'
        ]);

        $utilisateur = auth()->user();
        $utilisateur->updatePaiementStatus($request->index, $request->statutPaiement);

        return response()->json(['message' => 'Statut du paiement mis à jour']);
    }
}
