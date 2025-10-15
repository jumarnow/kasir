<?php

function toNumeric($value)
{
    if (is_null($value)) {
        return null;
    }
    $value = str_replace(['.', ','], ['', '.'], $value);
    return is_numeric($value) ? (float)$value : null;
}

function formatNumber($value)
{
    if (is_null($value)) {
        return null;
    }
    return number_format($value, 0, ',', '.');
}