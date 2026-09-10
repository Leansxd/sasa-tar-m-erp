<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\SprayingApplication;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;

class SprayingController extends Controller
{
    public function storeSprayingApplication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'application_date' => 'required|date',
            'spraying_recipe_id' => 'required|exists:ilac_receteleri,id',
            'purpose' => 'nullable|string|max:255',
            'applied_by_id' => 'nullable|exists:personeller,id',
            'covered_area_description' => 'nullable|string|max:255',
            'is_tank_finished' => 'boolean',
            'batch_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        SprayingApplication::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'İlaçlama uygulaması kaydedildi.');
    }

    public function destroySprayingApplication(SprayingApplication $app): RedirectResponse
    {
        $app->delete();
        return redirect()->back()->with('success', 'İlaçlama kaydı silindi.');
    }

}
