<?php
/**
 * MoonCart Users API
 * Endpoints: Login, Register, Get Users, Update Profile
 */

require_once '../config/database.php';
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            login();
        } elseif ($action === 'register') {
            register();
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
    
    case 'GET':
        if ($action === 'profile' && isset($_GET['id'])) {
            getProfile($_GET['id']);
        } elseif ($action === 'all') {
            getAllUsers();
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
    
    case 'PUT':
        if ($action === 'update') {
            updateProfile();
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }
        break;
    
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * User Login
 */
function login() {
    $data = getJsonInput();
    
    if (empty($data['email']) || empty($data['password'])) {
        jsonResponse(['success' => false, 'message' => 'Email and password required'], 400);
    }
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Verify password
    if (!password_verify($data['password'], $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid password'], 401);
    }
    
    // Remove password from response
    unset($user['password']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user
    ]);
}

/**
 * User Registration
 */
function register() {
    $data = getJsonInput();
    
    // Validation
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        jsonResponse(['success' => false, 'message' => 'Name, email, and password required'], 400);
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    $conn = getConnection();
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email already registered'], 409);
    }
    
    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['email'],
        $hashedPassword,
        $data['phone'] ?? null,
        $data['role'] ?? 'customer'
    ]);
    
    $userId = $conn->lastInsertId();
    
    // Fetch created user
    $stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    jsonResponse([
        'success' => true,
        'message' => 'Registration successful',
        'user' => $user
    ], 201);
}

/**
 * Get User Profile
 */
function getProfile($userId) {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, name, email, phone, role, avatar, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    // Get user addresses
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll();
    
    $user['addresses'] = $addresses;
    
    jsonResponse([
        'success' => true,
        'user' => $user
    ]);
}

/**
 * Get All Users (Admin only)
 */
function getAllUsers() {
    $conn = getConnection();
    $role = $_GET['role'] ?? null;
    
    if ($role) {
        $stmt = $conn->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE role = ? ORDER BY created_at DESC");
        $stmt->execute([$role]);
    } else {
        $stmt = $conn->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
    }
    
    $users = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'count' => count($users),
        'users' => $users
    ]);
}

/**
 * Update User Profile
 */
function updateProfile() {
    $data = getJsonInput();
    
    if (empty($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'User ID required'], 400);
    }
    
    $conn = getConnection();
    
    $updates = [];
    $params = [];
    
    if (!empty($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
    }
    if (!empty($data['phone'])) {
        $updates[] = "phone = ?";
        $params[] = $data['phone'];
    }
    if (!empty($data['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }
    
    $params[] = $data['id'];
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    jsonResponse([
        'success' => true,
        'message' => 'Profile updated successfully'
    ]);
}
?>

