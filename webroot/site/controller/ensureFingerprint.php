<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['fingerprint'])) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") ? 'http' : 'http';
    header('Location: ./fingerprinter.php?redirect=' . urlencode("$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));
}