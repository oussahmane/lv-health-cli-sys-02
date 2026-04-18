<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index(Request $request)
    {
        $clinicId = $request->user()->clinic_id;
        $counters = Counter::where('clinic_id', $clinicId)->get();
        return response()->json($counters);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $counter = Counter::create([
            'clinic_id' => $request->user()->clinic_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json($counter, 201);
    }

    public function update(Request $request, Counter $counter)
    {
        if ($counter->clinic_id !== $request->user()->clinic_id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $counter->update($request->only(['name', 'description', 'is_active']));

        return response()->json($counter);
    }

    public function destroy(Counter $counter, Request $request)
    {
        if ($counter->clinic_id !== $request->user()->clinic_id) abort(403);
        
        // Potential check: is any patient currently assigned to this counter?
        
        $counter->delete();
        return response()->json(['message' => 'Counter deleted successfully.']);
    }
}
