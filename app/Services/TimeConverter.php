<?php

namespace App\Services;

class TimeConverter {
    public function convertMilliseconds($milliseconds) {
        $seconds = $milliseconds / 1000;
        $minutes = floor($seconds / 60);
        $seconds = floor($seconds % 60);
        $formatted_minutes = sprintf("%02d", $minutes);
        $formatted_seconds = sprintf("%02d", $seconds);
        return array($formatted_minutes, $formatted_seconds);
    }
}
