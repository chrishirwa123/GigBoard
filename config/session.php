<?php

// Session lifetime (30 days)
$lifetime = 60 * 60 * 24 * 30;

// IMPORTANT: configure cookie BEFORE starting the session
session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,      // Change to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

ini_set('session.gc_maxlifetime', $lifetime);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>