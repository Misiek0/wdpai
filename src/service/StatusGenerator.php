<?php

class StatusGenerator {
    private $statuses = ['available', 'on_road', 'in_service'];

    public function generate(): string {
        return $this->statuses[array_rand($this->statuses)];
    }
}
