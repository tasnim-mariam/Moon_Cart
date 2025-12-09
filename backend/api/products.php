<?php
/**
 * MoonCart Products API
 * Endpoints: Get All, Get By ID, Get By Category, Create, Update, Delete
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'all';

switch ($method) {
    case 'GET':
        if ($action === 'single' && isset($_GET['id'])) {
            getProduct($_GET['id']);
        } elseif ($action === 'category' && isset($_GET['category'])) {
            getProductsByCategory($_GET['category']);
        } elseif ($action === 'search' && isset($_GET['q'])) {
            searchProducts($_GET['q']);
        } else {
            getAllProducts();
        }
        break;
    
    case 'POST':
        createProduct();
        break;
    
    case 'PUT':
        updateProduct();
        break;
    
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteProduct($_GET['id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get All Products
 */
function getAllProducts() {
    $conn = getConnection();
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $products = $stmt->fetchAll();
    
    // Get total count
    $stmt = $conn->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
    $total = $stmt->fetchColumn();
    
    jsonResponse([
        'success' => true,
        'count' => count($products),
        'total' => (int)$total,
        'products' => $products
    ]);
}

/**
 * Get Single Product
 */
function getProduct($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'product' => $product
    ]);
}

/**
 * Get Products by Category
 */
function getProductsByCategory($categorySlug) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE c.slug = ? AND p.is_active = 1 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$categorySlug]);
    $products = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'category' => $categorySlug,
        'count' => count($products),
        'products' => $products
    ]);
}

/**
 * Search Products
 */
function searchProducts($query) {
    $conn = getConnection();
    
    $searchTerm = "%{$query}%";
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1 
        AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)
        ORDER BY p.name ASC
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $products = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'query' => $query,
        'count' => count($products),
        'products' => $products
    ]);
}

/**
 * Create Product (Admin)
 */
function createProduct() {
    $data = getJsonInput();
    
    // Validation
    if (empty($data['name']) || empty($data['price'])) {
        jsonResponse(['success' => false, 'message' => 'Name and price required'], 400);
    }
    
    $conn = getConnection();
    
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']));
    
    $stmt = $conn->prepare("
        INSERT INTO products (name, slug, description, price, original_price, image, category_id, badge, stock) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['name'],
        $slug,
        $data['description'] ?? null,
        $data['price'],
        $data['original_price'] ?? null,
        $data['image'] ?? null,
        $data['category_id'] ?? null,
        $data['badge'] ?? null,
        $data['stock'] ?? 100
    ]);
    
    $productId = $conn->lastInsertId();
    
    // Fetch created product
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'message' => 'Product created successfully',
        'product' => $product
    ], 201);
}

/**
 * Update Product (Admin)
 */
function updateProduct() {
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
    }
    
    $conn = getConnection();
    
    $updates = [];
    $params = [];
    
    $fields = ['name', 'description', 'price', 'original_price', 'image', 'category_id', 'badge', 'stock', 'is_active'];
    
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    // Update slug if name changed
    if (!empty($data['name'])) {
        $updates[] = "slug = ?";
        $params[] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']));
    }
    
    $params[] = $data['id'];
    $sql = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    jsonResponse([
        'success' => true,
        'message' => 'Product updated successfully'
    ]);
}

/**
 * Delete Product (Admin)
 */
function deleteProduct($id) {
    $conn = getConnection();
    
    // Soft delete (mark as inactive)
    $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
}
?>

