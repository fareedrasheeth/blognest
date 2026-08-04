<?php
// api/auth/me.php - Active Session User Check Endpoint

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    sendJsonResponse(false, 'Not authenticated', ['authenticated' => false], 200);
}

sendJsonResponse(true, 'Authenticated', [
    'authenticated' => true,
    'user' => getCurrentUser()
]);
