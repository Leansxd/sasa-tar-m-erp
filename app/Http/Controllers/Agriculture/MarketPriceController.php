<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\MarketPrice;

class MarketPriceController extends Controller
{
    public function storeMarketPrice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:hal_piyasa_fiyatlari,id',
            'product_id' => 'required|exists:urunler,id',
            'price_date' => 'required|date',
            'unit_price' => 'required|numeric|min:0|max:999999',
            'source_name' => 'required|string|max:255',
        ]);

        MarketPrice::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'product_id' => $validated['product_id'],
                'price_date' => $validated['price_date'],
                'unit_price' => $validated['unit_price'],
                'source_name' => $validated['source_name'],
            ]
        );

        return redirect()->back()->with('success', 'Piyasa fiyatı kaydedildi.');
    }

    public function destroyMarketPrice(MarketPrice $price): RedirectResponse
    {
        $user = auth()->user();
        $personnel = \App\Models\Personnel::where('user_id', $user->id)->first();
        if (!$user->is_admin && !in_array('raporlar', $personnel->permissions ?? []) && !in_array('operasyon', $personnel->permissions ?? [])) {
            return redirect()->back()->withErrors(['error' => 'Piyasa fiyat kaydı silme yetkiniz bulunmamaktadır.']);
        }

        $price->delete();
        return redirect()->back()->with('success', 'Piyasa fiyat kaydı silindi.');
    }

}
