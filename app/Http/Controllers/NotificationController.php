<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;

class NotificationController extends Controller
{
    /**
     * Récupérer toutes les notifications de l'utilisateur connecté.
     */
    public function index()
    {
        $utilisateur = auth()->user();
        return response()->json($utilisateur->notifications);
    }

    /**
     * Ajouter une notification pour l'utilisateur connecté.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contenu' => 'required|string',
            'statut'  => 'required|string' // ex: "non_lu", "lu"
        ]);

        $utilisateur = auth()->user();
        $utilisateur->addNotification(
            $request->contenu,
            $request->statut
        );

        return response()->json(['message' => 'Notification ajoutée avec succès']);
    }

    /**
     * Marquer une notification comme lue (ou changer son statut).
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'index' => 'required|integer'
        ]);

        $utilisateur = auth()->user();
        $utilisateur->markNotificationAsRead($request->index);

        return response()->json(['message' => 'Notification marquée comme lue']);
    }
}
