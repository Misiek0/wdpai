<?php

class FuelGenerator {
    public function generate(): float {
        $min = 5.0; //min l per 100km
        $max = 13.0; //max l per 100km
        $random = mt_rand() / mt_getrandmax() * ($max - $min) + $min;
        return round($random, 1);
    }
}
