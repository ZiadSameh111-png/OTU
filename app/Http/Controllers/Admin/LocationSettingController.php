<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationSetting;
use Illuminate\Http\Request;

class LocationSettingController extends Controller
{
    public function index()
    {
        $locations = LocationSetting::all();
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'range_meters' => 'required|integer|min:10',
        ]);

        LocationSetting::create($validated);

        return redirect()->route('admin.locations.index')->with('success', 'Location setting created successfully!');
    }

    public function edit(LocationSetting $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, LocationSetting $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'range_meters' => 'required|integer|min:10',
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations.index')->with('success', 'Location setting updated successfully!');
    }

    public function destroy(LocationSetting $location)
    {
        $location->delete();
        return redirect()->route('admin.locations.index')->with('success', 'Location setting deleted successfully!');
    }
}
