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

    public function search(Request $request) {
        $query = Annonce::query();
    
        // Vérifier chaque critère avant d'appliquer le filtre
        $filters = [];
        
        if ($request->filled('marque')) {
            $filters['vehicule.Marque'] = $request->marque;
        }
        if ($request->filled('modele')) {
            $filters['vehicule.Modèle'] = $request->modele;
        }
        if ($request->filled('puissance')) {
            $filters['vehicule.Puissance'] = ['$gte' => (int) $request->puissance];
        }
        if ($request->filled('kilometrage')) {
            $filters['vehicule.Kilométrage'] = ['$lte' => (int) $request->kilometrage];
        }
        if ($request->filled('energie')) {
            $filters['vehicule.TypeCarburant'] = $request->energie;
        }
        if ($request->filled('boiteVitesse')) {
            $filters['vehicule.boiteVitesse'] = $request->boiteVitesse;
        }
        if ($request->filled('etat')) {
            $filters['vehicule.etat'] = $request->etat;
        }
        if ($request->filled('equipements')) {
            $filters['vehicule.equipement'] = ['$in' => $request->equipements];
        }
        // Appliquer les filtres
        $annonces = Annonce::where($filters)->get();
    
        return response()->json([
            'status' => 200,
            'data' => $annonces
        ]);
    }
    



    
    public function create(Request $request)
{
    // Check if the user is logged in
    if (!auth()->check()) {
        return response()->json([
            'status' => 401,
            'message' => 'Unauthorized. Please log in to proceed.',
        ], 401);
    }

    // Retrieve the authenticated user
    $user = auth()->user();

    // Validate the incoming data
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

        // Validate that images is an array, and that each single file is valid
        'images'                     => 'nullable|array',
        'images.*'                   => 'file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // max 5MB per file
    ]);

    // Process images if they exist
    $uploadedImages = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            // Save the image file in the "image" folder on the "public" disk
            $path = $file->store('image', 'public');

            $uploadedImages[] = [
                'chemin' => $path,
                'url'    => asset('storage/' . $path),
                'format' => $file->getClientOriginalExtension(),
                'taille' => $file->getSize()
            ];
        }
    }

    // Append additional data
    $data['Ref_id_user'] = $user->_id;
    $data['is_reported'] = false;
    $data['reported_by'] = [];

    // Override images field with our stored metadata if images were uploaded
    if (!empty($uploadedImages)) {
        $data['images'] = $uploadedImages;
    }

    // Create the annonce
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
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                // Enregistrer chaque image dans le répertoire public/images
                $path = $image->store('images', 'public'); // 'public' signifie stockage dans le dossier public/storage
                $images[] = [
                    'chemin' => asset('storage/' . $path), // Génère une URL publique pour l'image
                    'format' => $image->getClientOriginalExtension(),
                    'taille' => $image->getSize(),
                ];
            }
            // Remplacer les anciennes images par les nouvelles (si elles existent)
            $annonce->images = $images;
        }
    

        
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
