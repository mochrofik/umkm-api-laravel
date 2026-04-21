<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create a cart for a customer.
     */
    public function getOrCreateCart($customerId)
    {
        $cart = Cart::firstOrCreate(['customer_id' => $customerId]);
        return $cart->load(['items.product.store']);
    }

    /**
     * Get cart with items and products.
     */
    public function getCart($customerId)
    {
        return $this->getOrCreateCart($customerId);
    }

    /**
     * Add a product to the cart.
     */
    public function addToCart($customerId, $productId, $quantity = 1)
    {
        return DB::transaction(function () use ($customerId, $productId, $quantity) {
            $cart = $this->getOrCreateCart($customerId);
            
            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->first();

            if ($item) {
                $item->quantity += $quantity;
                $item->save();
            } else {
                $item = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }

            return $item->load('product.store');
        });
    }


    /**
     * Update quantity of an item in the cart.
     */
    public function updateCartItem($customerId, $itemId, $quantity)
    {
        $cart = $this->getOrCreateCart($customerId);
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);
        
        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->quantity = $quantity;
        $item->save();

        return $item->load('product');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeFromCart($customerId, $itemId)
    {
        $cart = $this->getOrCreateCart($customerId);
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);
        $item->delete();
        
        return true;
    }

    /**
     * Clear all items in the cart.
     */
    public function clearCart($customerId)
    {
        $cart = $this->getOrCreateCart($customerId);
        $cart->items()->delete();
        
        return true;
    }
}
