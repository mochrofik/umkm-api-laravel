<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function getNearby(Request $request)
    {

        try {
            $userLat = $request->input('lat');
            $userLng = $request->input('lng');
            $radius = 20; // Radius dalam KM

            if (!$userLat || !$userLng) {
                return response()->json(['message' => 'Latitude dan Longitude wajib diisi'], 400);
            }

            $lokasiTerdekat = Store::selectRaw("id, name, logo, rating, description, latitude, longitude, 
            ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) 
            * cos( radians( longitude ) - radians(?) ) 
            + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS jarak", [$userLat, $userLng, $userLat])
                ->having('jarak', '<', $radius)
                ->orderBy('jarak', 'asc')
                ->get();


            return $this->successResponse("Data toko terdekat", $lokasiTerdekat, 200);
        } catch (\Throwable $th) {
            Log::error("nearby toko error " . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
