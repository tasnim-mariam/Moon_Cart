<?php
/**
 * MoonCart Addresses API
 * Endpoints: Get User Addresses, Create, Update, Delete, Set Default
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            getUserAddresses($_GET['user_id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        break;
    
    case 'POST':
        createAddress();
        break;
    
    case 'PUT':
        if ($action === 'default') {
            setDefaultAddress();
        } else {
            updateAddress();
        }
        break;
    
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteAddress($_GET['id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Address ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get User Addresses
 */
function getUserAddresses($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($addresses),
        'addresses' => $addresses
    ]);
}

/**
 * Create Address
 */
function createAddress() {
    $data = getJsonInput();
    
    // Validation
    if (empty($data['user_id']) || empty($data['address_line']) || empty($data['city'])) {
        jsonResponse(['success' => false, 'message' => 'User ID, address, and city required'], 400);
    }
    
    $conn = getConnection();
    
    // If this is the first address, make it default
    $stmt = $conn->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ?");
    $stmt->execute([$data['user_id']]);
    $isFirst = $stmt->fetchColumn() == 0;
    
    $stmt = $conn->prepare("
        INSERT INTO addresses (user_id, label, address_line, city, zip_code, phone, is_default) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['user_id'],
        $data['label'] ?? 'Home',
        $data['address_line'],
        $data['city'],
        $data['zip_code'] ?? null,
        $data['phone'] ?? null,
        $isFirst || ($data['is_default'] ?? false) ? 1 : 0
    ]);
    
    $addressId = $conn->lastInsertId();
    
    // If marked as default, unset other defaults
    if ($data['is_default'] ?? false) {
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ? AND id != ?");
        $stmt->execute([$data['user_id'], $addressId]);
    }
    
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ?");
    $stmt->execute([$addressId]);
    $address = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'message' => 'Address added successfully',
        'address' => $address
    ], 201);
}

/**
 * Update Address
 */
function updateAddress() {
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Address ID required'], 400);
    }
    
    $conn = getConnection();
    
    $updates = [];
    $params = [];
    
    $fields = ['label', 'address_line', 'city', 'zip_code', 'phone'];
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    $params[] = $data['id'];
    $sql = "UPDATE addresses SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    jsonResponse([
        'success' => true,
        'message' => 'Address updated successfully'
    ]);
}

/**
 * Set Default Address
 */
function setDefaultAddress() {
    $data = getJsonInput();
    
    if (empty($data['id']) || empty($data['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'Address ID and User ID required'], 400);
    }
    
    $conn = getConnection();
    
    // Unset all defaults for this user
    $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
    $stmt->execute([$data['user_id']]);
    
    // Set new default
    $stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$data['id'], $data['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Address not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Default address updated'
    ]);
}

/**
 * Delete Address
 */
function deleteAddress($id) {
    $conn = getConnection();
    
    // Check if it's the default address
    $stmt = $conn->prepare("SELECT user_id, is_default FROM addresses WHERE id = ?");
    $stmt->execute([$id]);
    $address = $stmt->fetch();
    
    if (!$address) {
        jsonResponse(['success' => false, 'message' => 'Address not found'], 404);
    }
    
    // Delete the address
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ?");
    $stmt->execute([$id]);
    
    // If it was default, make another address default
    if ($address['is_default']) {
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$address['user_id']]);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Address deleted successfully'
    ]);
}
?>

