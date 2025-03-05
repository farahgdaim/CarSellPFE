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
        
        $notification = [
            'ref_id_user' => $utilisateur->_id, // référence à cet utilisateur
            'contenu'     => $request->contenu,
            'date'        => now(),
            'statut'      => $request->statut,
        ];

        $utilisateur->push('notifications', $notification);

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

        // Récupérer le tableau complet des notifications
        $notifications = $utilisateur->notifications ?? [];

        // Vérifier si l'index existe dans le tableau
        if (isset($notifications[$index])) {
            // Modifier la notification localement
            $notifications[$index]['statut'] = 'lu';

            // Réassigner le tableau modifié à la propriété notifications
            $utilisateur->notifications = $notifications;
            $utilisateur->save();
        }

        return response()->json(['message' => 'Notification marquée comme lue']);
    }
}
