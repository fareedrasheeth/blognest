<?php
// includes/functions.php - Global Helper Functions & Security Utilities

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Send JSON response and exit
 */
function sendJsonResponse($success, $message, $data = [], $statusCode = 200) {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'success' => (bool)$success,
        'message' => (string)$message,
        'data'    => $data
    ]);
    exit();
}

/**
 * Check if a user is currently logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user details
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? 'user'
    ];
}

/**
 * Require authentication - redirect or return 401 JSON
 */
function requireAuth() {
    if (!isLoggedIn()) {
        // If request expects JSON or is AJAX
        if (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
        ) {
            sendJsonResponse(false, 'Unauthorized access. Please log in.', [], 401);
        } else {
            header('Location: login.php');
            exit();
        }
    }
}

/**
 * Escape HTML output against XSS vulnerabilities
 */
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate post ownership on server side
 */
function isPostOwner($postUserId) {
    if (!isLoggedIn()) return false;
    return (int)$_SESSION['user_id'] === (int)$postUserId;
}
