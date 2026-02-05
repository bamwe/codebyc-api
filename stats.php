<?php
// api/stats.php - Get database statistics
require_once 'config.php';

$sql = "
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM messages) as total_messages,
        (SELECT COUNT(*) FROM friendships WHERE status = 'friends') as total_friendships,
        (SELECT COUNT(*) FROM activity_logs) as total_activities,
        (SELECT COUNT(*) FROM users WHERE is_online = true) as online_users,
        (SELECT MAX(created_at) FROM users) as last_user_created,
        (SELECT MAX(sent_at) FROM messages) as last_message_sent
";

$stmt = $conn->query($sql);
$stats = $stmt->fetch();

// Add server info
$stats['server_time'] = date('Y-m-d H:i:s');
$stats['api_version'] = '1.0';
$stats['database'] = 'Render PostgreSQL';

echo json_encode([
    'success' => true,
    'data' => $stats
]);

$conn = null;
?>