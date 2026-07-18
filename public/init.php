<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/session.php';

require __DIR__ . '/../config/database.php';

$auth = new \App\Auth();
$guard = new \App\Guard();
$rateLimiter = new \App\RateLimiter();

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
