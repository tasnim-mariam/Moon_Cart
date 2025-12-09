<?php
/**
 * MoonCart Cart API
 * Endpoints: Get Cart, Add to Cart, Update Quantity, Remove Item, Clear Cart
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            getCart($_GET['user_id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
        }
        break;
    
    case 'POST':
        addToCart();
        break;
    
    case 'PUT':
        updateCartItem();
        break;
    
    case 'DELETE':
        if ($action === 'clear' && isset($_GET['user_id'])) {
            clearCart($_GET['user_id']);
        } elseif (isset($_GET['user_id']) && isset($_GET['product_id'])) {
            removeFromCart($_GET['user_id'], $_GET['product_id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'User ID and Product ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get user's cart
 */
function getCart($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.stock as available_stock 
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.10; // 10% tax
    $shipping = $subtotal >= 5000 ? 0 : 50; // Free shipping over 5000
    $total = $subtotal + $tax + $shipping;
    $itemCount = array_sum(array_column($items, 'quantity'));
    
    jsonResponse([
        'success' => true,
        'cart' => [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2),
            'itemCount' => $itemCount
        ]
    ]);
}

/**
 * Add item to cart
 */
function addToCart() {
    $data = getJsonInput();
    
    // Validation
    if (empty($data['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'Please login to add items to cart'], 401);
    }
    
    if (empty($data['product_id'])) {
        jsonResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }
    
    $conn = getConnection();
    
    // Get product details if not provided
    $productId = (int)$data['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Product not found or unavailable'], 404);
    }
    
    // Check stock
    if ($product['stock'] <= 0) {
        jsonResponse(['success' => false, 'message' => 'Product is out of stock'], 400);
    }
    
    $userId = (int)$data['user_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    
    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        
        // Check stock availability
        if ($newQuantity > $product['stock']) {
            jsonResponse(['success' => false, 'message' => 'Not enough stock available. Available: ' . $product['stock']], 400);
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newQuantity, $existingItem['id']]);
        
        $message = 'Cart updated successfully';
    } else {
        // Insert new item
        $stmt = $conn->prepare("
            INSERT INTO cart (user_id, product_id, product_name, price, image, category, quantity) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $productId,
            $product['name'],
            $product['price'],
            $product['image'],
            $data['category'] ?? 'Product',
            $quantity
        ]);
        
        $message = 'Item added to cart';
    }
    
    // Return updated cart
    getCartResponse($userId, $message);
}

/**
 * Update cart item quantity
 */
function updateCartItem() {
    $data = getJsonInput();
    
    if (empty($data['user_id']) || empty($data['product_id'])) {
        jsonResponse(['success' => false, 'message' => 'User ID and Product ID required'], 400);
    }
    
    $conn = getConnection();
    
    $userId = (int)$data['user_id'];
    $productId = (int)$data['product_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : null;
    $change = isset($data['change']) ? (int)$data['change'] : null;
    
    // Get current cart item
    $stmt = $conn->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND c.product_id = ?");
    $stmt->execute([$userId, $productId]);
    $cartItem = $stmt->fetch();
    
    if (!$cartItem) {
        jsonResponse(['success' => false, 'message' => 'Item not found in cart'], 404);
    }
    
    // Calculate new quantity
    if ($quantity !== null) {
        $newQuantity = $quantity;
    } elseif ($change !== null) {
        $newQuantity = $cartItem['quantity'] + $change;
    } else {
        jsonResponse(['success' => false, 'message' => 'Quantity or change value required'], 400);
    }
    
    // Remove item if quantity is 0 or less
    if ($newQuantity <= 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        getCartResponse($userId, 'Item removed from cart');
        return;
    }
    
    // Check stock availability
    if ($newQuantity > $cartItem['stock']) {
        jsonResponse(['success' => false, 'message' => 'Not enough stock. Available: ' . $cartItem['stock']], 400);
    }
    
    // Update quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$newQuantity, $userId, $productId]);
    
    getCartResponse($userId, 'Cart updated');
}

/**
 * Remove item from cart
 */
function removeFromCart($userId, $productId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([(int)$userId, (int)$productId]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Item not found in cart'], 404);
    }
    
    getCartResponse($userId, 'Item removed from cart');
}

/**
 * Clear entire cart
 */
function clearCart($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([(int)$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Cart cleared successfully',
        'cart' => [
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0,
            'itemCount' => 0
        ]
    ]);
}

/**
 * Helper function to return cart with message
 */
function getCartResponse($userId, $message) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT c.*, p.stock as available_stock 
        FROM cart c
        LEFT JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.10;
    $shipping = $subtotal >= 5000 ? 0 : 50;
    $total = $subtotal + $tax + $shipping;
    $itemCount = array_sum(array_column($items, 'quantity'));
    
    jsonResponse([
        'success' => true,
        'message' => $message,
        'cart' => [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'total' => round($total, 2),
            'itemCount' => $itemCount
        ]
    ]);
}
?>

