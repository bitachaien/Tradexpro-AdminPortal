<?php

use App\Traits\NumberFormatTrait;

/**
 * Class to handle arbitrary precision mathematics operations using PHP's BC Math functions
 * BC Math is used for precise decimal calculations, especially important for financial operations
 */
class BcMath
{
    use NumberFormatTrait;

    /**
     * Performs BC Math operations with proper number formatting
     * @param string $operation BC Math function to execute (bcadd, bcsub, etc.)
     * @param mixed $num1 First number
     * @param mixed $num2 Second number
     * @param int $scale Number of decimal places (default: 8)
     * @return string Result of the BC Math operation
     */
    function bcOperation(string $operation, $num1, $num2, int $scale = 8)
    {
        $num1 = $this->truncateNum($num1, $scale);
        $num2 = $this->truncateNum($num2, $scale);
        return $operation($num1, $num2, $scale);
    }
}

/**
 * Adds two numbers with arbitrary precision
 * Example: bcaddx('1.23456789', '9.87654321')
 */
function bcaddx($num1, $num2, int $scale = 8)
{
    return (new BcMath())->bcOperation('bcadd', $num1, $num2, $scale);
}

/**
 * Subtracts two numbers with arbitrary precision
 */
function bcsubx($num1, $num2, int $scale = 8)
{
    return (new BcMath())->bcOperation('bcsub', $num1, $num2, $scale);
}

/**
 * Multiplies two numbers with arbitrary precision
 */
function bcmulx($num1, $num2, int $scale = 8)
{
    return (new BcMath())->bcOperation('bcmul', $num1, $num2, $scale);
}

/**
 * Divides two numbers with arbitrary precision
 */
function bcdivx($num1, $num2, int $scale = 8)
{
    return (new BcMath())->bcOperation('bcdiv', $num1, $num2, $scale);
}

/**
 * Gets modulus of two numbers with arbitrary precision
 */
function bcmodx($num1, $num2, int $scale = 8)
{
    return (new BcMath())->bcOperation('bcmod', $num1, $num2, $scale);
}

/**
 * Compares two numbers with arbitrary precision
 * Returns: 0 if equal, 1 if num1 > num2, -1 if num1 < num2
 */
function bccompx($num1, $num2, int $scale = 8)
{
    return (new BcMath())->bcOperation('bccomp', $num1, $num2, $scale);
}
