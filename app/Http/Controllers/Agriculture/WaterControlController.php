<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\WaterAnalysisLog;
use App\Models\RawWaterControl;
use App\Models\PurificationControl;

class WaterControlController extends Controller
{
    public function storeWaterAnalysis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'water_source_id' => 'required|exists:su_kaynaklari,id',
            'analysis_date' => 'required|date',
            'ph_level' => 'required|numeric|min:0|max:14',
            'ec_level' => 'required|numeric|min:0',
            'chemical_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        WaterAnalysisLog::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Su analizi kaydedildi.');
    }

    public function destroyWaterAnalysis(WaterAnalysisLog $log): RedirectResponse
    {
        $log->delete();
        return redirect()->back()->with('success', 'Su analiz kaydı silindi.');
    }

    public function storeRawWaterControl(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'water_source_id' => 'required|exists:su_kaynaklari,id',
            'control_date' => 'required|date',
            'ec_val' => 'nullable|numeric',
            'ph_val' => 'nullable|numeric',
            'pump_status' => 'required|in:open,closed,faulty',
            'pump_fault_note' => 'nullable|string',
            'active_start_date' => 'nullable|date',
            'passive_start_date' => 'nullable|date',
            'source_switch_reason' => 'nullable|string',
            'is_filter_cleaned' => 'boolean',
            'filter_cleaned_photo' => 'nullable',
            'water_tank_level' => 'required|in:full,half_plus,half_minus,empty',
            'water_tank_photo' => 'nullable',
            'water_tank_note' => 'nullable|string',
            'chlorine_tank_level' => 'required|in:full,half_plus,half_minus,empty',
            'dosing_pump_mode' => 'required|in:auto,manual,faulty',
            'dosing_pump_manual_val' => 'nullable|string',
            'dosing_pump_fault_note' => 'nullable|string',
        ]);

        if ($request->hasFile('filter_cleaned_photo')) {
            $validated['filter_cleaned_photo'] = '/storage/' . $request->file('filter_cleaned_photo')->store('water_controls', 'public');
        } elseif (!$request->filled('filter_cleaned_photo')) {
            unset($validated['filter_cleaned_photo']);
        }

        if ($request->hasFile('water_tank_photo')) {
            $validated['water_tank_photo'] = '/storage/' . $request->file('water_tank_photo')->store('water_controls', 'public');
        } elseif (!$request->filled('water_tank_photo')) {
            unset($validated['water_tank_photo']);
        }

        RawWaterControl::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Kaynak suyu kontrolü kaydedildi.');
    }

    public function destroyRawWaterControl(RawWaterControl $control): RedirectResponse
    {
        $control->delete();
        return redirect()->back()->with('success', 'Kaynak suyu kontrol kaydı silindi.');
    }

    public function storePurificationControl(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'water_source_id' => 'required|exists:su_kaynaklari,id',
            'control_date' => 'required|date',
            'inlet_pressure_bar' => 'required|numeric|min:0',
            'outlet_pressure_bar' => 'required|numeric|min:0',
            'max_threshold_bar' => 'nullable|numeric|min:0',
        ]);

        $inlet = floatval($validated['inlet_pressure_bar']);
        $outlet = floatval($validated['outlet_pressure_bar']);
        $delta = $inlet - $outlet;
        $maxThresh = floatval($validated['max_threshold_bar'] ?? 1.50);

        $hasWarning = $delta >= $maxThresh;
        $msg = $hasWarning ? "Uyarı: Barometre basınç farkı ($delta bar) üst limiti aştı! Filtre doldu, kontrol edip değiştiriniz." : null;

        PurificationControl::updateOrCreate(
            ['id' => $request->id],
            [
                'water_source_id' => $validated['water_source_id'],
                'control_date' => $validated['control_date'],
                'inlet_pressure_bar' => $inlet,
                'outlet_pressure_bar' => $outlet,
                'delta_pressure_bar' => $delta,
                'max_threshold_bar' => $maxThresh,
                'has_warning' => $hasWarning,
                'warning_message' => $msg,
            ]
        );

        return redirect()->back()->with('success', 'Arıtma suyu kontrolü ve basınç analizi tamamlandı.');
    }

    public function destroyPurificationControl(PurificationControl $control): RedirectResponse
    {
        $control->delete();
        return redirect()->back()->with('success', 'Arıtma kontrol kaydı silindi.');
    }

}
