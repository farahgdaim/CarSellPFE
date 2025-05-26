<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Annonce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnnonceController extends Controller
{

    public function getAnnonce()
    {
        return response()->json([
            'status' => 200,
            'data' => Annonce::all()
        ]);
    }

    public function getAnnonce_enAttente()
    {

        $annonces = Annonce::where('status', 'en attente')->get();

        return response()->json([
            'status' => 200,
            'data' => $annonces,
        ]);
    }

    public function getAnnonceById($id)
    {
        $annonce = Annonce::find($id);
        if (is_null($annonce)) {
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        return response()->json([
            'status' => '200',
            'data' => $annonce
        ]);
    }




    public function search(Request $request)
    {
        $filters = [];

        if ($request->filled('marque')) {
            $filters['vehicule.Marque'] = $request->query('marque'); // Correctement récupéré
        }
        if ($request->filled('modele')) {
            $filters['vehicule.Modèle'] = $request->query('modele');
        }
        if ($request->filled('puissance')) {
            $filters['vehicule.Puissance'] = ['$gte' => (int) $request->query('puissance')];
        }
        if ($request->filled('kilometrage')) {
            $filters['vehicule.Kilométrage'] = ['$lte' => (int) $request->query('kilometrage')];
        }
        if ($request->filled('energie')) {
            $filters['vehicule.TypeCarburant'] = $request->query('energie');
        }
        if ($request->filled('boiteVitesse')) {
            $filters['vehicule.boiteVitesse'] = $request->query('boiteVitesse');
        }
        if ($request->filled('etat')) {
            $filters['vehicule.etat'] = $request->query('etat');
        }
        if ($request->filled('equipements')) {
            $filters['vehicule.equipement'] = ['$in' => (array) $request->query('equipements')];
        }

        // Vérification des filtres
        if (empty($filters)) {
            return response()->json([
                'status' => 400,
                'message' => 'Aucun filtre fourni'
            ]);
        }

        // Exécution de la requête MongoDB
        $annonces = Annonce::where($filters)->get();

        // Vérifier si des annonces sont trouvées
        if ($annonces->isEmpty()) {
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }

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

            // Validate that images is an array, and that each single file is valid.
            'images'                     => 'nullable|array',
            'images.*'                   => 'file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // max 5MB per file
        ]);


        // Process images if they exist.
        $uploadedImages = [];

        if ($request->hasFile('images')) {
            $flaskUrl = env('FLASK_URL', 'http://localhost:5000/flouter');
            $multipart = [];

            foreach ($request->file('images') as $file) {
                $multipart[] = [
                    'name' => 'images',
                    'contents' => fopen($file->path(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ];
            }

            $response = Http::timeout(120)->attach($multipart)->post($flaskUrl);

            if ($response->failed()) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Échec du traitement des images: ' . $response->body()
                ], 500);
            }

            $responseData = $response->json();

            if (isset($responseData['error'])) {
                return response()->json([
                    'status' => 500,
                    'message' => $responseData['error']
                ], 500);
            }

            foreach ($responseData['resultats'] as $result) {
                $imageResponse = Http::get($result['url']);

                if (!$imageResponse->successful()) {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Échec du téléchargement de l\'image traitée'
                    ], 500);
                }

                $path = 'blurred_images/' . $result['fichier'];
                Storage::disk('public')->put($path, $imageResponse->body());

                $uploadedImages[] = [
                    'chemin' => $path,
                    'url'    => asset('storage/' . $path),
                    'format' => pathinfo($result['fichier'], PATHINFO_EXTENSION),
                    'taille' => Storage::disk('public')->size($path)
                ];
            }
        }





        // Append additional data
        $data['Ref_id_user'] = $user->_id;
        $data['is_reported'] = false;
        $data['status'] = 'en attente';
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



    public function update($id, Request $request)
    {
       

        $annonce = Annonce::find($id);
        if (!$annonce) {
            return response()->json([
                'status' => 404,
                'data' => null
            ]);
        }
        /*$data=$request->all();
         return response()->json([
                'status' => 404,
                'data' => $data['images']
            ]); */

           $data = $request->validate([
            'titre' => 'string|max:255',
            'description' => 'string',
            'DatePub' => 'date',
            'prix' => 'numeric',
            'isSponsored' => 'boolean',
            'status' => 'string|in:en attente,vendue',

            'vehicule' => 'array',
            'vehicule.Categorie' => 'string',
            'vehicule.Marque' => 'string',
            'vehicule.Modèle' => 'string',
            'vehicule.TypeCarburant' => 'string|in:Essence,Diesel,GPL,Electrique,Hybride',
            'vehicule.Puissance' => 'string',
            'vehicule.DateDeMiseEnCirculation' => 'date',
            'vehicule.Cylindre' => 'string',
            'vehicule.Kilométrage' => 'numeric',
            'vehicule.nbPortes' => 'numeric',
            'vehicule.boiteVitesse' => 'string|in:automatique,manuelle',
            'vehicule.etat' => 'string|in:neuf,excellent,correct,endommagé',
            'vehicule.equipement' => 'string',

            /* 'images'                     => 'nullable|array',
            'images.*'                   => 'file|mimes:jpeg,png,jpg,gif,pdf|max:5120', */
        ]); 

        /*  return response()->json([
            'status' => 201,
            'data' =>  $data['description']
        ]); */
        /* $uploadedImages = [];


        if ($request->hasFile('images')) {
            $flaskUrl = env('FLASK_URL', 'http://localhost:5000/flouter');
            $multipart = [];

            foreach ($request->file('images') as $file) {
                $multipart[] = [
                    'name' => 'images',
                    'contents' => fopen($file->path(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ];
            }

            $response = Http::timeout(120)->attach($multipart)->post($flaskUrl);


            if ($response->failed()) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Échec du traitement des images: ' . $response->body()
                ], 500);
            }

            $responseData = $response->json();

            if (isset($responseData['error'])) {
                return response()->json([
                    'status' => 500,
                    'message' => $responseData['error']
                ], 500);
            }

            foreach ($responseData['resultats'] as $result) {
                $imageResponse = Http::get($result['url']);

                if (!$imageResponse->successful()) {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Échec du téléchargement de l\'image traitée'
                    ], 500);
                }

                $path = 'blurred_images/' . $result['fichier'];
                Storage::disk('public')->put($path, $imageResponse->body());

                $uploadedImages[] = [
                    'chemin' => $path,
                    'url'    => asset('storage/' . $path),
                    'format' => pathinfo($result['fichier'], PATHINFO_EXTENSION),
                    'taille' => Storage::disk('public')->size($path)
                ];
            }
            return response()->json([
                'status' => 600,
                'message' => $uploadedImages
            ]);

      
            $data['images'] = $uploadedImages;
        }
 */

        // Mise à jour des champs simples
        /*  $annonce->fill($request->except(['vehicule', 'images']));

        // Mise à jour des champs spécifiques dans le sous-document `vehicule`
        if ($request->has('vehicule')) {
            foreach ($request->input('vehicule') as $key => $value) {
                $annonce->vehicule[$key] = $value;
            }
        } */

      /* return response()->json([
            'data' => $uploadedImages
        ]);  */
        $annonce->update($data);

        // Log::info("Données de l'annonce :", $annonce);
        return response()->json([
            'status' => 201,
            'data' => $annonce
        ]);
    }

    public function updateImages($id, Request $request)
{
    $annonce = Annonce::find($id);

    if (!$annonce) {
        return response()->json([
            'status' => 404,
            'message' => 'Annonce non trouvée'
        ]);
    }

    $request->validate([
        'images' => 'sometimes|array',
        'images.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,pdf|max:5120'
    ]);

    $uploadedImages = [];

    if ($request->hasFile('images')) {
        $flaskUrl = env('FLASK_URL', 'http://localhost:5000/flouter');
        $multipart = [];

        foreach ($request->file('images') as $file) {
            $multipart[] = [
                'name' => 'images',
                'contents' => fopen($file->path(), 'r'),
                'filename' => $file->getClientOriginalName()
            ];
        }

        $response = Http::timeout(120)->attach($multipart)->post($flaskUrl);

        if ($response->failed()) {
            return response()->json([
                'status' => 500,
                'message' => 'Échec du traitement des images: ' . $response->body()
            ]);
        }

        $responseData = $response->json();

        if (isset($responseData['error'])) {
            return response()->json([
                'status' => 500,
                'message' => $responseData['error']
            ]);
        }

        foreach ($responseData['resultats'] as $result) {
            $imageResponse = Http::get($result['url']);

            if (!$imageResponse->successful()) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Échec du téléchargement de l\'image traitée'
                ]);
            }

            $path = 'blurred_images/' . $result['fichier'];
            Storage::disk('public')->put($path, $imageResponse->body());

            $uploadedImages[] = [
                'chemin' => $path,
                'url'    => asset('storage/' . $path),
                'format' => pathinfo($result['fichier'], PATHINFO_EXTENSION),
                'taille' => Storage::disk('public')->size($path)
            ];
        }

        // Ici, tu peux enregistrer les images dans ton modèle si besoin :
        $annonce->images = $uploadedImages;
        $annonce->save();

        return response()->json([
            'status' => 201,
            'message' => 'Images mises à jour',
            'data' => $uploadedImages
        ]);
    }
     return response()->json([
        'status' => 200,
        'message' => 'Aucune image à mettre à jour'
    ]);

    /* return response()->json([
        'status' => 400,
        'message' => 'Aucun fichier reçu'
    ]); */
}




    public function destroy($id)
    {
        $annonce = Annonce::find($id);
        if (!$annonce) {
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

    public function getMarques()
    {
        // Effectue une agrégation pour compter le nombre d'annonces par marque
        $marques = Annonce::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => ['_id' => '$vehicule.Marque', 'count' => ['$sum' => 1]]],
                ['$sort' => ['count' => -1]] // Trie les résultats du plus grand au plus petit
            ]);
        });

        return response()->json($marques); // Retourne le résultat en JSON
    }


    public function getModeles(Request $request)
    {
        $marque = $request->query('marque'); // Récupère la marque envoyée en GET

        if (!$marque) {
            return response()->json(['error' => 'Marque is required'], 400);
        }

        // Récupère les modèles distincts pour la marque spécifiée
        $modeles = Annonce::raw(function ($collection) use ($marque) {
            return $collection->distinct('vehicule.Modèle', ['vehicule.Marque' => $marque]);
        });

        return response()->json($modeles);
    }

    public function mesAnnonces()
    {
        $userId = auth()->id(); // Récupérer l'ID de l'utilisateur connecté

        $annonces = Annonce::where('Ref_id_user', $userId)->get();

        return response()->json([
            'status' => 200,
            'data' => $annonces
        ]);
    }
}
