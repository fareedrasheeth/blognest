<?php
// api/posts/manage.php - Update and Delete Blog Posts with Server-Side Ownership Enforcement

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Require authentication
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDBConnection();

// Get Post ID from URL query or payload
$postId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Read JSON input or form payload
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
if (!$postId && isset($input['id'])) {
    $postId = (int)$input['id'];
}

// Allow method override via header or payload action (e.g. POST with _method=PUT or _method=DELETE)
$actionOverride = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $input['_method'] ?? $input['action'] ?? null;
if ($actionOverride) {
    $method = strtoupper($actionOverride);
}

if (!$postId) {
    sendJsonResponse(false, 'Post ID is required.', [], 400);
}

try {
    // 1. Fetch Post to verify existence and check server-side ownership
    $checkStmt = $db->prepare("SELECT id, user_id FROM blogPost WHERE id = :id LIMIT 1");
    $checkStmt->execute([':id' => $postId]);
    $post = $checkStmt->fetch();

    if (!$post) {
        sendJsonResponse(false, 'Blog post not found.', [], 404);
    }

    // 2. SERVER-SIDE AUTHORIZATION CHECK
    // Compare post.user_id with $_SESSION['user_id']
    if (!isPostOwner($post['user_id'])) {
        sendJsonResponse(false, 'Unauthorized: You can only update or delete your own blog posts.', [], 403);
    }

    // --- UPDATE / PUT Action ---
    if ($method === 'PUT' || $method === 'UPDATE' || ($method === 'POST' && strtolower($input['action'] ?? '') === 'update')) {
        $title   = trim($input['title'] ?? '');
        $content = trim($input['content'] ?? '');

        if (empty($title) || empty($content)) {
            sendJsonResponse(false, 'Title and content cannot be empty.', [], 400);
        }

        if (strlen($title) > 255) {
            sendJsonResponse(false, 'Post title must not exceed 255 characters.', [], 400);
        }

        $updateStmt = $db->prepare("UPDATE blogPost SET title = :title, content = :content, updated_at = NOW() WHERE id = :id");
        $updateStmt->execute([
            ':title'   => $title,
            ':content' => $content,
            ':id'      => $postId
        ]);

        sendJsonResponse(true, 'Blog post updated successfully!');
    }

    // --- DELETE Action ---
    if ($method === 'DELETE' || ($method === 'POST' && strtolower($input['action'] ?? '') === 'delete')) {
        $deleteStmt = $db->prepare("DELETE FROM blogPost WHERE id = :id");
        $deleteStmt->execute([':id' => $postId]);

        sendJsonResponse(true, 'Blog post deleted successfully!');
    }

    sendJsonResponse(false, 'Invalid action or HTTP method specified.', [], 405);

} catch (PDOException $e) {
    sendJsonResponse(false, 'Database error while modifying post.', [], 500);
}
