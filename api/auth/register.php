<?php
// api/auth/register.php - User Registration Endpoint

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method Not Allowed', [], 405);
}

// Support both form-urlencoded and JSON body inputs
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$username = trim($input['username'] ?? '');
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Basic Server-side Validation
if (empty($username) || empty($email) || empty($password)) {
    sendJsonResponse(false, 'Please fill in all required fields.', [], 400);
}

if (strlen($username) < 3 || strlen($username) > 50) {
    sendJsonResponse(false, 'Username must be between 3 and 50 characters.', [], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(false, 'Invalid email address format.', [], 400);
}

if (strlen($password) < 6) {
    sendJsonResponse(false, 'Password must be at least 6 characters long.', [], 400);
}

$db = getDBConnection();

try {
    // Check if username or email already exists
    $checkStmt = $db->prepare("SELECT id, username, email FROM user WHERE username = :username OR email = :email LIMIT 1");
    $checkStmt->execute([':username' => $username, ':email' => $email]);
    $existingUser = $checkStmt->fetch();

    if ($existingUser) {
        if ($existingUser['username'] === $username) {
            sendJsonResponse(false, 'Username is already taken.', [], 409);
        } else {
            sendJsonResponse(false, 'Email address is already registered.', [], 409);
        }
    }

    // Securely hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into DB
    $insertStmt = $db->prepare("INSERT INTO user (username, email, password, role) VALUES (:username, :email, :password, 'user')");
    $insertStmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => $hashedPassword
    ]);

    $newUserId = $db->lastInsertId();

    // Auto log-in after registration
    $_SESSION['user_id']  = $newUserId;
    $_SESSION['username'] = $username;
    $_SESSION['email']    = $email;
    $_SESSION['role']     = 'user';

    sendJsonResponse(true, 'Registration successful! Welcome to BlogNest.', [
        'user' => [
            'id'       => $newUserId,
            'username' => $username,
            'email'    => $email,
            'role'     => 'user'
        ]
    ], 201);

} catch (PDOException $e) {
    sendJsonResponse(false, 'An error occurred during registration. Please try again.', [], 500);
}
