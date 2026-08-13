<?php
// api/messages/index.php - Live Community Messaging API Endpoint

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$db     = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Ensure message table exists
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS message (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES user(id) ON DELETE CASCADE
        )
    ");
} catch (Exception $ignored) {}

// --- GET Method: Fetch recent messages ---
if ($method === 'GET') {
    $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
    $limit   = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 30;

    try {
        if ($sinceId > 0) {
            $stmt = $db->prepare("
                SELECT m.id, m.sender_id, m.content, m.created_at, u.username as sender_name
                FROM message m
                JOIN user u ON m.sender_id = u.id
                WHERE m.id > :since_id
                ORDER BY m.id ASC
                LIMIT :limit_num
            ");
            $stmt->bindValue(':since_id', $sinceId, PDO::PARAM_INT);
            $stmt->bindValue(':limit_num', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("
                SELECT m.id, m.sender_id, m.content, m.created_at, u.username as sender_name
                FROM message m
                JOIN user u ON m.sender_id = u.id
                ORDER BY m.id DESC
                LIMIT :limit_num
            ");
            $stmt->bindValue(':limit_num', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }

        $messages = $stmt->fetchAll();
        if ($sinceId === 0) {
            $messages = array_reverse($messages); // Return in chronological order
        }

        $currentUserId = isLoggedIn() ? (int)$_SESSION['user_id'] : null;

        foreach ($messages as &$msg) {
            $msg['is_self']             = ($currentUserId !== null && (int)$msg['sender_id'] === $currentUserId);
            $msg['sender_name']         = sanitizeOutput($msg['sender_name']);
            $msg['content']             = sanitizeOutput($msg['content']);
            $msg['created_at_formatted'] = date('g:i A', strtotime($msg['created_at']));
        }

        sendJsonResponse(true, 'Messages retrieved successfully.', [
            'messages' => $messages,
            'current_user_id' => $currentUserId
        ]);

    } catch (PDOException $e) {
        sendJsonResponse(false, 'Database error while fetching messages.', [], 500);
    }
}

// --- POST Method: Send a message (Auth Required) ---
if ($method === 'POST') {
    requireAuth();

    $input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $content = trim($input['content'] ?? '');

    if (empty($content)) {
        sendJsonResponse(false, 'Message content cannot be empty.', [], 400);
    }

    if (strlen($content) > 1000) {
        sendJsonResponse(false, 'Message content must not exceed 1000 characters.', [], 400);
    }

    $senderId = (int)$_SESSION['user_id'];

    try {
        $stmt = $db->prepare("INSERT INTO message (sender_id, content) VALUES (:sender_id, :content)");
        $stmt->execute([
            ':sender_id' => $senderId,
            ':content'   => $content
        ]);

        $newMsgId = $db->lastInsertId();

        $fetchStmt = $db->prepare("
            SELECT m.id, m.sender_id, m.content, m.created_at, u.username as sender_name
            FROM message m
            JOIN user u ON m.sender_id = u.id
            WHERE m.id = :id
            LIMIT 1
        ");
        $fetchStmt->execute([':id' => $newMsgId]);
        $newMsg = $fetchStmt->fetch();

        $newMsg['is_self']             = true;
        $newMsg['sender_name']         = sanitizeOutput($newMsg['sender_name']);
        $newMsg['content']             = sanitizeOutput($newMsg['content']);
        $newMsg['created_at_formatted'] = date('g:i A', strtotime($newMsg['created_at']));

        sendJsonResponse(true, 'Message sent successfully!', [
            'message' => $newMsg
        ], 201);

    } catch (PDOException $e) {
        sendJsonResponse(false, 'Database error while sending message.', [], 500);
    }
}

sendJsonResponse(false, 'Method Not Allowed', [], 405);
