<?php
// api/friends.php - REST API for friendships
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all friendships
        $sql = "SELECT f.*, 
                       u1.profilename as user1_name,
                       u2.profilename as user2_name
                FROM friendships f
                LEFT JOIN users u1 ON f.user1_id = u1.id
                LEFT JOIN users u2 ON f.user2_id = u2.id
                ORDER BY f.requested_at DESC";
        
        $stmt = $conn->query($sql);
        $friendships = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $friendships,
            'count' => count($friendships)
        ]);
        break;
        
    case 'POST':
        // Add new friendship
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            break;
        }
        
        if (empty($input['user1_id']) || empty($input['user2_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user1_id and user2_id are required']);
            break;
        }
        
        try {
            // Check if friendship already exists
            $checkSql = "SELECT * FROM friendships 
                         WHERE (user1_id = ? AND user2_id = ?) 
                         OR (user1_id = ? AND user2_id = ?)";
            
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$input['user1_id'], $input['user2_id'], $input['user2_id'], $input['user1_id']]);
            
            if ($checkStmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'error' => 'Friendship already exists']);
                break;
            }
            
            // Insert new friendship
            $status = $input['status'] ?? 'pending';
            
            $sql = "INSERT INTO friendships (user1_id, user2_id, status) 
                    VALUES (?, ?, ?) 
                    RETURNING *";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['user1_id'], $input['user2_id'], $status]);
            
            $newFriendship = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Friendship request sent',
                'data' => $newFriendship
            ]);
            
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

$conn = null;
?>