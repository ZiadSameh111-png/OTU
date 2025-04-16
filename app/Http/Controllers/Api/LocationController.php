<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationSetting;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all location settings
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocations()
    {
        $locations = LocationSetting::all();
        
        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }

    /**
     * Check if student is within the allowed range of a location
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $locations = LocationSetting::all();
        
        if ($locations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No location settings found',
                'data' => null
            ], 404);
        }

        $withinRange = false;
        $closestLocation = null;
        $distance = null;

        // Check if user is within range of any location
        foreach ($locations as $location) {
            $currentDistance = LocationSetting::calculateDistance(
                $location->latitude,
                $location->longitude,
                $validated['latitude'],
                $validated['longitude']
            );

            if ($closestLocation === null || $currentDistance < $distance) {
                $closestLocation = $location;
                $distance = $currentDistance;
            }

            if ($location->isWithinRange($validated['latitude'], $validated['longitude'])) {
                $withinRange = true;
                $closestLocation = $location;
                $distance = $currentDistance;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'within_range' => $withinRange,
            'data' => [
                'location' => $closestLocation,
                'distance' => round($distance, 2),
                'max_range' => $closestLocation->range_meters,
                'remaining_distance' => $withinRange ? 0 : round($distance - $closestLocation->range_meters, 2),
            ]
        ]);
    }
} 