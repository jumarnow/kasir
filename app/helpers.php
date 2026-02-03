<?php

function toNumeric($value)
{
    if (is_null($value) || $value === '') {
        return null;
    }

    // Remove anything that's not a digit, comma, or dot
    $clean = preg_replace('/[^0-9,.]/', '', $value);

    // Common Indonesian format: 1.000.000,00 or 1.000.000
    // If it has both . and , we assume . is thousands and , is decimal
    if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);
    }
    // If it only has dots, and the dots are every 3 digits, they are thousands
    elseif (strpos($clean, '.') !== false && preg_match('/\.\d{3}/', $clean)) {
        $clean = str_replace('.', '', $clean);
    }
    // If it only has a comma, it's likely a decimal separator
    elseif (strpos($clean, ',') !== false) {
        $clean = str_replace(',', '.', $clean);
    }

    return is_numeric($clean) ? (float) $clean : null;
}

function formatNumber($value)
{
    if (is_null($value)) {
        return null;
    }
    return number_format($value, 0, ',', '.');
}