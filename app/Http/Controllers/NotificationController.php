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
        return response()->json([
            'status' => 200,
            'data' => $utilisateur->notifications
        ]);
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


        return response()->json([
            'status' => 200,
            'data' => 'Notification ajoutée avec succès'
        ]);
    }

    /**
     * Marquer une notification comme lue (ou changer son statut).
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'index' => 'required|integer'
        ]);

        // Retrieve the index from the request
        $index = $request->input('index');

        // Get the authenticated user's notifications
        $utilisateur = auth()->user();
        $notifications = $utilisateur->notifications ?? [];

        // Check if the given index exists in the notifications array
        if (isset($notifications[$index])) {
            // Update the notification's status to 'lu'
            $notifications[$index]['statut'] = 'lu';

            // Save the updated notifications back to the user model
            $utilisateur->notifications = $notifications;
            $utilisateur->save();

            return response()->json([
                'status' => 200,
                'data' => 'Notification marquée comme lue'
            ]);
        }

        return response()->json([
            'status' => 404,
            'data' => 'Notification not found.'
        ]);
    }
    
    /**
     * Envoie une notification à un utilisateur.
     */
    public static function notifyUser(Utilisateur $user, string $contenu, string $statut = 'non_lu')
    {
        $notification = [
            'ref_id_user' => $user->_id,
            'contenu'     => $contenu,
            'date'        => now(),
            'statut'      => $statut,
        ];

        $user->push('notifications', $notification);
    }

}
