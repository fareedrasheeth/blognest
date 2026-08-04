<?php
// api/auth/logout.php - User Session Destruction Endpoint

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect if standard form POST, else return JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['json'])) {
    header('Location: ../../index.php');
    exit();
}

sendJsonResponse(true, 'Logged out successfully.');
