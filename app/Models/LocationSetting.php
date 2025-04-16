<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'range_meters'
    ];

    /**
     * Calculate distance between two points using Haversine formula
     * 
     * @param float $lat1 First point latitude
     * @param float $lon1 First point longitude
     * @param float $lat2 Second point latitude
     * @param float $lon2 Second point longitude
     * @return float Distance in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if given coordinates are within range of this location
     * 
     * @param float $latitude Latitude to check
     * @param float $longitude Longitude to check
     * @return bool True if within range, false otherwise
     */
    public function isWithinRange($latitude, $longitude)
    {
        $distance = self::calculateDistance(
            $this->latitude,
            $this->longitude,
            $latitude,
            $longitude
        );

        return $distance <= $this->range_meters;
    }

    /**
     * Get the closest location setting to the given coordinates
     * 
     * @param float $latitude Latitude to check
     * @param float $longitude Longitude to check
     * @return LocationSetting|null The closest location or null if none found
     */
    public static function getClosestLocation($latitude, $longitude)
    {
        $locations = self::all();
        
        if ($locations->isEmpty()) {
            return null;
        }

        $closest = null;
        $closestDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = self::calculateDistance(
                $location->latitude,
                $location->longitude,
                $latitude,
                $longitude
            );

            if ($distance < $closestDistance) {
                $closest = $location;
                $closestDistance = $distance;
            }
        }

        return $closest;
    }
    
    /**
     * العلاقة مع سجلات الحضور المكاني
     */
    public function attendances()
    {
        return $this->hasMany(LocationAttendance::class);
    }
}
