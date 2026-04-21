<?php

namespace App\Services;

use App\Helpers\DeleteImageHelper;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    protected string $staticPath = 'uploads/customer';

    /**
     * Fetch customers with filters and pagination.
     */
    public function fetchCustomers(array $filters)
    {
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? null;
        $status = $filters['status'] ?? null;

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
            return $query->paginate($limit)->withQueryString();
        }

        return $query->get();
    }

    /**
     * Add or update a customer and their user account.
     */
    public function addOrUpdateCustomer(array $data, $id = null)
    {
        return DB::transaction(function () use ($data, $id) {
            if ($id) {
                $user = User::findOrFail($id);
                $cust = Customer::where('user_id', $id)->firstOrFail();

                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }
                
                if (!empty($data['email']) && $user->email != $data['email']) {
                    $user->email = $data['email'];
                }
            } else {
                $user = new User();
                $user->email = $data['email'];
                $user->password = Hash::make($data['password'] ?? 'password'); // Default password if not provided
            }

            $user->name = $data['name'];
            $user->role = $data['role'] ?? 'customer';
            $user->status = $data['status'] ?? 'active';
            $user->save();

            if (!$id) {
                $user->assignRole('customer');
                $cust = new Customer();
            }

            $cust->user_id = $user->id;
            $cust->nik = $data['nik'] ?? null;
            $cust->phone_number = $data['phone_number'];
            $cust->gender = $data['gender'];
            $cust->date_of_birth = $data['date_of_birth'] ?? null;
            $cust->address = $data['address'] ?? null;
            $cust->postal_code = $data['postal_code'] ?? null;
            $cust->latitude = $data['latitude'] ?? null;
            $cust->longitude = $data['longitude'] ?? null;
            $cust->is_open = $data['is_open'] ?? null;

            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                DeleteImageHelper::deleteOldImage($cust->avatar, $this->staticPath);

                $file = $data['avatar'];
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs($this->staticPath, $filename, 'public');
                $cust->avatar = $filename;
            }

            $cust->save();

            return $cust;
        });
    }

    /**
     * Delete a customer and their user account.
     */
    public function deleteCustomer($id)
    {
        return DB::transaction(function () use ($id) {
            $cust = Customer::findOrFail($id);
            DeleteImageHelper::deleteOldImage($cust->avatar, $this->staticPath);
            
            $user = User::find($cust->user_id);
            if ($user) {
                $user->delete();
            }
            
            $cust->delete();
            return $cust;
        });
    }
}
