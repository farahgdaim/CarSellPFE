<?php

namespace App\Http\Controllers;
use App\Models\Conversation;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use MongoDB\BSON\ObjectId;

class ConversationController extends Controller
{
    public function getAllConversation(){
        return response()->json([
            'status' => 200,
            'data' => Conversation::all()
        ]);
    }

    public function getConversationById($userId1, $userId2)
    {
        $conversation = Conversation::where(function ($query) use ($userId1, $userId2) {
            $query->where('Ref_id_user1', $userId1)
                ->where('Ref_id_user2', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('Ref_id_user1', $userId2)
                ->where('Ref_id_user2', $userId1);
        })->first();

        if (!$conversation) {
            return response()->json([
                'status' => 404,
                'data' => 'Conversation not found'
            ]);
        }

        return response()->json([
            'status' => 200,
            'data' => $conversation
        ]);
    }

    public function createConversation($userId)
    {
        $sender = auth()->user();
        if (!$sender) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized']);
        }

        $receiver = Utilisateur::find($userId);
        if (!$receiver) {
            return response()->json(['status' => 404, 'data' => 'User not found']);
        }

        // Check if conversation already exists
        $conversation = Conversation::where(function ($query) use ($sender, $receiver) {
            $query->where('Ref_id_user1', $sender->_id)
                ->where('Ref_id_user2', $receiver->_id);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('Ref_id_user1', $receiver->_id)
                ->where('Ref_id_user2', $sender->_id);
        })->first();

        if ($conversation) {
            return response()->json(['status' => 200, 'data' => $conversation]);
        }

        // Create new conversation
        $newConversation = Conversation::create([
            'Ref_id_user1' => $sender->_id,
            'Ref_id_user2' => $receiver->_id,
            'messages'     => [],
        ]);

        return response()->json(['status' => 201, 'data' => $newConversation]);
    }

    public function deleteConversation($userId1, $userId2)
    {
        $conversation = Conversation::where(function ($query) use ($userId1, $userId2) {
            $query->where('Ref_id_user1', $userId1)
                ->where('Ref_id_user2', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('Ref_id_user1', $userId2)
                ->where('Ref_id_user2', $userId1);
        })->first();

        if (!$conversation) {
            return response()->json([
                'status' => 404,
                'data' => 'Conversation not found'
            ]);
        }

        $conversation->delete();

        return response()->json([
            'status' => 200,
            'data' => 'Conversation deleted successfully'
        ]);
    }

    public function addMessage(Request $request, $userId1, $userId2)
    {
        $conversation = Conversation::where(function ($query) use ($userId1, $userId2) {
            $query->where('Ref_id_user1', $userId1)
                ->where('Ref_id_user2', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('Ref_id_user1', $userId2)
                ->where('Ref_id_user2', $userId1);
        })->first();

        if (!$conversation) {
            return response()->json([
                'status' => 404,
                'data' => 'Conversation not found'
            ]);
        }

        $data = $request->validate([
            'contenu' => 'required|string',
        ]);

        $message = [
            'contenu'   => $data['contenu'],
            'dateEnvoi' => Carbon::now()
        ];

        // Ensure $conversation->messages is treated as an array
        $existingMessages = $conversation->messages ? $conversation->messages->toArray() : [];
        $conversation->messages = array_merge($existingMessages, [$message]);
        $conversation->save();

        return response()->json([
            'status' => 201,
            'data' => $message
        ]);
    }



    /* public function updateMessage(Request $request, $conversationId, $messageId)
{
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return response()->json([
            'status' => 404,
            'data' => 'Conversation not found'
        ]);
    }

    $data = $request->validate([
        'contenu' => 'required|string',
    ]);

    // Récupère le message via la relation embedsMany
    $message = $conversation->messages()->find($messageId);
    if (!$message) {
        return response()->json([
            'status' => 404,
            'data' => 'Message not found'
        ]);
    }

    // Met à jour le contenu et la date d'envoi du message
    $message->contenu = $data['contenu'];
    $message->dateEnvoi = \Carbon\Carbon::now();

    // Sauvegarde la conversation qui contient le message modifié
    $conversation->save();

    return response()->json([
        'status' => 200,
        'data' => $message
    ]);
}
 */
    

 /* public function deleteMessage($conversationId, $messageId)
{
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return response()->json([
            'status' => 404,
            'data'   => 'Conversation not found'
        ]);
    }

    // Convertir les messages en tableau
    $messagesArray = $conversation->messages->toArray();

    // Filtrer les messages pour exclure celui qui a l'ID donné
    $filteredMessages = array_filter($messagesArray, function ($msg) use ($messageId) {
        return $msg['_id'] !== $messageId;
    });

    // Vérifier si un message a été supprimé
    if (count($filteredMessages) === count($messagesArray)) {
        return response()->json([
            'status' => 404,
            'data'   => 'Message not found'
        ]);
    }

    // Réassigner les messages filtrés et sauvegarder
    $conversation->messages = array_values($filteredMessages);
    $conversation->save();

    return response()->json([
        'status' => 200,
        'data'   => 'Message deleted successfully'
    ]);
} */

 
}
