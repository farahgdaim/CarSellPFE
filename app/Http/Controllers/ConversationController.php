<?php

namespace App\Http\Controllers;
use App\Models\Conversation;
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

    public function  getConversationById($id){
        $conversation = Conversation::find($id);
        if(!$conversation){
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
    public function createConversation(Request $request){
        $validatedData =$request ->validate([
            'contenu'=> 'required|string',
            'messages'=>'required|array',
            'messages.*.contenu'=>'required|string',
            
        ]);
        $validatedData['Ref_id_user'] = auth()->user()->_id; 
        
        $conversation = Conversation::create([
            'contenu'      => $validatedData['contenu'],
            'Ref_id_user'  => $validatedData['Ref_id_user'],
        ]);
    
        // Pour chaque message, on l'ajoute via la relation, ce qui va automatiquement générer un _id pour chaque sous-document
        foreach ($validatedData['messages'] as $msg) {
            $conversation->messages()->create([
                'contenu'   => $msg['contenu'],
                'dateEnvoi' => \Carbon\Carbon::now(),
            ]);
        }

        return response()->json([
            'status' => 201,
            'data' => $conversation
        ]);

    }

    public function deleteConversation($id){
        $conversation = Conversation::find($id);
        if(!$conversation){
            return response()->json([
               'status' => 404,
                'data' => 'Conversation not found'
            ]);
        }
        $conversation->delete();
        return response()->json([
            'status' => 200,
            'data' => 'conversation deleted successfully'
        ]);
    }

    public function addMessage(Request $request,$id){
        $conversation = Conversation::find($id);
        if(!$conversation){
            return response()->json([
               'status' => 404,
                'data' => 'Conversation not found'
            ]);
        }
        $data= $request ->validate([
            'contenu'=> 'required|string',
        ]);
        $message = [ 
            'contenu' => $data['contenu'],
            'dateEnvoi' => Carbon::now(), 
        ];

        $conversation->messages()->create([
            'contenu'   => $data['contenu'],
            'dateEnvoi' => Carbon::now(),
        ]);
    
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
