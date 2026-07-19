<?php

require __DIR__ . '/../vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    if (strpos($message, 'pg_connect') !== false) {
        return true;
    }
    if (strpos($message, 'Cannot modify header information') !== false) {
        return true;
    }
    if (strpos($message, 'Session cannot be started') !== false) {
        return true;
    }
    if (strpos($message, 'session_destroy()') !== false || strpos($message, 'session_regenerate_id()') !== false) {
        return true;
    }
    return false;
}, E_WARNING);

if (!function_exists('getallheaders')) {
    /**
     * @return array<string, string>
     */
    function getallheaders(): array
    {
        return [];
    }
}
