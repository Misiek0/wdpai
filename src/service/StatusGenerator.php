<?php
class StatusGenerator {
    public function generateVehicleStatus(): string { //status on_road if two objects pair during adding objects
        $pool = array_merge(
            array_fill(0, 70, 'available'), //70% available
            array_fill(0, 30, 'in_service') //30% in_service
        );
        return $pool[array_rand($pool)];
    }

    public function generateDriverStatus(): string {
        $pool = array_merge(
            array_fill(0, 70, 'available'), //70% available
            array_fill(0, 30, 'on_leave') //30% on_leave
        );
        return $pool[array_rand($pool)];
    }
}
