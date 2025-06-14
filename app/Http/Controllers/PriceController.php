<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PriceController extends Controller {
  public function predict(Request $req) {
    // 1) Validation des inputs bruts
    $data = $req->validate([
      'Categorie'                 => 'required|string',
      'Marque'                    => 'required|string',
      'Modèle'                    => 'required|string',
      'TypeCarburant'             => 'required|string',
      'Puissance'                 => 'required|numeric',
      'Cylindre'                  => 'required|integer',
      'DateDeMiseEnCirculation'   => 'required|date',
      'Kilométrage'               => 'required|numeric',
      'etat'                      => 'required|in:neuf,excellent,correct,endommagé',
      'boiteVitesse'              => 'required|in:manuelle,automatique',
    ]);

    // Derived features…
    $year      = (int)substr($data['DateDeMiseEnCirculation'], 0, 4);
    $age       = date('Y') - $year;
    $isAuto    = $data['boiteVitesse'] === 'automatique' ? 1 : 0;
    $w         = config('ml.brand_weights')[$data['Marque']] ?? 1.0;
    $age_brand = $age * $w;
    $age_trans = $age * $isAuto;

    $payload = array_merge($data, [
      'age'         => $age,
      'raw_km'      => $data['Kilométrage'],
      'is_automatic'=> $isAuto,
      'age_brand'   => $age_brand,
      'age_trans'   => $age_trans,
    ]);

    Log::info('→ Sending to FastAPI', ['url'=>config('services.python.url'), 'body'=>$payload]);

    $client = new Client([
      'base_uri' => config('services.python.url'),
      'timeout'  => 5,
    ]);

    try {
      $res  = $client->post('/predict', ['json'=>$payload]);
      $body = json_decode($res->getBody(), true);

      return response()->json([
        'success'     => true,
        'prix_estime' => $body['prix_estime']
      ]);
    }
    catch (GuzzleException $e) {
      // Log message, code and full trace
      Log::error('FastAPI request failed', [
        'message' => $e->getMessage(),
        'code'    => $e->getCode(),
        'trace'   => $e->getTraceAsString()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Service indisponible: '.$e->getMessage()
      ], 500);
    }
  }
}
