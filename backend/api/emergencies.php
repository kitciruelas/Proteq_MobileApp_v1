<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class EmergencyController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new emergency
    public function create($data) {
        $query = "INSERT INTO emergencies (emergency_type, description, triggered_by) 
                  VALUES (:emergency_type, :description, :triggered_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":emergency_type", $data['emergency_type']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":triggered_by", $data['triggered_by']);
        
        if($stmt->execute()) {
            $emergency_id = $this->conn->lastInsertId();
            return array(
                "success" => true,
                "message" => "Emergency created successfully",
                "emergency_id" => $emergency_id
            );
        }
        
        return array("success" => false, "message" => "Unable to create emergency");
    }
    
    // Get all emergencies
    public function getAll($filters = array()) {
        $query = "SELECT e.*, a.name as triggered_by_name, r.name as resolved_by_name 
                  FROM emergencies e 
                  LEFT JOIN admin a ON e.triggered_by = a.admin_id 
                  LEFT JOIN admin r ON e.resolved_by = r.admin_id";
        
        $conditions = array();
        $params = array();
        
        if(isset($filters['is_active'])) {
            $conditions[] = "e.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }
        
        if(isset($filters['emergency_type'])) {
            $conditions[] = "e.emergency_type = :emergency_type";
            $params[':emergency_type'] = $filters['emergency_type'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY e.triggered_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get emergency by ID
    public function getById($id) {
        $query = "SELECT e.*, a.name as triggered_by_name, r.name as resolved_by_name 
                  FROM emergencies e 
                  LEFT JOIN admin a ON e.triggered_by = a.admin_id 
                  LEFT JOIN admin r ON e.resolved_by = r.admin_id 
                  WHERE e.emergency_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update emergency
    public function update($id, $data) {
        $query = "UPDATE emergencies SET 
                  emergency_type = :emergency_type, 
                  description = :description, 
                  is_active = :is_active, 
                  resolution_reason = :resolution_reason, 
                  resolved_by = :resolved_by, 
                  resolved_at = :resolved_at 
                  WHERE emergency_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":emergency_type", $data['emergency_type']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":is_active", $data['is_active']);
        $stmt->bindParam(":resolution_reason", $data['resolution_reason']);
        $stmt->bindParam(":resolved_by", $data['resolved_by']);
        $stmt->bindParam(":resolved_at", $data['resolved_at']);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Emergency updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update emergency");
    }
    
    // Resolve emergency
    public function resolve($id, $resolution_reason, $resolved_by) {
        $query = "UPDATE emergencies SET 
                  is_active = 0, 
                  resolution_reason = :resolution_reason, 
                  resolved_by = :resolved_by, 
                  resolved_at = NOW() 
                  WHERE emergency_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":resolution_reason", $resolution_reason);
        $stmt->bindParam(":resolved_by", $resolved_by);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Emergency resolved successfully");
        }
        
        return array("success" => false, "message" => "Unable to resolve emergency");
    }
    
    // Delete emergency
    public function delete($id) {
        $query = "DELETE FROM emergencies WHERE emergency_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Emergency deleted successfully");
        }
        
        return array("success" => false, "message" => "Unable to delete emergency");
    }
    
    // Get emergency statistics
    public function getStatistics() {
        $query = "SELECT 
                  COUNT(*) as total_emergencies,
                  COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_emergencies,
                  COUNT(CASE WHEN is_active = 0 THEN 1 END) as resolved_emergencies,
                  emergency_type,
                  COUNT(*) as count_by_type
                  FROM emergencies 
                  GROUP BY emergency_type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get active emergencies
    public function getActive() {
        $query = "SELECT e.*, a.name as triggered_by_name 
                  FROM emergencies e 
                  LEFT JOIN admin a ON e.triggered_by = a.admin_id 
                  WHERE e.is_active = 1 
                  ORDER BY e.triggered_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get resolved emergencies
    public function getResolved() {
        $query = "SELECT e.*, a.name as triggered_by_name, r.name as resolved_by_name 
                  FROM emergencies e 
                  LEFT JOIN admin a ON e.triggered_by = a.admin_id 
                  LEFT JOIN admin r ON e.resolved_by = r.admin_id 
                  WHERE e.is_active = 0 
                  ORDER BY e.resolved_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new EmergencyController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'POST':
        if(end($path_parts) == 'emergencies') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->create($data);
            http_response_code(201);
            echo json_encode($result);
        }
        break;
        
    case 'GET':
        if(end($path_parts) == 'emergencies') {
            $filters = array();
            if(isset($_GET['active'])) $filters['is_active'] = $_GET['active'];
            if(isset($_GET['type'])) $filters['emergency_type'] = $_GET['type'];
            
            $emergencies = $controller->getAll($filters);
            echo json_encode($emergencies);
        } elseif(end($path_parts) == 'active') {
            $emergencies = $controller->getActive();
            echo json_encode($emergencies);
        } elseif(end($path_parts) == 'resolved') {
            $emergencies = $controller->getResolved();
            echo json_encode($emergencies);
        } elseif(end($path_parts) == 'statistics') {
            $stats = $controller->getStatistics();
            echo json_encode($stats);
        } elseif(is_numeric(end($path_parts))) {
            $emergency = $controller->getById(end($path_parts));
            if($emergency) {
                echo json_encode($emergency);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Emergency not found"));
            }
        }
        break;
        
    case 'PUT':
        if(is_numeric(end($path_parts))) {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(isset($data['resolve']) && $data['resolve'] === true) {
                $result = $controller->resolve(end($path_parts), $data['resolution_reason'], $data['resolved_by']);
            } else {
                $result = $controller->update(end($path_parts), $data);
            }
            
            echo json_encode($result);
        }
        break;
        
    case 'DELETE':
        if(is_numeric(end($path_parts))) {
            $result = $controller->delete(end($path_parts));
            echo json_encode($result);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}
?> 