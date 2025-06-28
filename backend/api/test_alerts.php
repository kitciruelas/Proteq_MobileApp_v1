<?php
// Test file for Alerts API endpoints
// This file shows you the correct URLs to use

echo "<h1>Alerts API Test Endpoints</h1>";
echo "<p>Base URL: http://your-domain.com/proteq_mobileapp/backend/api/alerts.php</p>";

echo "<h2>Available Endpoints:</h2>";

echo "<h3>GET Requests:</h3>";
echo "<ul>";
echo "<li><strong>Get All Alerts:</strong> GET /alerts.php/alerts</li>";
echo "<li><strong>Get Recent Alerts:</strong> GET /alerts.php/alerts?recent=1&limit=5</li>";
echo "<li><strong>Get Nearby Alerts:</strong> GET /alerts.php/alerts?nearby=1&lat=14.5995&lng=120.9842&radius=50</li>";
echo "<li><strong>Get Statistics:</strong> GET /alerts.php/statistics</li>";
echo "<li><strong>Get Alert by ID:</strong> GET /alerts.php/123</li>";
echo "<li><strong>Get Alerts by Type:</strong> GET /alerts.php/typhoon</li>";
echo "<li><strong>Get Alerts by Type:</strong> GET /alerts.php/fire</li>";
echo "<li><strong>Get Alerts by Type:</strong> GET /alerts.php/flood</li>";
echo "<li><strong>Get Alerts by Type:</strong> GET /alerts.php/earthquake</li>";
echo "<li><strong>Get Alerts by Type:</strong> GET /alerts.php/other</li>";
echo "</ul>";

echo "<h3>POST Requests:</h3>";
echo "<ul>";
echo "<li><strong>Create New Alert:</strong> POST /alerts.php/alerts</li>";
echo "</ul>";

echo "<h3>PUT Requests:</h3>";
echo "<ul>";
echo "<li><strong>Update Alert:</strong> PUT /alerts.php/123</li>";
echo "<li><strong>Update Status:</strong> PUT /alerts.php/123 (with JSON: {\"status\": \"resolved\"})</li>";
echo "</ul>";

echo "<h3>DELETE Requests:</h3>";
echo "<ul>";
echo "<li><strong>Delete Alert:</strong> DELETE /alerts.php/123</li>";
echo "</ul>";

echo "<h2>Example JSON Response Format:</h2>";
echo "<pre>";
echo json_encode(array(
    "success" => true,
    "message" => "Alerts retrieved successfully",
    "data" => array(
        array(
            "id" => 1,
            "alert_type" => "typhoon",
            "title" => "Typhoon Warning",
            "description" => "Strong typhoon approaching",
            "latitude" => 14.5995,
            "longitude" => 120.9842,
            "radius_km" => 100,
            "status" => "active",
            "created_at" => "2024-01-15 10:30:00"
        )
    ),
    "count" => 1
), JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h2>Common Error Responses:</h2>";
echo "<pre>";
echo json_encode(array(
    "success" => false,
    "message" => "Endpoint not found. Available endpoints: /alerts, /statistics, /{id}, /{type}",
    "data" => array(
        "available_endpoints" => [
            "GET /alerts - Get all alerts",
            "GET /alerts?recent=1 - Get recent alerts",
            "GET /alerts?nearby=1&lat=X&lng=Y - Get nearby alerts",
            "GET /statistics - Get alert statistics",
            "GET /{id} - Get alert by ID",
            "GET /{type} - Get alerts by type (typhoon, fire, flood, earthquake, other)",
            "POST /alerts - Create new alert",
            "PUT /{id} - Update alert",
            "DELETE /{id} - Delete alert"
        ]
    )
), JSON_PRETTY_PRINT);
echo "</pre>";

echo "<h2>Testing with cURL:</h2>";
echo "<pre>";
echo "# Get all alerts\n";
echo "curl -X GET \"http://your-domain.com/proteq_mobileapp/backend/api/alerts.php/alerts\"\n\n";
echo "# Create new alert\n";
echo "curl -X POST \"http://your-domain.com/proteq_mobileapp/backend/api/alerts.php/alerts\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\n";
echo "    \"alert_type\": \"typhoon\",\n";
echo "    \"title\": \"Typhoon Warning\",\n";
echo "    \"description\": \"Strong typhoon approaching\",\n";
echo "    \"latitude\": 14.5995,\n";
echo "    \"longitude\": 120.9842,\n";
echo "    \"radius_km\": 100\n";
echo "  }'\n\n";
echo "# Get statistics\n";
echo "curl -X GET \"http://your-domain.com/proteq_mobileapp/backend/api/alerts.php/statistics\"\n";
echo "</pre>";
?> 