<?php
// api/config.php - Database configuration for Render
header('Content-Type: application/json');

// Allow CORS (Cross-Origin Resource Sharing) for team access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Render PostgreSQL connection details
$host = "dpg-d61elqlactks73b614tg-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "codebyc_db";
$username = "codebyc_admin";
$password = "EsyrtXxGwLi3EMqNEFf4VedDErVdT4XA";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>