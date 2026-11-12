<?php
/**
 * AMnote Server Portal — allowlisted access only.
 * No trust in X-Forwarded-For: reverse proxies require an explicit deployment review.
 */
function amnote_guard() {
    $allowedIps = array('115.78.232.22', '127.0.0.1', '::1');
    $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!in_array($remote, $allowedIps, true)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store, private');
        exit('403 Forbidden: This dashboard is restricted to the company IP.');
    }
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; font-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'");
}
