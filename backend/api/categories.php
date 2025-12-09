<?php
/**
 * MoonCart Categories API
 * Endpoints: Get All, Get By ID, Create, Update, Delete
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'all';

switch ($method) {
    case 'GET':
        if ($action === 'single' && isset($_GET['id'])) {
            getCategory($_GET['id']);
        } elseif ($action === 'with_products') {
            getCategoriesWithProducts();
        } else {
            getAllCategories();
        }
        break;
    
    case 'POST':
        createCategory();
        break;
    
    case 'PUT':
        updateCategory();
        break;
    
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteCategory($_GET['id']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Category ID required'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Get All Categories
 */
function getAllCategories() {
    $conn = getConnection();
    
    $stmt = $conn->query("
        SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($categories),
        'categories' => $categories
    ]);
}

/**
 * Get Single Category
 */
function getCategory($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        jsonResponse(['success' => false, 'message' => 'Category not found'], 404);
    }
    
    // Get products in this category
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $category['products'] = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'category' => $category
    ]);
}

/**
 * Get Categories with Product Counts
 */
function getCategoriesWithProducts() {
    $conn = getConnection();
    
    $stmt = $conn->query("
        SELECT 
            c.*,
            COUNT(p.id) as product_count,
            SUM(p.stock) as total_stock
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
        GROUP BY c.id 
        ORDER BY product_count DESC
    ");
    $categories = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'categories' => $categories
    ]);
}

/**
 * Create Category (Admin)
 */
function createCategory() {
    $data = getJsonInput();
    
    if (empty($data['name'])) {
        jsonResponse(['success' => false, 'message' => 'Category name required'], 400);
    }
    
    $conn = getConnection();
    
    // Generate slug
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']));
    
    // Check if slug exists
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Category already exists'], 409);
    }
    
    $stmt = $conn->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $slug,
        $data['icon'] ?? null,
        $data['description'] ?? null
    ]);
    
    $categoryId = $conn->lastInsertId();
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'message' => 'Category created successfully',
        'category' => $category
    ], 201);
}

/**
 * Update Category (Admin)
 */
function updateCategory() {
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'Category ID required'], 400);
    }
    
    $conn = getConnection();
    
    $updates = [];
    $params = [];
    
    if (!empty($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
        $updates[] = "slug = ?";
        $params[] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name']));
    }
    if (isset($data['icon'])) {
        $updates[] = "icon = ?";
        $params[] = $data['icon'];
    }
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    $params[] = $data['id'];
    $sql = "UPDATE categories SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    jsonResponse([
        'success' => true,
        'message' => 'Category updated successfully'
    ]);
}

/**
 * Delete Category (Admin)
 */
function deleteCategory($id) {
    $conn = getConnection();
    
    // Check if category has products
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();
    
    if ($productCount > 0) {
        jsonResponse([
            'success' => false, 
            'message' => "Cannot delete category. It has {$productCount} products. Please reassign products first."
        ], 400);
    }
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Category not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Category deleted successfully'
    ]);
}
?>

