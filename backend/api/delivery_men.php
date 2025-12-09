<?php
/**
 * MoonCart Delivery Men API
 * Endpoints: CRUD operations for delivery men
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'all';

switch ($method) {
    case 'GET':
        if ($action === 'single' && isset($_GET['id'])) {
            getDeliveryMan($_GET['id']);
        } else {
            getAllDeliveryMen();
        }
        break;
    
    case 'POST':
        createDeliveryMan();
        break;
    
    case 'PUT':
        updateDeliveryMan();
        break;
    
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteDeliveryMan($_GET['id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get All Delivery Men
 */
function getAllDeliveryMen() {
    $conn = getConnection();
    
    // Check if table exists
    try {
        $checkStmt = $conn->query("SHOW TABLES LIKE 'delivery_men'");
        if ($checkStmt->rowCount() === 0) {
            // Table doesn't exist, return empty array
            jsonResponse([
                'success' => true,
                'count' => 0,
                'delivery_men' => [],
                'message' => 'Delivery men table does not exist. Please run the migration SQL first.'
            ]);
            return;
        }
    } catch (Exception $e) {
        jsonResponse([
            'success' => false,
            'message' => 'Error checking for delivery_men table: ' . $e->getMessage()
        ], 500);
        return;
    }
    
    $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
    
    $sql = "SELECT * FROM delivery_men";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    
    try {
        $stmt = $conn->query($sql);
        $deliveryMen = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'count' => count($deliveryMen),
            'delivery_men' => $deliveryMen
        ]);
    } catch (Exception $e) {
        jsonResponse([
            'success' => false,
            'message' => 'Error fetching delivery men: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get Single Delivery Man
 */
function getDeliveryMan($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM delivery_men WHERE id = ?");
    $stmt->execute([$id]);
    $deliveryMan = $stmt->fetch();
    
    if (!$deliveryMan) {
        jsonResponse(['success' => false, 'message' => 'Delivery man not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'delivery_man' => $deliveryMan
    ]);
}

/**
 * Create New Delivery Man
 */
function createDeliveryMan() {
    $data = getJsonInput();
    
    // Validation
    $required = ['name', 'phone', 'nid'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => "$field is required"], 400);
        }
    }
    
    $conn = getConnection();
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO delivery_men (name, phone, nid, profile_image, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['nid'],
            $data['profile_image'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);
        
        $id = $conn->lastInsertId();
        
        // Fetch created delivery man
        $stmt = $conn->prepare("SELECT * FROM delivery_men WHERE id = ?");
        $stmt->execute([$id]);
        $deliveryMan = $stmt->fetch();
        
        jsonResponse([
            'success' => true,
            'message' => 'Delivery man created successfully',
            'delivery_man' => $deliveryMan
        ], 201);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['success' => false, 'message' => 'NID already exists'], 400);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create delivery man: ' . $e->getMessage()], 500);
        }
    }
}

/**
 * Update Delivery Man
 */
function updateDeliveryMan() {
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID is required'], 400);
    }
    
    $conn = getConnection();
    
    // Check if delivery man exists
    $stmt = $conn->prepare("SELECT * FROM delivery_men WHERE id = ?");
    $stmt->execute([$data['id']]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Delivery man not found'], 404);
    }
    
    // Build update query dynamically
    $fields = [];
    $values = [];
    
    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $values[] = $data['name'];
    }
    if (isset($data['phone'])) {
        $fields[] = "phone = ?";
        $values[] = $data['phone'];
    }
    if (isset($data['nid'])) {
        $fields[] = "nid = ?";
        $values[] = $data['nid'];
    }
    if (isset($data['profile_image'])) {
        $fields[] = "profile_image = ?";
        $values[] = $data['profile_image'];
    }
    if (isset($data['is_active'])) {
        $fields[] = "is_active = ?";
        $values[] = (int)$data['is_active'];
    }
    
    if (empty($fields)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    $values[] = $data['id'];
    $sql = "UPDATE delivery_men SET " . implode(", ", $fields) . " WHERE id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        
        // Fetch updated delivery man
        $stmt = $conn->prepare("SELECT * FROM delivery_men WHERE id = ?");
        $stmt->execute([$data['id']]);
        $deliveryMan = $stmt->fetch();
        
        jsonResponse([
            'success' => true,
            'message' => 'Delivery man updated successfully',
            'delivery_man' => $deliveryMan
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['success' => false, 'message' => 'NID already exists'], 400);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update delivery man: ' . $e->getMessage()], 500);
        }
    }
}

/**
 * Delete Delivery Man
 */
function deleteDeliveryMan($id) {
    $conn = getConnection();
    
    // Check if delivery man is assigned to any orders
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE delivery_man_id = ?");
    $stmt->execute([$id]);
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount > 0) {
        // Instead of deleting, deactivate
        $stmt = $conn->prepare("UPDATE delivery_men SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse([
            'success' => true,
            'message' => 'Delivery man deactivated (has assigned orders)'
        ]);
    } else {
        $stmt = $conn->prepare("DELETE FROM delivery_men WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse([
            'success' => true,
            'message' => 'Delivery man deleted successfully'
        ]);
    }
}
?>

