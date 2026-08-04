<?php
// api/auth/login.php - User Authentication Endpoint

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method Not Allowed', [], 405);
}

// Support both form-urlencoded and JSON body inputs
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$identifier = trim($input['identifier'] ?? $input['email'] ?? $input['username'] ?? '');
$password   = $input['password'] ?? '';

if (empty($identifier) || empty($password)) {
    sendJsonResponse(false, 'Please enter both your email/username and password.', [], 400);
}

$db = getDBConnection();

try {
    // Find user by username OR email
    $stmt = $db->prepare("SELECT id, username, email, password, role FROM user WHERE username = :id_user OR email = :id_email LIMIT 1");
    $stmt->execute([':id_user' => $identifier, ':id_email' => $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        sendJsonResponse(false, 'Invalid credentials. Please check your username/email and password.', [], 401);
    }

    // Set Session Variables
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];

    sendJsonResponse(true, 'Login successful!', [
        'user' => [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role']
        ]
    ]);

} catch (PDOException $e) {
    sendJsonResponse(false, 'An error occurred during login. Please try again.', [], 500);
}
