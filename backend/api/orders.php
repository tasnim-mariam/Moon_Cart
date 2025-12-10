<?php
/**
 * MoonCart Orders API
 * Endpoints: Create Order, Get Orders, Get Single Order, Update Status
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'all';

switch ($method) {
    case 'GET':
        if ($action === 'single' && isset($_GET['id'])) {
            getOrder($_GET['id']);
        } elseif ($action === 'user' && isset($_GET['user_id'])) {
            getUserOrders($_GET['user_id']);
        } elseif ($action === 'stats') {
            getOrderStats();
        } elseif ($action === 'migrate') {
            runMigration();
        } else {
            getAllOrders();
        }
        break;
    
    case 'POST':
        createOrder();
        break;
    
    case 'PUT':
        if ($action === 'status') {
            updateOrderStatus();
        } elseif ($action === 'migrate') {
            runMigration();
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get All Orders (Admin)
 */
function getAllOrders() {
    $conn = getConnection();
    
    $status = $_GET['status'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Check if delivery_men table exists
    $deliveryMenTableExists = false;
    try {
        $checkStmt = $conn->query("SHOW TABLES LIKE 'delivery_men'");
        $deliveryMenTableExists = $checkStmt->rowCount() > 0;
    } catch (Exception $e) {
        $deliveryMenTableExists = false;
    }
    
    // Build SQL query with conditional JOIN
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email";
    
    if ($deliveryMenTableExists) {
        $sql .= ", dm.name as delivery_man_name, dm.phone as delivery_man_phone, dm.profile_image as delivery_man_image";
    } else {
        $sql .= ", NULL as delivery_man_name, NULL as delivery_man_phone, NULL as delivery_man_image";
    }
    
    $sql .= " FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id";
    
    if ($deliveryMenTableExists) {
        $sql .= " LEFT JOIN delivery_men dm ON o.delivery_man_id = dm.id";
    }
    
    $params = [];
    
    if ($status) {
        $sql .= " WHERE o.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    // Get items for each order and ensure proper data types
    foreach ($orders as &$order) {
        // Convert numeric fields to strings for frontend compatibility
        $order['id'] = (string)$order['id'];
        $order['user_id'] = (string)$order['user_id'];
        
        // Convert delivery_man_id to string if it exists
        if (isset($order['delivery_man_id']) && $order['delivery_man_id'] !== null) {
            $order['delivery_man_id'] = (string)$order['delivery_man_id'];
        }
        
        // Ensure order_number exists
        if (empty($order['order_number'])) {
            $order['order_number'] = 'ORD' . str_pad($order['id'], 8, '0', STR_PAD_LEFT);
        }
        
        // Add customer_name for frontend compatibility
        if (empty($order['customer_name']) && !empty($order['user_name'])) {
            $order['customer_name'] = $order['user_name'];
        }
        
        // If delivery_man_id exists but delivery_man_name is null, try to fetch it
        if (!empty($order['delivery_man_id']) && empty($order['delivery_man_name']) && $deliveryMenTableExists) {
            $dmStmt = $conn->prepare("SELECT name, phone FROM delivery_men WHERE id = ?");
            $dmStmt->execute([$order['delivery_man_id']]);
            $dm = $dmStmt->fetch();
            if ($dm) {
                $order['delivery_man_name'] = $dm['name'];
                $order['delivery_man_phone'] = $dm['phone'];
            }
        }
        
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
        $order['item_count'] = count($order['items']);
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) FROM orders";
    if ($status) {
        $countSql .= " WHERE status = ?";
        $stmt = $conn->prepare($countSql);
        $stmt->execute([$status]);
    } else {
        $stmt = $conn->query($countSql);
    }
    $total = $stmt->fetchColumn();
    
    jsonResponse([
        'success' => true,
        'count' => count($orders),
        'total' => (int)$total,
        'orders' => $orders
    ]);
}

/**
 * Get Single Order
 */
function getOrder($id) {
    $conn = getConnection();
    
    // Check if delivery_men table exists
    $deliveryMenTableExists = false;
    try {
        $checkStmt = $conn->query("SHOW TABLES LIKE 'delivery_men'");
        $deliveryMenTableExists = $checkStmt->rowCount() > 0;
    } catch (Exception $e) {
        $deliveryMenTableExists = false;
    }
    
    // Build SQL query with conditional JOIN
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email";
    
    if ($deliveryMenTableExists) {
        $sql .= ", dm.name as delivery_man_name, dm.phone as delivery_man_phone, dm.profile_image as delivery_man_image";
    } else {
        $sql .= ", NULL as delivery_man_name, NULL as delivery_man_phone, NULL as delivery_man_image";
    }
    
    $sql .= " FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id";
    
    if ($deliveryMenTableExists) {
        $sql .= " LEFT JOIN delivery_men dm ON o.delivery_man_id = dm.id";
    }
    
    $sql .= " WHERE o.id = ? OR o.order_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id, $id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }
    
    // Convert numeric fields to strings for frontend compatibility
    $order['id'] = (string)$order['id'];
    $order['user_id'] = (string)$order['user_id'];
    
    // Convert delivery_man_id to string if it exists
    if (isset($order['delivery_man_id']) && $order['delivery_man_id'] !== null) {
        $order['delivery_man_id'] = (string)$order['delivery_man_id'];
    }
    
    // Ensure order_number exists
    if (empty($order['order_number'])) {
        $order['order_number'] = 'ORD' . str_pad($order['id'], 8, '0', STR_PAD_LEFT);
    }
    
    // Add customer_name for frontend compatibility
    if (empty($order['customer_name']) && !empty($order['user_name'])) {
        $order['customer_name'] = $order['user_name'];
    }
    
    // If delivery_man_id exists but delivery_man_name is null, try to fetch it
    if (!empty($order['delivery_man_id']) && empty($order['delivery_man_name']) && $deliveryMenTableExists) {
        $dmStmt = $conn->prepare("SELECT name, phone FROM delivery_men WHERE id = ?");
        $dmStmt->execute([$order['delivery_man_id']]);
        $dm = $dmStmt->fetch();
        if ($dm) {
            $order['delivery_man_name'] = $dm['name'];
            $order['delivery_man_phone'] = $dm['phone'];
        }
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.image as product_image 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll();
    $order['item_count'] = count($order['items']);
    
    jsonResponse([
        'success' => true,
        'order' => $order
    ]);
}

/**
 * Get Orders by User
 */
function getUserOrders($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
    
    // Get items for each order and ensure proper data types
    foreach ($orders as &$order) {
        // Convert numeric fields to strings for frontend compatibility
        $order['id'] = (string)$order['id'];
        $order['user_id'] = (string)$order['user_id'];
        
        // Ensure order_number exists
        if (empty($order['order_number'])) {
            $order['order_number'] = 'ORD' . str_pad($order['id'], 8, '0', STR_PAD_LEFT);
        }
        
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
        $order['item_count'] = count($order['items']);
    }
    
    jsonResponse([
        'success' => true,
        'count' => count($orders),
        'orders' => $orders
    ]);
}

/**
 * Get Order Statistics (Admin Dashboard)
 */
function getOrderStats() {
    $conn = getConnection();
    
    // Total orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders");
    $totalOrders = $stmt->fetchColumn();
    
    // Orders by status
    $stmt = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status
    ");
    $statusCounts = $stmt->fetchAll();
    
    // Total revenue
    $stmt = $conn->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'");
    $totalRevenue = $stmt->fetchColumn() ?? 0;
    
    // Today's orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
    $todayOrders = $stmt->fetchColumn();
    
    // Today's revenue
    $stmt = $conn->query("SELECT SUM(total) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
    $todayRevenue = $stmt->fetchColumn() ?? 0;
    
    // Recent orders
    $stmt = $conn->query("
        SELECT o.*, u.name as user_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'stats' => [
            'total_orders' => (int)$totalOrders,
            'total_revenue' => (float)$totalRevenue,
            'today_orders' => (int)$todayOrders,
            'today_revenue' => (float)$todayRevenue,
            'status_breakdown' => $statusCounts,
            'recent_orders' => $recentOrders
        ]
    ]);
}

/**
 * Create New Order
 */
function createOrder() {
    $data = getJsonInput();
    
    // Validation
    $required = ['user_id', 'customer_name', 'email', 'phone', 'address', 'city', 'items'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            jsonResponse(['success' => false, 'message' => "$field is required"], 400);
        }
    }
    
    if (!is_array($data['items']) || count($data['items']) === 0) {
        jsonResponse(['success' => false, 'message' => 'Order must have at least one item'], 400);
    }
    
    $conn = getConnection();
    
    try {
        $conn->beginTransaction();
        
        // Generate order number
        $orderNumber = 'ORD' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Calculate totals
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $tax = $subtotal * 0.10; // 10% tax
        $shipping = $subtotal >= 5000 ? 0 : 50; // Free shipping over 5000
        $total = $subtotal + $tax + $shipping;
        
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                order_number, user_id, customer_name, email, phone, 
                address, city, zip_code, delivery_slot, delivery_instructions,
                payment_method, subtotal, tax, shipping, total, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $orderNumber,
            $data['user_id'],
            $data['customer_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['zip_code'] ?? null,
            $data['delivery_slot'] ?? null,
            $data['delivery_instructions'] ?? null,
            $data['payment_method'] ?? 'card',
            $subtotal,
            $tax,
            $shipping,
            $total
        ]);
        
        $orderId = $conn->lastInsertId();
        
        // Insert order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($data['items'] as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            
            // Handle product_id - convert to int or null if not numeric
            $productId = null;
            if (isset($item['product_id'])) {
                if (is_numeric($item['product_id'])) {
                    $productId = (int)$item['product_id'];
                }
            }
            
            // Use product_name from item, fallback to name
            $productName = $item['product_name'] ?? $item['name'] ?? 'Unknown Product';
            
            $stmt->execute([
                $orderId,
                $productId,
                $productName,
                $item['price'],
                $item['quantity'],
                $itemTotal
            ]);
            
            // Update product stock only if we have a valid numeric product_id
            if ($productId !== null) {
                $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $updateStock->execute([$item['quantity'], $productId]);
            }
        }
        
        $conn->commit();
        
        // Fetch created order
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'message' => 'Order placed successfully',
            'order' => $order
        ], 201);
        
    } catch (Exception $e) {
        $conn->rollBack();
        jsonResponse(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()], 500);
    }
}

/**
 * Update Order Status (Admin)
 */
function updateOrderStatus() {
    $data = getJsonInput();
    
    // Debug: Log received data (remove in production)
    error_log('Received data: ' . print_r($data, true));
    
    // Check if data is valid
    if (empty($data) || !is_array($data)) {
        jsonResponse(['success' => false, 'message' => 'Invalid request data'], 400);
    }
    
    if (empty($data['id']) || empty($data['status'])) {
        jsonResponse([
            'success' => false, 
            'message' => 'Order ID and status required',
            'debug' => ['received_data' => $data]
        ], 400);
    }
    
    $validStatuses = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled'];
    if (!in_array($data['status'], $validStatuses)) {
        jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
    }
    
    $conn = getConnection();
    
    // Check if columns exist before trying to update them
    $columns = [];
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM orders");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // If we can't check columns, just proceed with basic update
    }
    
    // Build update query
    $updates = ["status = ?"];
    $params = [$data['status']];
    
    // Handle cancellation reason (only if column exists)
    if (in_array('cancellation_reason', $columns)) {
        if ($data['status'] === 'cancelled' && isset($data['cancellation_reason'])) {
            $updates[] = "cancellation_reason = ?";
            $params[] = $data['cancellation_reason'];
        } elseif ($data['status'] !== 'cancelled') {
            // Clear cancellation reason if not cancelled
            $updates[] = "cancellation_reason = NULL";
        }
    }
    
    // Handle delivery man assignment (only if column exists)
    if (in_array('delivery_man_id', $columns) && isset($data['delivery_man_id'])) {
        $updates[] = "delivery_man_id = ?";
        $params[] = $data['delivery_man_id'];
    }
    
    // Handle estimated delivery time (only if column exists)
    if (in_array('estimated_delivery_time', $columns) && isset($data['estimated_delivery_time'])) {
        $updates[] = "estimated_delivery_time = ?";
        $params[] = $data['estimated_delivery_time'];
    }
    
    $params[] = $data['id'];
    $params[] = $data['id']; // For order_number check
    
    try {
        $sql = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = ? OR order_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Order status updated to ' . $data['status']
        ]);
    } catch (PDOException $e) {
        // Check if error is due to invalid ENUM value (database migration not run)
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, 'Data truncated') !== false || 
            strpos($errorMessage, 'Invalid') !== false ||
            strpos($errorMessage, '1265') !== false) {
            
            // Try to auto-run migration
            $migrationResult = tryAutoMigration($conn);
            
            if ($migrationResult['success']) {
                // Migration successful, retry the update
                try {
                    $sql = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = ? OR order_number = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() === 0) {
                        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
                    }
                    
                    jsonResponse([
                        'success' => true,
                        'message' => 'Order status updated to ' . $data['status'] . ' (database migrated automatically)'
                    ]);
                } catch (Exception $retryError) {
                    jsonResponse([
                        'success' => false, 
                        'message' => 'Migration completed but update failed: ' . $retryError->getMessage()
                    ], 500);
                }
            } else {
                // Auto-migration failed, provide manual instructions
                $migrationMessage = 'The database needs to be updated to support the "completed" status. ';
                $migrationMessage .= 'Please visit: http://localhost/Moon_Cart/backend/run_migration.php';
                $migrationMessage .= ' Or run this SQL: ALTER TABLE orders MODIFY COLUMN status ENUM(\'pending\', \'confirmed\', \'preparing\', \'out_for_delivery\', \'delivered\', \'completed\', \'cancelled\') DEFAULT \'pending\';';
                
                jsonResponse([
                    'success' => false, 
                    'message' => $migrationMessage,
                    'error_code' => 'MIGRATION_REQUIRED',
                    'migration_url' => 'http://localhost/Moon_Cart/backend/run_migration.php',
                    'auto_migration_failed' => $migrationResult['message']
                ], 400);
            }
        } else {
            jsonResponse([
                'success' => false, 
                'message' => 'Database error: ' . $errorMessage
            ], 500);
        }
    } catch (Exception $e) {
        jsonResponse([
            'success' => false, 
            'message' => 'Error updating order status: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Try to automatically run the migration
 */
function tryAutoMigration($conn) {
    try {
        // Check if 'completed' already exists
        $stmt = $conn->query("SHOW COLUMNS FROM orders WHERE Field = 'status'");
        $column = $stmt->fetch();
        
        if ($column && strpos($column['Type'], 'completed') !== false) {
            return ['success' => true, 'message' => 'Migration already applied'];
        }
        
        // Try to run the migration
        $sql = "ALTER TABLE orders 
                MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'completed', 'cancelled') DEFAULT 'pending'";
        
        $conn->exec($sql);
        
        return ['success' => true, 'message' => 'Migration completed successfully'];
    } catch (Exception $e) {
        return [
            'success' => false, 
            'message' => 'Auto-migration failed: ' . $e->getMessage() . '. Please run migration manually.'
        ];
    }
}

/**
 * Run Migration Endpoint
 */
function runMigration() {
    $conn = getConnection();
    $result = tryAutoMigration($conn);
    
    if ($result['success']) {
        jsonResponse([
            'success' => true,
            'message' => 'Database migration completed successfully!',
            'details' => $result['message']
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Migration failed',
            'error' => $result['message']
        ], 500);
    }
}
?>

