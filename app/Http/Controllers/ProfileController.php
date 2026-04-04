<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $auth = Auth::user();

            $user = User::where('id', $auth->id)
                ->with('getStore')
                ->first();

            return $this->successResponse("Data profile", $user, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data profile gagal", $th, 500);
        }
    }

    public function update(Request $request)
    {

        try {
            if (isset($request->id_user) && $request->id_user != null) {
            }
            return $request;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error update profile " . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
