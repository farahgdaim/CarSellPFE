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
    public function create(Request $request)
    {
        // Validation des données
        $data= $request->validate([
            'Titre' => 'required|string|max:255',
            'Description' => 'required|string',
            'DatePub' => 'required|date',
            'Prix' => 'required|numeric',
            
            
            'Ref_id_user' => 'required',

            'vehicule' => 'required|array',
            'vehicule.Categorie' => 'required|string',
            'vehicule.Marque' => 'required|string',
            'vehicule.Modèle' => 'required|string',
            'vehicule.TypeCarburant' => 'required|string|in:Essence,Diesel,GPL,Electrique,Hybride',
            'vehicule.Puissance' => 'required|numeric',
            'vehicule.DateDeMiseEnCirculation' => 'required|date',
            'vehicule.Cylindre'=> 'required|string',
            'vehicule.Kilométrage' => 'required|numeric',
            'vehicule.nbPortes'=> 'required|string',
            'vehicule.boiteVitesse'=> 'required|string|in:automatique,manuelle',
            'vehicule.etat'=> 'required|string|in:neuf,excellent,correct,endommagé',
            'vehicule.equipement' => 'required|string',
            
            'images' => 'nullable|array',
            'images.*.chemin' => 'required|string',
            'images.*.format' => 'required|string',
            'images.*.taille' => 'required|string',
        ]);
        //$data['Ref_id_user'] = auth()->user()->_id; // Si tu utilises le modèle Utilisateur
            $data['is_reported']= false ;
            $data ['reported_by']=[];
            $annonce =Annonce::create($data);
        

        return response()->json([
            'status' => 201,
            'data' => $annonce
        ]);
    }

    
   public function report ($id){
    $annonce = Annonce::find($id);
    $userId = auth()->user()->_id;
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
