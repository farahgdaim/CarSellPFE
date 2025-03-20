<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    
    public function getAnnonce()
    {
        return response()->json([
            'status' => 200,
            'data' => Annonce::all()
        ]);
    }

    public function getAnnonceById($id){
        $annonce = Annonce::find($id);
        if(is_null($annonce)){
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        return response()->json([
            'status'=>'200', 
            'data' => $annonce
        ]);
    }

    
    public function create(Request $request)
    {
        $user = auth()->user();

        // Validate the incoming data.
        $data = $request->validate([
            'titre'                        => 'required|string|max:255',
            'description'                  => 'required|string',
            'prix'                         => 'required|numeric',

            'vehicule'                     => 'required|array',
            'vehicule.Categorie'           => 'required|string',
            'vehicule.Marque'              => 'required|string',
            'vehicule.Modèle'              => 'required|string',
            'vehicule.TypeCarburant'       => 'required|string|in:Essence,Diesel,GPL,Electrique,Hybride',
            'vehicule.Puissance'           => 'required|string',
            'vehicule.DateDeMiseEnCirculation' => 'required|date',
            'vehicule.Cylindre'            => 'required|string',
            'vehicule.Kilométrage'         => 'required|numeric',
            'vehicule.nbPortes'            => 'required|string',
            'vehicule.boiteVitesse'        => 'required|string|in:automatique,manuelle',
            'vehicule.etat'                => 'required|string|in:neuf,excellent,correct,endommagé',
            'vehicule.equipement'          => 'required|string',

            // Validate that images is an array, and that each file is either an image or a PDF.
            'images'                     => 'nullable|array',
            'images.*'                   => 'file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // max 5MB per file
        ]);

        // Process images if they exist.
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Save file in the "uploads/images" directory on the "public" disk.
                $path = $file->store('uploads/images', 'public');

                $uploadedImages[] = [
                    'chemin' => $path,
                    'url'    => asset('storage/' . $path),
                    'format' => $file->getClientOriginalExtension(),
                    'taille' => $file->getSize()
                ];
            }
        }

        // Append additional data specific to the annonce.
        $data['Ref_id_user'] = $user->_id;
        $data['is_reported'] = false;
        $data['reported_by'] = [];

        // Override images field with our stored metadata if images were uploaded.
        if (!empty($uploadedImages)) {
            $data['images'] = $uploadedImages;
        }

        // Create the annonce.
        $annonce = Annonce::create($data);

        return response()->json([
            'status' => 201,
            'data'   => $annonce
        ]);
    }


    
    public function reportAnnonce($annonceId)
{
    $annonce = Annonce::find($annonceId);

    if (!$annonce) {
        return response()->json([ 
            'status' => 404,
            'data' => 'Annonce introuvable'
        ]);
    }

    $userId = auth()->id();

    if ($annonce->Ref_id_user === $userId) {
        return response()->json([
            'status' => 403,
            'data' => 'Vous ne pouvez pas signaler votre propre annonce'
        ]);
    }
    
    if (!is_array($annonce->reported_by)) {
        $annonce->reported_by = []; 
        $annonce->save(); 
    }

    // Vérifier si l'utilisateur a déjà signalé cette annonce
    if (is_array($annonce->reported_by) && in_array($userId, $annonce->reported_by)) {
        return response()->json([
            'status' => 400,
            'data' => 'Vous avez déjà signalé cette annonce'
        ]);
    }

    
    $annonce->push('reported_by', $userId, true); // `true` empêche les doublons

    // 🔹 Mettre à jour `is_reported` et sauvegarder
    $annonce->update(['is_reported' => true]);

    return response()->json(['status' => 200, 'data' => 'Annonce signalée']);
}


    
    public function update(Request $request, $id)
    {
        $annonce =Annonce::find($id);
        if(!$annonce){
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        $request->validate([
            'Titre' => 'string|max:255',
            'Description' => 'string',
            'DatePub' => 'date',
            'Prix' => 'numeric',
            'isSponsored' => 'boolean',
            
            'vehicule' => 'array',
            'vehicule.Categorie' => 'string',
            'vehicule.Marque' => 'string',
            'vehicule.Modèle' => 'string',
            'vehicule.TypeCarburant' => 'string|in:Essence,Diesel,GPL,Electrique,Hybride',
            'vehicule.Puissance' => 'numeric',
            'vehicule.DateDeMiseEnCirculation' => 'date',
            'vehicule.Cylindre'=> 'string',
            'vehicule.Kilométrage' => 'numeric',
            'vehicule.nbPortes'=> 'string',
            'vehicule.boiteVitesse'=> 'string|in:automatique,manuelle',
            'vehicule.etat'=> 'string|in:neuf,excellent,correct,endommagé',
            'vehicule.equipement' => 'string',
            'images' => 'nullable|array',
            'images.*.chemin' => 'string',
            'images.*.format' => 'string',
            'images.*.taille' => 'string',
        ]);

        
        $annonce->update($request->all());

        return response()->json([
            'status' => 201,
            'data' => $annonce
        ]);

    }

   
    public function destroy($id)
    {
        $annonce =Annonce::find($id);
        if(!$annonce){
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        $annonce->delete();
        return response()->json([
            'status' => 204,
            'data' => null
        ]);
    
    }
}
