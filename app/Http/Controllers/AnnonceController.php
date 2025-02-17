<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'Titre' => 'required|string|max:255',
            'Description' => 'required|string',
            'DatePub' => 'required|date',
            'Prix' => 'required|numeric',
            'isSponsored' => 'required|boolean',
            'Ref_id_admin' => 'required',
            'Ref_id_user' => 'required',
            'voiture' => 'required|array',
            'voiture.Marque' => 'required|string',
            'voiture.Modèle' => 'required|string',
            'voiture.Puissance' => 'required|numeric',
            'voiture.Année' => 'required|integer',
            'voiture.DateDeMiseEnCirculation' => 'required|date',
            'voiture.Kilométrage' => 'required|numeric',
            'voiture.Couleur' => 'required|string',
            'voiture.Energie' => 'required|string',
            'images' => 'nullable|array',
            'images.*.chemin' => 'required|string',
            'images.*.format' => 'required|string',
            'images.*.taille' => 'required|string',
        ]);
            $annonce =Annonce::create($request->all());
        // Création de l'annonce avec ses documents imbriqués
        /* $annonce = Annonce::create([
            'Titre' => $request->Titre,
            'Description' => $request->Description,
            'DatePub' => $request->DatePub,
            'Prix' => $request->Prix,
            'isSponsored' => $request->isSponsored ?? false,
            'Ref_id_admin' => $request->Ref_id_admin,
            'Ref_id_user' => $request->Ref_id_user,
            'voiture' => $request->voiture,
            'images' => $request->images ?? []
        ]); */

        return response()->json([
            'status' => 201,
            'data' => $annonce
        ]);
    }

    
   

    /**
     * Update the specified resource in storage.
     */
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
            'voiture' => 'array',
            'voiture.Marque' => 'string',
            'voiture.Modèle' => 'string',
            'voiture.Puissance' => 'numeric',
            'voiture.Année' => 'integer',
            'voiture.DateDeMiseEnCirculation' => 'date',
            'voiture.Kilométrage' => 'numeric',
            'voiture.Couleur' => 'string',
            'voiture.Energie' => 'string',
            'images' => 'nullable|array',
            'images.*.chemin' => 'string',
            'images.*.format' => 'string',
            'images.*.taille' => 'string',
        ]);

        // Mise à jour des champs modifiables
        $annonce->update($request->all());

        return response()->json([
            'status' => 201,
            'data' => $annonce
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
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
