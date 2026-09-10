<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\FertilizationRun;
use App\Models\FertilizationTankLog;
use Illuminate\Support\Facades\Auth;
use App\Models\Personnel;

class FertilizationController extends Controller
{
    public function storeFertilizationRun(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'fertilization_recipe_id' => 'required|exists:gubre_receteleri,id',
            'start_date' => 'required|date',
            'end_condition' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;

        if ($isActive) {
            FertilizationRun::query()->where('id', '!=', $request->id ?? 0)->update(['is_active' => false, 'end_date' => now()->toDateString()]);
        }

        FertilizationRun::updateOrCreate(
            ['id' => $request->id],
            [
                'fertilization_recipe_id' => $validated['fertilization_recipe_id'],
                'start_date' => $validated['start_date'],
                'end_condition' => $validated['end_condition'] ?? null,
                'is_active' => $isActive,
                'end_date' => $isActive ? null : now()->toDateString(),
            ]
        );

        return redirect()->back()->with('success', 'Gübreleme reçetesi kaydı güncellendi.');
    }

    public function toggleActiveFertilizationRun(FertilizationRun $run): RedirectResponse
    {
        if (!$run->is_active) {
            FertilizationRun::query()->where('id', '!=', $run->id)->update(['is_active' => false, 'end_date' => now()->toDateString()]);
            $run->update(['is_active' => true, 'end_date' => null]);
            return redirect()->back()->with('success', 'Gübreleme reçetesi aktif edildi.');
        } else {
            $run->update(['is_active' => false, 'end_date' => now()->toDateString()]);
            return redirect()->back()->with('success', 'Gübreleme reçetesi pasife alındı.');
        }
    }

    public function storeFertilizationTankLog(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fertilization_run_id' => 'required|exists:gubre_uygulamalari,id',
            'fertilization_tank_id' => 'required|exists:gubre_tanklari,id',
            'prepared_at' => 'required|date',
            'prepared_by_id' => 'nullable|exists:personeller,id',
            'tank_name' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        FertilizationTankLog::create($validated);

        return redirect()->back()->with('success', 'Tank hazırlama kaydı oluşturuldu.');
    }

    public function destroyFertilizationRun(FertilizationRun $run): RedirectResponse
    {
        if ($run->is_active) {
            return redirect()->back()->withErrors(['error' => 'Şu anda sahada aktif olan bir gübreleme reçetesi silinemez. Önce pasife alınız.']);
        }

        $run->delete();
        return redirect()->back()->with('success', 'Gübreleme reçetesi kaydı silindi.');
    }

}
