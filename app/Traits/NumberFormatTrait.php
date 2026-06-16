<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait NumberFormatTrait
{
    // Only for balance format
    public function truncateNum($value, $scale = 8): string
    {
        if (is_integer($value)) {
            return number_format($value, 2, '.', '');
        } elseif (is_string($value)) {
            $value = floatval($value);
        }

        // Convert to string representation with the full precision
        $valueString = sprintf("%.10f", $value);

        // Split into integer and decimal parts
        $parts = explode('.', $valueString);

        // Keep only necessary decimal places without rounding
        $decimalPart = rtrim(substr($parts[1], 0, $scale), '0');

        // If the decimal part is empty, return only the integer part
        if (empty($decimalPart)) {
            return $parts[0] . '.00';
        }

        // Combine the integer and formatted decimal parts
        return $parts[0] . '.' . $decimalPart;
    }


    public function fmtNum($value, int $decimal = 8): string
    {
        $value = is_string($value) ? floatval($value) : $value;
        $result = number_format($value, $decimal, '.', '');

        return (string) $result;
    }

    public function trimNum($value, int $decimal = 8): string
    {
        $number = $this->fmtNum($value, $decimal);
        $result = rtrim(rtrim($number, '0'), '.');

        return (string) $result;
    }
}
