<?php
// api/users.php - REST API for users
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all users
        $limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 100) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        $sql = "SELECT id, userid, profilename, firstname, lastname, email, telephone, gender, created_at 
                FROM users 
                ORDER BY id 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $users = $stmt->fetchAll();
        
        // Get total count
        $countStmt = $conn->query("SELECT COUNT(*) as total FROM users");
        $total = $countStmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'returned' => count($users)
            ]
        ]);
        break;
        
    case 'POST':
        // Add new user
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            break;
        }
        
        // Required fields
        if (empty($input['name']) || empty($input['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Name and email are required']);
            break;
        }
        
        try {
            // Generate unique userid (8 digits)
            $userid = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            
            // Create profilename from name
            $profilename = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $input['name'])) . '_' . rand(100, 999);
            
            // Default values
            $telephone = $input['telephone'] ?? '+60123456789';
            $gender = $input['gender'] ?? 'other';
            
            $sql = "INSERT INTO users (userid, profilename, firstname, email, telephone, gender) 
                    VALUES (?, ?, ?, ?, ?, ?) 
                    RETURNING id, userid, profilename, firstname, email";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$userid, $profilename, $input['name'], $input['email'], $telephone, $gender]);
            
            $newUser = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $newUser
            ]);
            
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'OPTIONS':
        // Handle preflight
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

$conn = null;
?>