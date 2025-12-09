<?php
/**
 * MoonCart Contact Messages API
 * Endpoints: Submit Message, Get Messages, Mark Read
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'all';

switch ($method) {
    case 'GET':
        if ($action === 'single' && isset($_GET['id'])) {
            getMessage($_GET['id']);
        } else {
            getAllMessages();
        }
        break;
    
    case 'POST':
        submitMessage();
        break;
    
    case 'PUT':
        if ($action === 'read') {
            markAsRead();
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
    
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteMessage($_GET['id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Message ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get All Messages (Admin)
 */
function getAllMessages() {
    $conn = getConnection();
    
    $unread = $_GET['unread'] ?? null;
    
    $sql = "SELECT * FROM contact_messages";
    $params = [];
    
    if ($unread === 'true') {
        $sql .= " WHERE is_read = 0";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
    
    // Count unread
    $stmt = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $unreadCount = $stmt->fetchColumn();
    
    jsonResponse([
        'success' => true,
        'count' => count($messages),
        'unread_count' => (int)$unreadCount,
        'messages' => $messages
    ]);
}

/**
 * Get Single Message
 */
function getMessage($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        jsonResponse(['success' => false, 'message' => 'Message not found'], 404);
    }
    
    // Mark as read
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    jsonResponse([
        'success' => true,
        'message' => $message
    ]);
}

/**
 * Submit Contact Message
 */
function submitMessage() {
    $data = getJsonInput();
    
    // Validation
    if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
        jsonResponse(['success' => false, 'message' => 'Name, email, and message required'], 400);
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO contact_messages (name, email, subject, message) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['subject'] ?? null,
        $data['message']
    ]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Message sent successfully! We will get back to you soon.'
    ], 201);
}

/**
 * Mark Message as Read (Admin)
 */
function markAsRead() {
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Message ID required'], 400);
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Message not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Marked as read'
    ]);
}

/**
 * Delete Message (Admin)
 */
function deleteMessage($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Message not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Message deleted successfully'
    ]);
}
?>

