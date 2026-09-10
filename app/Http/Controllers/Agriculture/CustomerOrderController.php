<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function storeCustomerOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trading_party_id' => 'required|exists:cari_taraflar,id',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date',
            'contact_person' => 'nullable|string|max:100',
            'total_amount' => 'numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $order = CustomerOrder::create($validated);

            if ($request->has('items') && is_array($request->items)) {
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    if (!empty($item['product_id'])) {
                        $qty = max(0.01, floatval($item['quantity'] ?? 0));
                        $price = max(0, floatval($item['unit_price'] ?? 0));
                        $lineTotal = $qty * $price;
                        $totalAmount += $lineTotal;
                        $order->items()->create([
                            'product_id' => $item['product_id'],
                            'packaging_definition_id' => $item['packaging_definition_id'] ?? null,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_price' => $lineTotal,
                        ]);
                    }
                }
                if ($totalAmount > 0) {
                    $order->update(['total_amount' => $totalAmount]);
                }
            }
        });

        return redirect()->back()->with('success', 'Sipariş başarıyla oluşturuldu.');
    }

    public function updateCustomerOrder(Request $request, CustomerOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'trading_party_id' => 'required|exists:cari_taraflar,id',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date',
            'contact_person' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,confirmed,shipped,cancelled',
        ]);

        DB::transaction(function () use ($request, $validated, $order) {
            $order->update($validated);

            if ($request->has('items') && is_array($request->items)) {
                $order->items()->delete();
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    if (!empty($item['product_id'])) {
                        $qty = max(0.01, floatval($item['quantity'] ?? 0));
                        $price = max(0, floatval($item['unit_price'] ?? 0));
                        $lineTotal = $qty * $price;
                        $totalAmount += $lineTotal;
                        $order->items()->create([
                            'product_id' => $item['product_id'],
                            'packaging_definition_id' => $item['packaging_definition_id'] ?? null,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_price' => $lineTotal,
                        ]);
                    }
                }
                $order->update(['total_amount' => $totalAmount]);
            }
        });

        return redirect()->back()->with('success', 'Sipariş başarıyla güncellendi.');
    }

    public function updateOrderStatus(Request $request, CustomerOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Sipariş durumu güncellendi.');
    }

    public function destroyCustomerOrder(CustomerOrder $order): RedirectResponse
    {
        if ($order->shipments()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Bu siparişe bağlı sevkiyat kayıtları bulunmaktadır. Önce ilgili sevkiyatları silmelisiniz.']);
        }

        $order->delete();
        return redirect()->back()->with('success', 'Sipariş kaydı silindi.');
    }

}
