<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class AlertController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new alert
    public function create($data) {
        // Validate required fields
        $required_fields = ['alert_type', 'title', 'description', 'latitude', 'longitude', 'radius_km'];
        foreach($required_fields as $field) {
            if(!isset($data[$field]) || empty($data[$field])) {
                return array(
                    "success" => false, 
                    "message" => "Missing required field: $field",
                    "data" => null
                );
            }
        }
        
        $query = "INSERT INTO alerts (alert_type, title, description, latitude, longitude, radius_km, status, created_at) 
                  VALUES (:alert_type, :title, :description, :latitude, :longitude, :radius_km, 'active', NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":alert_type", $data['alert_type']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":latitude", $data['latitude']);
        $stmt->bindParam(":longitude", $data['longitude']);
        $stmt->bindParam(":radius_km", $data['radius_km']);
        
        if($stmt->execute()) {
            $alert_id = $this->conn->lastInsertId();
            return array(
                "success" => true,
                "message" => "Alert created successfully",
                "data" => array("alert_id" => $alert_id)
            );
        }
        
        return array(
            "success" => false, 
            "message" => "Unable to create alert",
            "data" => null
        );
    }
    
    // Get all alerts
    public function getAll($filters = array()) {
        $query = "SELECT * FROM alerts";
        
        $conditions = array();
        $params = array();
        
        if(isset($filters['status'])) {
            $conditions[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['alert_type'])) {
            $conditions[] = "alert_type = :alert_type";
            $params[':alert_type'] = $filters['alert_type'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array(
            "success" => true,
            "message" => "Alerts retrieved successfully",
            "data" => $alerts,
            "count" => count($alerts)
        );
    }
    
    // Get alert by ID
    public function getById($id) {
        $query = "SELECT * FROM alerts WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $alert = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($alert) {
            return array(
                "success" => true,
                "message" => "Alert retrieved successfully",
                "data" => $alert
            );
        }
        
        return array(
            "success" => false,
            "message" => "Alert not found",
            "data" => null
        );
    }
    
    // Get nearby alerts
    public function getNearby($lat, $lng, $radius = 50) {
        // Using Haversine formula to calculate distance
        $query = "SELECT *, 
                  (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * 
                   cos(radians(longitude) - radians(:lng)) + 
                   sin(radians(:lat)) * sin(radians(latitude)))) AS distance 
                  FROM alerts 
                  WHERE status = 'active' 
                  HAVING distance <= :radius 
                  ORDER BY distance ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":lat", $lat);
        $stmt->bindParam(":lng", $lng);
        $stmt->bindParam(":radius", $radius);
        $stmt->execute();
        
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array(
            "success" => true,
            "message" => "Nearby alerts retrieved successfully",
            "data" => $alerts,
            "count" => count($alerts),
            "search_params" => array(
                "latitude" => $lat,
                "longitude" => $lng,
                "radius_km" => $radius
            )
        );
    }
    
    // Update alert
    public function update($id, $data) {
        $query = "UPDATE alerts SET 
                  alert_type = :alert_type, 
                  title = :title, 
                  description = :description, 
                  latitude = :latitude, 
                  longitude = :longitude, 
                  radius_km = :radius_km, 
                  status = :status,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":alert_type", $data['alert_type']);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":latitude", $data['latitude']);
        $stmt->bindParam(":longitude", $data['longitude']);
        $stmt->bindParam(":radius_km", $data['radius_km']);
        $stmt->bindParam(":status", $data['status']);
        
        if($stmt->execute()) {
            return array(
                "success" => true, 
                "message" => "Alert updated successfully",
                "data" => array("alert_id" => $id)
            );
        }
        
        return array(
            "success" => false, 
            "message" => "Unable to update alert",
            "data" => null
        );
    }
    
    // Update alert status
    public function updateStatus($id, $status) {
        $query = "UPDATE alerts SET status = :status, updated_at = NOW() WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);
        
        if($stmt->execute()) {
            return array(
                "success" => true, 
                "message" => "Alert status updated successfully",
                "data" => array("alert_id" => $id, "status" => $status)
            );
        }
        
        return array(
            "success" => false, 
            "message" => "Unable to update alert status",
            "data" => null
        );
    }
    
    // Delete alert
    public function delete($id) {
        $query = "DELETE FROM alerts WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return array(
                "success" => true, 
                "message" => "Alert deleted successfully",
                "data" => array("alert_id" => $id)
            );
        }
        
        return array(
            "success" => false, 
            "message" => "Unable to delete alert",
            "data" => null
        );
    }
    
    // Get alert statistics
    public function getStatistics() {
        $query = "SELECT 
                  COUNT(*) as total_alerts,
                  COUNT(CASE WHEN status = 'active' THEN 1 END) as active_alerts,
                  COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_alerts,
                  alert_type,
                  COUNT(*) as count_by_type
                  FROM alerts 
                  GROUP BY alert_type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array(
            "success" => true,
            "message" => "Statistics retrieved successfully",
            "data" => $stats
        );
    }
    
    // Get alerts by type
    public function getByType($type) {
        $query = "SELECT * FROM alerts WHERE alert_type = :type ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":type", $type);
        $stmt->execute();
        
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array(
            "success" => true,
            "message" => "Alerts by type retrieved successfully",
            "data" => $alerts,
            "count" => count($alerts),
            "alert_type" => $type
        );
    }
    
    // Get recent alerts
    public function getRecent($limit = 10) {
        $query = "SELECT * FROM alerts ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array(
            "success" => true,
            "message" => "Recent alerts retrieved successfully",
            "data" => $alerts,
            "count" => count($alerts),
            "limit" => $limit
        );
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new AlertController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Debug information
error_log("Request Method: " . $method);
error_log("Request Path: " . $path);
error_log("Path Parts: " . print_r($path_parts, true));

try {
    switch($method) {
        case 'POST':
            // Handle POST requests for creating alerts
            if(end($path_parts) == 'alerts' || in_array('alerts', $path_parts)) {
                $data = json_decode(file_get_contents("php://input"), true);
                if($data === null) {
                    http_response_code(400);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Invalid JSON data",
                        "data" => null
                    ));
                    exit;
                }
                $result = $controller->create($data);
                if($result['success']) {
                    http_response_code(201);
                } else {
                    http_response_code(400);
                }
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "POST endpoint not found. Use /alerts to create new alerts.",
                    "data" => null
                ));
            }
            break;
            
        case 'GET':
            // Handle GET requests
            if(end($path_parts) == 'alerts' || in_array('alerts', $path_parts)) {
                $filters = array();
                if(isset($_GET['status'])) $filters['status'] = $_GET['status'];
                if(isset($_GET['type'])) $filters['alert_type'] = $_GET['type'];
                
                if(isset($_GET['nearby']) && isset($_GET['lat']) && isset($_GET['lng'])) {
                    $radius = isset($_GET['radius']) ? $_GET['radius'] : 50;
                    $result = $controller->getNearby($_GET['lat'], $_GET['lng'], $radius);
                } elseif(isset($_GET['recent'])) {
                    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
                    $result = $controller->getRecent($limit);
                } else {
                    $result = $controller->getAll($filters);
                }
                
                echo json_encode($result);
            } elseif(end($path_parts) == 'statistics') {
                $result = $controller->getStatistics();
                echo json_encode($result);
            } elseif(is_numeric(end($path_parts))) {
                $result = $controller->getById(end($path_parts));
                if(!$result['success']) {
                    http_response_code(404);
                }
                echo json_encode($result);
            } elseif(in_array(end($path_parts), ['typhoon', 'fire', 'flood', 'earthquake', 'other'])) {
                $result = $controller->getByType(end($path_parts));
                echo json_encode($result);
            } else {
                // If no specific endpoint matches, return available endpoints
                http_response_code(404);
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
                        ],
                        "current_path" => $path,
                        "path_parts" => $path_parts
                    )
                ));
            }
            break;
            
        case 'PUT':
            if(is_numeric(end($path_parts))) {
                $data = json_decode(file_get_contents("php://input"), true);
                if($data === null) {
                    http_response_code(400);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Invalid JSON data",
                        "data" => null
                    ));
                    exit;
                }
                
                if(isset($data['status']) && count($data) == 1) {
                    $result = $controller->updateStatus(end($path_parts), $data['status']);
                } else {
                    $result = $controller->update(end($path_parts), $data);
                }
                
                if(!$result['success']) {
                    http_response_code(400);
                }
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "PUT endpoint not found. Use /{id} to update alerts.",
                    "data" => null
                ));
            }
            break;
            
        case 'DELETE':
            if(is_numeric(end($path_parts))) {
                $result = $controller->delete(end($path_parts));
                if(!$result['success']) {
                    http_response_code(400);
                }
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "DELETE endpoint not found. Use /{id} to delete alerts.",
                    "data" => null
                ));
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(array(
                "success" => false,
                "message" => "Method not allowed. Use GET, POST, PUT, or DELETE.",
                "data" => null
            ));
            break;
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Internal server error: " . $e->getMessage(),
        "data" => null
    ));
}
?> 