<?php

namespace App\Http\Controllers;
use App\Models\Contrat;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function getContrat(){
        return response()->json([
            'status'=>200,
            'data'=>Contrat::all()
        ]);
    }

    public function getContratById($id){
        $contrat = Contrat::find($id);
        if(is_null($contrat)){
            return response()->json([
               'status'=>404,
                'data'=>null
            ]);
        }
        return response()->json([
            'status'=>200,
            'data'=>$contrat
        ]);
    }

    public function createContrat(Request $request){
        $request->validate([
            'typeContrat'=>'required|string',
            'dateSignature'=>'required|date',
            'Ref_id_paiement'=>'required'
        ]);
        $utilisateur = \App\Models\Utilisateur::where('paiements', 'elemMatch', ['id_paiement' => $request->Ref_id_paiement])->first();

    if (!$utilisateur) {
        return response()->json(['error' => 'Le paiement spécifié n\'existe pas.'], 404);
    }
    $contrat = Contrat::create($request->all());
    return response()->json([
       'status'=>201,
        'data'=>$contrat
    ]);
    }

    public function updateContrat($id, Request $request){
        $contrat = Contrat::find($id);
        if(is_null($contrat)){
            return response()->json([
               'status'=>404,
                'data'=>null
            ]);
        }
        $contrat->update($request->all());
        return response()->json([
            'status'=>200,
            'data'=>$contrat
        ]);
    }

    public function deleteContrat($id){
        $contrat = Contrat::find($id);
        if(is_null($contrat)){
            return response()->json([
               'status'=>404,
                'data'=>null
            ]);
        }
        $contrat->delete();
        return response()->json([
           'status'=>200,
            'data'=>null
        ]);
    }



}
