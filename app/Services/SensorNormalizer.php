<?php
namespace App\Services;
class SensorNormalizer {
    public function process($type, $value) {
        switch($type) {
            case 'temperature':
                // Loại bỏ giá trị vô lý (nhiễu cảm biến)
                if ($value > 100 || $value < -10) return null; 
                return round($value, 1);
            case 'humidity':
                if ($value < 0 || $value > 100) return null;
                return (int)$value;
            default:
                return $value;
        }
    }
}