<?php

namespace App\Http\Controllers;


use App\Models\Sponsorship;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    public function getSponsorship(){
        return response()->json([
            'status'=>200,
            'data'=>Sponsorship::all()
        ]);
    }

    public function getSponsorshipById($id){
        $sponsorship = Sponsorship::find($id);
        if(is_null($sponsorship)){
            return response()->json([
               'status'=>404,
                'data'=>null
            ]);
        }
        return response()->json([
           'status'=>200,
            'data'=>$sponsorship
        ]);
    }

    public function createSponsorship(Request $request){
        $request->validate([
            'DateDebut'=>'required|date',
            'DateFin'=>'required|date',
            'id_annonce'=>'required|exists:annonces,_id',
            'id_sponsoring'=>'required|exists:sponsoring,_id'
        ]);
        $sponsorship = Sponsorship::create($request->all());
        return response()->json([
           'status'=>201,
            'data'=>$sponsorship
        ]); 
    }

    public function updateSponsorship($id, Request $request){
        $sponsorship = Sponsorship::find($id);
        if(is_null($sponsorship)){
            return response()->json([
               'status'=>404,
                'data'=>'Sponsorship introuvable'
            ]);
        }
        $sponsorship->update($request->all());
        return response()->json([
           'status'=>200,
            'data'=>$sponsorship
        ]);
    }

    public function deleteSponsorship($id){
        $sponsorship = Sponsorship::find($id);
        if(is_null($sponsorship)){
            return response()->json([
               'status'=>404,
                'data'=>'Sponsorship introuvable'
            ]);
        }
        $sponsorship->delete();
        return response()->json([
           'status'=>204,
            'data'=>'Sponsorship deleted successfully'
        ]);  
    }
}
