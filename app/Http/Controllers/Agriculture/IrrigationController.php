<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\IrrigationSchedule;
use App\Models\LocationValve;

class IrrigationController extends Controller
{
    public function storeIrrigationSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'schedule_date' => 'required|date',
            'run_number' => 'required|integer|min:1',
            'start_time' => 'required|string',
            'is_fertilized' => 'boolean',
            'fertilization_recipe_id' => 'nullable|exists:gubre_receteleri,id',
            'tank_stage_note' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['tank_stage_note']) && empty($validated['notes'])) {
            $validated['notes'] = $validated['tank_stage_note'];
        }

        $scheduleData = collect($validated)->except('tank_stage_note')->toArray();
        $schedule = IrrigationSchedule::updateOrCreate(['id' => $request->id], $scheduleData);

        if ($request->has('valves') && is_array($request->valves)) {
            $schedule->valves()->delete();
            $locId = $request->production_location_id;
            foreach ($request->valves as $v) {
                $valveId = $v['location_valve_id'] ?? $v['valve_id'] ?? null;
                $vLocId = $v['production_location_id'] ?? $locId;
                if (!empty($valveId) && !empty($vLocId) && \App\Models\LocationValve::where('id', $valveId)->exists()) {
                    $schedule->valves()->create([
                        'production_location_id' => $vLocId,
                        'location_valve_id' => $valveId,
                        'duration_minutes' => $v['duration_minutes'] ?? 5,
                        'tank_step_level' => $v['tank_step_level'] ?? $validated['tank_stage_note'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Sulama programı kaydedildi.');
    }

    public function destroyIrrigationSchedule(IrrigationSchedule $schedule): RedirectResponse
    {
        $schedule->delete();
        return redirect()->back()->with('success', 'Sulama programı kaydı silindi.');
    }

}
