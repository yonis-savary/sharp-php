<?php

use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Storage;

/**
 * Debug function: used to measure an execution time
 *
 * @param callable $callback Function to measure (execution time)
 * @param string $label You can give the measurement a name
 * @return mixed Return the callback return value
 */
function sharpDebugMeasure(callable $callback, string $label="Measurement"): mixed
{
    $start = hrtime(true);
    $returnValue = $callback();
    $deltaMicro = (hrtime(true) - $start) / 1000;

    $infoString = "$label : $deltaMicro µs (". $deltaMicro/1000 ."ms)";

    Logger::getInstance()->debug($infoString);

    return $returnValue;
}