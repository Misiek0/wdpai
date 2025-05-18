<?php

class LocationGenerator {
    public function generate(): array {
        // min/max Poland coordinates
        $minLat = 49.0;
        $maxLat = 54.0;
        $minLon = 14.1;
        $maxLon = 23.0;

        $lat = mt_rand() / mt_getrandmax() * ($maxLat - $minLat) + $minLat;
        $lon = mt_rand() / mt_getrandmax() * ($maxLon - $minLon) + $minLon;

        return [round($lat, 6), round($lon, 6)];
    }
}
