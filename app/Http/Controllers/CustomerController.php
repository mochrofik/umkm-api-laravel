<?php

namespace App\Http\Controllers;

use App\Helpers\DeleteImageHelper;
use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

            $lokasiTerdekat = Store::selectRaw("id, name, slug, logo, rating, description, latitude, longitude, 
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
    public function fetch(Request $request)
    {
        try {
            $search = $request->query('search');
            $limit = $request->query('limit');
            $status = $request->query('status');

            $query = Customer::query()
                ->when($status, function ($query, $status) {
                    $query->whereHas('user', function ($query) use ($status) {
                        if ($status != "all") {
                            $query->where('status', $status);
                        }
                    });
                })
                ->when($search, function ($query, $search) {
                    $query->whereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                })
                ->with("user")
                ->latest();

            if ($limit) {
                $categories = $query->paginate($limit)->withQueryString();
            } else {
                $categories = $query->get();
            }

            return $this->successResponse("Data pelanggan berhasil diambil", $categories, 200);
        } catch (\Throwable $th) {
            Log::error("fetch custo " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th, 500);
        }
    }
    public function addEdit(Request $request)
    {


        $validator =  Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_number' => 'required|string|max:15',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullabel|date_format:Y-m-d',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:5',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'role'     => 'required|in:admin,store,customer',
            'status'   => 'required|in:active,verify,banned',
        ]);

        if ($validator->failed()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        if (!isset($request->id) || $request->id == null) {

            $checkEmail = User::where('email', $request->email)->first();
            if ($checkEmail != null) {
                return $this->errorResponse("Validasi Gagal Email Sudah digunakan", null, 422);
            }
        }

        DB::beginTransaction();
        try {
            if (isset($request->id) && $request->id != null) {
                $user = User::where("id", $request->id)->first();
                $cust = Customer::where('user_id', $request->id)->first();

                if ($request->password != null) {
                    if ($user->password != $request->password) {
                        $user->password = $request->password;
                    }
                }
                if ($request->email != null && $user->email != $request->email) {
                    $user->email = $request->email;
                }
            } else {
                $user =  new User();
                $user->password = Hash::make($request->password);
                $user->email = $request->email;
            }

            $user->name =      $request->name;
            $user->role = $request->role;
            $user->status   = $request->status;
            $user->save();

            if (!isset($request->id) || $request->id == null) {
                $user->assignRole('customer');
                $cust = new Customer();
            }

            $cust->user_id = $user->id;
            $cust->nik = $request->nik;
            $cust->phone_number = $request->phone_number;
            $cust->gender = $request->gender;
            $cust->date_of_birth = $request->date_of_birth;
            $cust->address = $request->address;
            $cust->postal_code = $request->postal_code;
            $cust->latitude = $request->latitude;
            $cust->longitude = $request->longitude;

            $staticPath = 'uploads/customer';
            if ($request->hasFile('avatar')) {
                DeleteImageHelper::deleteOldImage($cust->avatar, $staticPath);

                $file = $request->file('avatar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs($staticPath, $filename, 'public');
                $cust->avatar = $filename;
            }

            $cust->save();
            DB::commit();
            return $this->successResponse('Berhasil menambahkan data pelanggan',  $cust, 201);
        } catch (\Throwable $th) {
            DeleteImageHelper::deleteOldImage($filename, $staticPath);
            DB::rollBack();
            Log::error("add edit customer error " . $th->getLine() . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cust = Customer::findOrFail($id);
            $staticPath = 'uploads/customer';
            DeleteImageHelper::deleteOldImage($cust->avatar, $staticPath);
            User::find($cust->user_id)->delete();
            $cust->delete();
            return $this->successResponse("Data pelanggan berhasil dihapus", $cust, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data pelanggan gagal dihapus", $th, 500);
        }
    }

    public function storeByCategory(Request $request)
    {
        try {

            $filter = $request->category;
            $userLat = $request->input('lat');
            $userLng = $request->input('lng');
            $category = str_replace('-', '%', $filter);

            $store = Store::select('*')
                ->selectRaw("id, name, slug, logo, rating, description, latitude, longitude, 
            ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) 
            * cos( radians( longitude ) - radians(?) ) 
            + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS jarak", [$userLat, $userLng, $userLat])
                ->where(function ($q) use ($category) {
                    $q->whereHas('menuCategories', function ($sub) use ($category) {
                        $sub->where(function ($child) use ($category) {
                            $child->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($category) . "%"]);
                        });
                    })
                        ->orWhereHas('store_categories', function ($sub) use ($category) {
                            $sub->where(function ($child) use ($category) {
                                $child->whereHas('categories', function ($query) use ($category) {
                                    $query->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($category) . "%"]);
                                })
                                    ->orWhereRaw('LOWER(name) LIKE ?', ["%" . strtolower($category) . "%"]);
                            });
                        })->orWhereHas('getProducts', function ($sub) use ($category) {
                            $sub->where(function ($child) use ($category) {
                                $child->whereRaw('LOWER(name) LIKE ? ', ["%" . strtolower(($category) . "%")]);
                            })->orWhereHas('tags', function ($child) use ($category) {
                                $child->where(function ($sub) use ($category) {
                                    $sub->whereRaw('LOWER(tag_name) LIKE ?', ["%" . strtolower($category) . "%"]);
                                });
                            });
                        })
                        ->with('getProducts.tags')
                        ->with('store_categories.categories')
                    ;
                })->get();

            return $this->successResponse("Data store", $store, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
