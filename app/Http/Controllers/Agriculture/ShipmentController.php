<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\ShipmentDelivery;
use App\Models\CustomerOrder;

class ShipmentController extends Controller
{
    public function storeShipmentDelivery(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_order_id' => 'nullable|exists:musteri_siparisleri,id',
            'trading_party_id' => 'required|exists:cari_taraflar,id',
            'product_id' => 'required|exists:urunler,id',
            'packaging_definition_id' => 'nullable|exists:paketleme_tanimlari,id',
            'shipment_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'numeric|min:0',
            'delivery_type_id' => 'nullable|exists:teslimat_sekilleri,id',
            'vehicle_plate' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:50',
            'dia_waybill_code' => 'nullable|string|max:100',
            'status' => 'required|in:on_the_way,delivered',
            'notes' => 'nullable|string',
        ]);

        $shipment = ShipmentDelivery::create($validated);

        if (!empty($validated['customer_order_id'])) {
            $order = CustomerOrder::with(['items', 'shipments'])->find($validated['customer_order_id']);
            if ($order) {
                $totalOrderQty = $order->items->sum('quantity');
                $totalShippedQty = $order->shipments->sum('quantity');
                if ($totalOrderQty > 0 && $totalShippedQty >= $totalOrderQty) {
                    $order->update(['status' => 'shipped']);
                } else if ($order->status === 'pending') {
                    $order->update(['status' => 'confirmed']);
                }
            }
        }

        return redirect()->back()->with('success', 'Sevkiyat ve irsaliye kaydı oluşturuldu.');
    }

    public function updateShipmentStatus(Request $request, ShipmentDelivery $shipment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:on_the_way,delivered',
        ]);

        $shipment->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Sevkiyat durumu güncellendi.');
    }

    public function destroyShipmentDelivery(ShipmentDelivery $shipment): RedirectResponse
    {
        $order = $shipment->order;
        $shipment->delete();

        if ($order) {
            $totalOrderQty = $order->items->sum('quantity');
            $totalShippedQty = $order->shipments()->sum('quantity');
            if ($totalShippedQty < $totalOrderQty) {
                $order->update(['status' => $totalShippedQty > 0 ? 'confirmed' : 'pending']);
            }
        }

        return redirect()->back()->with('success', 'Sevkiyat kaydı silindi.');
    }

}
