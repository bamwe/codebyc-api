<?php
// api/messages.php - REST API for messages
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get messages with optional filters
        $limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 100) : 20;
        $sender_id = isset($_GET['sender_id']) ? intval($_GET['sender_id']) : null;
        $receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : null;
        
        $sql = "SELECT m.*, 
                       u1.profilename as sender_name,
                       u2.profilename as receiver_name
                FROM messages m
                LEFT JOIN users u1 ON m.sender_id = u1.id
                LEFT JOIN users u2 ON m.receiver_id = u2.id
                WHERE 1=1";
        
        $params = [];
        
        if ($sender_id) {
            $sql .= " AND m.sender_id = ?";
            $params[] = $sender_id;
        }
        
        if ($receiver_id) {
            $sql .= " AND m.receiver_id = ?";
            $params[] = $receiver_id;
        }
        
        $sql .= " ORDER BY m.sent_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $messages,
            'count' => count($messages)
        ]);
        break;
        
    case 'POST':
        // Send new message
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            break;
        }
        
        // Required fields
        if (empty($input['sender_id']) || empty($input['receiver_id']) || empty($input['message'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'sender_id, receiver_id, and message are required']);
            break;
        }
        
        try {
            $sql = "INSERT INTO messages (sender_id, receiver_id, message_text) 
                    VALUES (?, ?, ?) 
                    RETURNING *";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['sender_id'], $input['receiver_id'], $input['message']]);
            
            $newMessage = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $newMessage
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