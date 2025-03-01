<?php

namespace App\Http\Controllers;

use App\Models\Sponsoring;
use Illuminate\Http\Request;

class SponsoringController extends Controller
{
    public function getSponsoring()
    {
        return response()->json([
            'status' => 200,
            'data' =>Sponsoring::all()
        ]);
    }

    public function getSponsoringById($id){
        $sponsoring = Sponsoring::find($id);
        if(is_null($sponsoring)){
            return response()->json([
               'status' => 404,
                'data' => null
            ]);
        }
        return response()->json($sponsoring,200);
    }

    public function create (Request $request){
        $request->validate([
            'nomSponsoring'=>'required|string|in:premium,gold,standard',
            'description'=>'required|string',
            'prix'=>'required|string',
            'durée'=>'required|string',
            'id_annonce' => 'required|exists:annonces,_id'
        ]);
        $sponsoring = Sponsoring::create($request->all());
        return response()->json([
            'status' => 201,
            'data' => $sponsoring
        ]);

    }
    public function update($id, Request $request){
        $sponsoring = Sponsoring::find($id);
        if(!$sponsoring){
            return response()->json([
               'status' => 404,
                'data' => null
            ]);
        }
        $request->validate([
           'nomSponsoring'=>'string|in:premium,gold,standard',
        ]);
        $sponsoring->update($request->all());
        return response()->json([
            'status' => 201,
            'data' => $sponsoring
        ]);
    }
    public function destroy($id){
        $sponsoring=Sponsoring::find($id);
        if(!$sponsoring){
            return response()->json([
                'status' => 404,
                'data' => null
            ]);

    }
    $sponsoring->delete();
    return response()->json([
        'status' => 204,
        'data' => null
    ]);

    }

}
