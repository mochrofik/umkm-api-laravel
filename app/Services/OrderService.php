<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class OrderService
{
    /**
     * Fetch incoming orders for a specific store.
     *
     * @param array $filters
     * @param int $storeId
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function fetchIncomingOrders(array $filters, $storeId)
    {
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $status = $filters['status'] ?? null;

        return Order::query()
            ->where('store_id', $storeId)
            ->when($status, function ($query, $status) {
                if ($status != "all") {
                    $query->where('status', $status);
                }
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($sub) use ($search) {
                            $sub->whereHas('user', function ($u) use ($search) {
                                $u->where('name', 'like', "%{$search}%");
                            });
                        });
                });
            })
            ->with(['customer.user', 'items.product'])
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     * Update the status of an order.
     *
     * @param int $orderId
     * @param string $status
     * @param int $storeId
     * @return Order
     * @throws \Exception
     */
    public function updateOrderStatus($orderId, $status, $storeId)
    {
        $validator = Validator::make(['status' => $status], [
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $order = Order::where('id', $orderId)
            ->where('store_id', $storeId)
            ->firstOrFail();

        $order->status = $status;
        $order->save();

        return $order;
    }

    /**
     * Create a new order for a customer.
     *
     * @param array $data
     * @param int $customerId
     * @return Order
     * @throws \Exception
     */
    public function createOrder(array $data, $customerId)
    {
        $validator = Validator::make($data, [
            'store_id'         => 'required|exists:stores,id',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'payment_method'   => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'notes'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data, $customerId) {
            $storeId = $data['store_id'];
            $items = $data['items']; // Expected: [['product_id' => 1, 'quantity' => 2], ...]

            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $totalPrice += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'customer_id' => $customerId,
                'store_id' => $storeId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'delivery_address' => $data['delivery_address'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order->load(['items.product', 'store']);
        });
    }

    /**
     * Fetch order history for a customer.
     *
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchCustomerOrders($customerId)
    {
        return Order::where('customer_id', $customerId)
            ->with(['store', 'items.product'])
            ->latest()
            ->get();
    }
}
