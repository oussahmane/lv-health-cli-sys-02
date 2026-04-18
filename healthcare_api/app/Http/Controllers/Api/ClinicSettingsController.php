<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicWorkingHour;
use App\Models\QueueSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClinicSettingsController extends Controller
{
    public function index(Request $request)
    {
        $clinic = Clinic::with(['workingHours', 'plan'])->findOrFail($request->user()->clinic_id);
        
        $overrides = QueueSetting::where('clinic_id', $clinic->id)
            ->where('date', '>=', now()->toDateString())
            ->get();

        return response()->json([
            'clinic' => $clinic,
            'overrides' => $overrides,
        ]);
    }

    public function update(Request $request)
    {
        $clinic = Clinic::findOrFail($request->user()->clinic_id);

        // FormData sends arrays as strings, we need to decode it
        if ($request->has('working_hours') && is_string($request->working_hours)) {
            $request->merge([
                'working_hours' => json_decode($request->working_hours, true)
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string',
            'timezone' => 'required|string',
            'avg_minutes_per_patient' => 'required|integer|min:1',
            'default_max_patients' => 'required|integer|min:1',
            'logo' => 'nullable|image|max:2048',
            'display_ticker' => 'nullable|string|max:1000',
            'working_hours' => 'required|array',
            'working_hours.*.weekday' => 'required|integer|between:0,6',
            'working_hours.*.open_time' => 'nullable',
            'working_hours.*.close_time' => 'nullable',
            'working_hours.*.is_open' => 'required',
        ]);

        $data = $request->only(['name', 'address', 'phone', 'timezone', 'avg_minutes_per_patient', 'default_max_patients', 'display_ticker']);

        if ($request->hasFile('logo')) {
            if ($clinic->logo) Storage::disk('public')->delete($clinic->logo);
            $data['logo'] = $request->file('logo')->store('clinic_logos', 'public');
        }

        $clinic->update($data);

        // Sync today's max patients limit if an override exists
        QueueSetting::where('clinic_id', $clinic->id)
            ->where('date', now()->toDateString())
            ->update(['max_patients' => $request->default_max_patients]);

        // Update working hours
        foreach ($request->working_hours as $hour) {
             ClinicWorkingHour::updateOrCreate(
                 ['clinic_id' => $clinic->id, 'weekday' => $hour['weekday']],
                 ['open_time' => $hour['open_time'], 'close_time' => $hour['close_time'], 'is_open' => $hour['is_open']]
             );
         }
 
         event(new \App\Events\ClinicSettingsUpdated($clinic->id, $clinic->only(['name', 'display_ticker'])));
 
         return response()->json($clinic->load('workingHours'));
    }
}
