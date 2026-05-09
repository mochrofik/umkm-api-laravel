<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get the customer instance for the logged-in user.
     */
    private function getCustomer()
    {
        $auth = Auth::user();
        $customer = Customer::where('user_id', $auth->id)->first();
        
        if (!$customer) {
            throw new \Exception("Data pelanggan tidak ditemukan!");
        }
        
        return $customer;
    }

    /**
     * Display the current cart items.
     */
    public function index()
    {
        try {
           
            $userId = Auth::id();
            $cart = $this->cartService->getCart($userId);
            return $this->successResponse("Data keranjang berhasil diambil", $cart, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), null, 404);
        }
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request)
    {
        try {
            $customer = $this->getCustomer();
            $item = $this->cartService->addToCart(
                $customer->id,
                $request->product_id,
                $request->quantity ?? 1
            );
            return $this->successResponse("Produk berhasil ditambahkan ke keranjang", $item, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse("Validasi gagal", $e->errors(), 422);
        } catch (\Throwable $th) {
            Log::error("Add to cart error: " . $th->getMessage());
            return $this->errorResponse("Gagal menambahkan ke keranjang", $th->getMessage(), 500);
        }
    }

    /**
     * Update an item in the cart.
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = $this->getCustomer();
            $item = $this->cartService->updateCartItem($customer->id, $id, $request->quantity);
            return $this->successResponse("Keranjang berhasil diperbarui", $item, 200);
        } catch (ValidationException $e) {
            return $this->errorResponse("Validasi gagal", $e->errors(), 422);
        } catch (\Throwable $th) {
            return $this->errorResponse("Gagal memperbarui keranjang", $th->getMessage(), 500);
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy($id)
    {
        try {
            $customer = $this->getCustomer();
            $this->cartService->removeFromCart($customer->id, $id);
            return $this->successResponse("Produk berhasil dihapus dari keranjang", null, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Gagal menghapus produk dari keranjang", $th->getMessage(), 500);
        }
    }

    /**
     * Clear the cart.
     */
    public function clear()
    {
        try {
            $customer = $this->getCustomer();
            $this->cartService->clearCart($customer->id);
            return $this->successResponse("Keranjang berhasil dikosongkan", null, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Gagal mengosongkan keranjang", $th->getMessage(), 500);
        }
    }
}
