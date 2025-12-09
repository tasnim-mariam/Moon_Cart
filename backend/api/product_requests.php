<?php
/**
 * MoonCart Product Requests API
 * Endpoints: Create Request, Get Requests, Update Status
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'all';

switch ($method) {
    case 'GET':
        if ($action === 'user' && isset($_GET['user_id'])) {
            getUserRequests($_GET['user_id']);
        } elseif ($action === 'single' && isset($_GET['id'])) {
            getRequest($_GET['id']);
        } else {
            getAllRequests();
        }
        break;
    
    case 'POST':
        createRequest();
        break;
    
    case 'PUT':
        updateRequestStatus();
        break;
    
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteRequest($_GET['id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Request ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get All Requests (Admin)
 */
function getAllRequests() {
    $conn = getConnection();
    
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT pr.*, u.name as user_name 
            FROM product_requests pr 
            LEFT JOIN users u ON pr.user_id = u.id";
    
    $params = [];
    
    if ($status) {
        $sql .= " WHERE pr.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY pr.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($requests),
        'requests' => $requests
    ]);
}

/**
 * Get Single Request
 */
function getRequest($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT pr.*, u.name as user_name, u.email as user_email 
        FROM product_requests pr 
        LEFT JOIN users u ON pr.user_id = u.id 
        WHERE pr.id = ?
    ");
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    
    if (!$request) {
        jsonResponse(['success' => false, 'message' => 'Request not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'request' => $request
    ]);
}

/**
 * Get Requests by User
 */
function getUserRequests($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT * FROM product_requests 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $requests = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($requests),
        'requests' => $requests
    ]);
}

/**
 * Create Product Request
 */
function createRequest() {
    $data = getJsonInput();
    
    if (empty($data['product_name'])) {
        jsonResponse(['success' => false, 'message' => 'Product name is required'], 400);
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO product_requests (user_id, product_name, category, description, email, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->execute([
        $data['user_id'] ?? null,
        $data['product_name'],
        $data['category'] ?? null,
        $data['description'] ?? null,
        $data['email'] ?? null
    ]);
    
    $requestId = $conn->lastInsertId();
    
    $stmt = $conn->prepare("SELECT * FROM product_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'message' => 'Product request submitted successfully',
        'request' => $request
    ], 201);
}

/**
 * Update Request Status (Admin)
 */
function updateRequestStatus() {
    $data = getJsonInput();
    
    if (empty($data['id']) || empty($data['status'])) {
        jsonResponse(['success' => false, 'message' => 'Request ID and status required'], 400);
    }
    
    $validStatuses = ['pending', 'under_review', 'approved', 'rejected'];
    if (!in_array($data['status'], $validStatuses)) {
        jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
    }
    
    $conn = getConnection();
    
    $sql = "UPDATE product_requests SET status = ?";
    $params = [$data['status']];
    
    if (!empty($data['admin_notes'])) {
        $sql .= ", admin_notes = ?";
        $params[] = $data['admin_notes'];
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $data['id'];
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Request not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Request status updated to ' . $data['status']
    ]);
}

/**
 * Delete Request (Admin)
 */
function deleteRequest($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("DELETE FROM product_requests WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Request not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Request deleted successfully'
    ]);
}
?>

