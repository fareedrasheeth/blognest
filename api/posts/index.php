<?php
// api/posts/index.php - List all posts, fetch single post, or create new post

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDBConnection();

// --- GET Method: Fetch List of Posts or Single Post by ID ---
if ($method === 'GET') {
    $postId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    try {
        if ($postId) {
            // Fetch single blog post with author details
            $stmt = $db->prepare("
                SELECT p.id, p.user_id, p.title, p.content, p.created_at, p.updated_at, u.username as author
                FROM blogPost p
                JOIN user u ON p.user_id = u.id
                WHERE p.id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $postId]);
            $post = $stmt->fetch();

            if (!$post) {
                sendJsonResponse(false, 'Blog post not found.', [], 404);
            }

            // Check if current user is the owner of this post
            $post['is_owner'] = isPostOwner($post['user_id']);
            
            // XSS sanitization for title and author
            $post['title']  = sanitizeOutput($post['title']);
            $post['author'] = sanitizeOutput($post['author']);
            // Note: content will be rendered as Markdown on client side with XSS protection

            sendJsonResponse(true, 'Post retrieved successfully.', ['post' => $post]);

        } else {
            // Fetch all posts ordered by creation date (newest first)
            $stmt = $db->query("
                SELECT p.id, p.user_id, p.title, p.content, p.created_at, p.updated_at, u.username as author
                FROM blogPost p
                JOIN user u ON p.user_id = u.id
                ORDER BY p.created_at DESC
            ");
            $posts = $stmt->fetchAll();

            $currentUserId = isLoggedIn() ? $_SESSION['user_id'] : null;

            foreach ($posts as &$p) {
                $p['is_owner'] = ($currentUserId !== null && (int)$p['user_id'] === (int)$currentUserId);
                $p['title']    = sanitizeOutput($p['title']);
                $p['author']   = sanitizeOutput($p['author']);
                // Create short text excerpt for listing
                $plainContent  = strip_tags($p['content']);
                $p['excerpt']   = mb_substr($plainContent, 0, 150) . (mb_strlen($plainContent) > 150 ? '...' : '');
            }

            sendJsonResponse(true, 'Posts retrieved successfully.', ['posts' => $posts]);
        }

    } catch (PDOException $e) {
        sendJsonResponse(false, 'Database error while fetching posts.', [], 500);
    }
}

// --- POST Method: Create a New Blog Post (Auth Required) ---
if ($method === 'POST') {
    requireAuth();

    $input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $title   = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');

    if (empty($title) || empty($content)) {
        sendJsonResponse(false, 'Post title and content cannot be empty.', [], 400);
    }

    if (strlen($title) > 255) {
        sendJsonResponse(false, 'Post title must not exceed 255 characters.', [], 400);
    }

    $userId = $_SESSION['user_id'];

    try {
        $stmt = $db->prepare("INSERT INTO blogPost (user_id, title, content) VALUES (:user_id, :title, :content)");
        $stmt->execute([
            ':user_id' => $userId,
            ':title'   => $title,
            ':content' => $content
        ]);

        $newPostId = $db->lastInsertId();

        sendJsonResponse(true, 'Blog post created successfully!', [
            'post_id' => $newPostId
        ], 201);

    } catch (PDOException $e) {
        sendJsonResponse(false, 'Database error while creating blog post.', [], 500);
    }
}

sendJsonResponse(false, 'Method Not Allowed', [], 405);
