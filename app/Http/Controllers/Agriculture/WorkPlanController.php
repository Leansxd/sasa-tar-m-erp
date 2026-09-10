<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\WorkPlan;
use App\Models\WorkPlanComment;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;

class WorkPlanController extends Controller
{
    public function storeWorkPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'production_location_id' => 'nullable|exists:uretim_yerleri,id',
            'job_type_id' => 'nullable|exists:is_tanimlari,id',
            'assigned_personnel_id' => 'nullable|exists:personeller,id',
            'plan_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'description' => 'nullable|string',
            'completion_notes' => 'nullable|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('work_plans', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        if ($request->filled('id')) {
            $workPlan = WorkPlan::findOrFail($request->id);
            $workPlan->update($validated);
        } else {
            WorkPlan::create($validated);
        }

        return redirect()->back()->with('success', 'İş planı kaydedildi.');
    }

    public function storeWorkPlanComment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_plan_id' => 'required|exists:is_planlari,id',
            'comment' => 'required|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $personnel = Personnel::where('user_id', Auth::id())->first();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('work_plan_comments', 'public');
            $photoPath = '/storage/' . $path;
        }

        \App\Models\WorkPlanComment::create([
            'work_plan_id' => $validated['work_plan_id'],
            'personnel_id' => $personnel?->id,
            'comment' => $validated['comment'],
            'photo_path' => $photoPath,
        ]);

        return redirect()->back()->with('success', 'Not / Yorum eklendi.');
    }

    public function destroyWorkPlan(WorkPlan $workPlan): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            $personnel = Personnel::where('user_id', $user->id)->first();
            $allowedCompanies = $personnel ? ($personnel->company_ids ?? []) : [];
            $location = $workPlan->productionLocation;
            if ($location && !in_array($location->company_id, $allowedCompanies)) {
                return redirect()->back()->withErrors(['error' => 'Bu lokasyondaki iş planını silme yetkiniz bulunmamaktadır.']);
            }
        }

        $workPlan->delete();
        return redirect()->back()->with('success', 'İş planı silindi.');
    }

}
