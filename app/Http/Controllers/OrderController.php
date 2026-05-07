<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Store;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get incoming orders for the logged-in store.
     */
    public function getIncomingOrders(Request $request)
    {
        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();

            if (!$store) {
                return $this->errorResponse("Data toko tidak ditemukan!", null, 404);
            }

            $orders = $this->orderService->fetchIncomingOrders($request->all(), $store->id);
            return $this->successResponse("Data pesanan masuk berhasil diambil", $orders, 200);
        } catch (\Throwable $th) {
            Log::error("fetch incoming orders error: " . $th->getMessage());
            return $this->errorResponse("Terjadi kesalahan sistem", $th->getMessage(), 500);
        }
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();

            if (!$store) {
                return $this->errorResponse("Data toko tidak ditemukan!", null, 404);
            }

            $order = $this->orderService->updateOrderStatus($id, $request->status, $store->id);
            return $this->successResponse("Status pesanan berhasil diperbarui", $order, 200);
        } catch (ValidationException $e) {
            return $this->errorResponse("Validasi gagal", $e->errors(), 422);
        } catch (\Throwable $th) {
            Log::error("update order status error: " . $th->getMessage());
            return $this->errorResponse("Terjadi kesalahan sistem", $th->getMessage(), 500);
        }
    }

    /**
     * Place a new order (Checkout).
     */
    public function checkout(Request $request)
    {
        try {
            $auth = Auth::user();
            $customer = Customer::where('user_id', $auth->id)->first();

            if (!$customer) {
                return $this->errorResponse("Data pelanggan tidak ditemukan!", null, 404);
            }

            $order = $this->orderService->createOrder($request->all(), $customer->id);
            return $this->successResponse("Pesanan berhasil dibuat", $order, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse("Validasi gagal", $e->errors(), 422);
        } catch (\Throwable $th) {
            Log::error("checkout error: " . $th->getMessage());
            return $this->errorResponse("Terjadi kesalahan sistem", $th->getMessage(), 500);
        }
    }

    /**
     * Get order history for the logged-in customer.
     */
    public function customerOrderHistory()
    {
        try {
            $auth = Auth::user();
            $customer = Customer::where('user_id', $auth->id)->first();

            if (!$customer) {
                return $this->errorResponse("Data pelanggan tidak ditemukan!", null, 404);
            }

            $orders = $this->orderService->fetchCustomerOrders($customer->id);
            return $this->successResponse("Riwayat pesanan berhasil diambil", $orders, 200);
        } catch (\Throwable $th) {
            Log::error("customer order history error: " . $th->getMessage());
            return $this->errorResponse("Terjadi kesalahan sistem", $th->getMessage(), 500);
        }
    }
}
