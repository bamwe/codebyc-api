<?php
// api/index.php - API Documentation
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$api_info = [
    'project' => 'Codebyc Social Platform API',
    'description' => 'REST API for team to access database via curl commands',
    'base_url' => 'https://codebyc-api.onrender.com/api/',
    'status' => 'Live',
    'database' => 'Render PostgreSQL',
    'record_counts' => [],
    'endpoints' => [
        '/users' => [
            'GET' => 'Get all users (returns JSON array)',
            'POST' => 'Add new user (requires JSON: name, email)',
            'example_curl_get' => 'curl https://codebyc-api.onrender.com/api/users.php',
            'example_curl_post' => 'curl -X POST https://codebyc-api.onrender.com/api/users.php -H "Content-Type: application/json" -d \'{"name":"John","email":"john@codebyc.com"}\''
        ],
        '/messages' => [
            'GET' => 'Get all messages',
            'POST' => 'Send message (requires JSON: sender_id, receiver_id, message)',
            'example_curl_get' => 'curl https://codebyc-api.onrender.com/api/messages.php',
            'example_curl_post' => 'curl -X POST https://codebyc-api.onrender.com/api/messages.php -H "Content-Type: application/json" -d \'{"sender_id":1,"receiver_id":2,"message":"Hello!"}\''
        ],
        '/friends' => [
            'GET' => 'Get all friendships',
            'POST' => 'Add friendship (requires JSON: user1_id, user2_id)',
            'example_curl_get' => 'curl https://codebyc-api.onrender.com/api/friends.php',
            'example_curl_post' => 'curl -X POST https://codebyc-api.onrender.com/api/friends.php -H "Content-Type: application/json" -d \'{"user1_id":1,"user2_id":3}\''
        ],
        '/stats' => [
            'GET' => 'Get database statistics',
            'example' => 'curl https://codebyc-api.onrender.com/api/stats.php'
        ]
    ],
    'notes' => [
        'All responses are in JSON format',
        'Use Content-Type: application/json for POST requests',
        'CORS enabled for all origins',
        'Data is stored in Render PostgreSQL cloud database'
    ]
];

// Try to get actual counts
try {
    require_once 'config.php';
    
    $tables = ['users', 'messages', 'friendships', 'activity_logs'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        $api_info['record_counts'][$table] = $result['count'];
    }
    
    $conn = null;
} catch(Exception $e) {
    $api_info['record_counts'] = 'Unable to fetch counts';
}

echo json_encode($api_info, JSON_PRETTY_PRINT);
?>