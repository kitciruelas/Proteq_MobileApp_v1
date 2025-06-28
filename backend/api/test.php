<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Test database connection
try {
    include_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo json_encode([
            "status" => "success",
            "message" => "API is working correctly",
            "database" => "Connected successfully",
            "timestamp" => date('Y-m-d H:i:s'),
            "endpoints" => [
                "GET /api/alerts" => "Get all alerts",
                "POST /api/alerts" => "Create new alert",
                "GET /api/alerts/{id}" => "Get alert by ID",
                "PUT /api/alerts/{id}" => "Update alert",
                "DELETE /api/alerts/{id}" => "Delete alert",
                "GET /api/alerts?nearby=1&lat={lat}&lng={lng}" => "Get nearby alerts"
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Database connection failed",
            "timestamp" => date('Y-m-d H:i:s')
        ]);
    }
} catch(Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage(),
        "timestamp" => date('Y-m-d H:i:s')
    ]);
}
?> 