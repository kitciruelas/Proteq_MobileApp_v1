<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class WelfareCheckController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new welfare check
    public function create($data) {
        $query = "INSERT INTO welfare_checks (user_id, emergency_id, status, remarks) 
                  VALUES (:user_id, :emergency_id, :status, :remarks)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->bindParam(":emergency_id", $data['emergency_id']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":remarks", $data['remarks']);
        
        if($stmt->execute()) {
            $check_id = $this->conn->lastInsertId();
            return array(
                "success" => true,
                "message" => "Welfare check created successfully",
                "check_id" => $check_id
            );
        }
        
        return array("success" => false, "message" => "Unable to create welfare check");
    }
    
    // Get all welfare checks
    public function getAll($filters = array()) {
        $query = "SELECT wc.*, gu.first_name, gu.last_name, gu.email as user_email, 
                         e.emergency_type, e.description as emergency_description 
                  FROM welfare_checks wc 
                  LEFT JOIN general_users gu ON wc.user_id = gu.user_id 
                  LEFT JOIN emergencies e ON wc.emergency_id = e.emergency_id";
        
        $conditions = array();
        $params = array();
        
        if(isset($filters['status'])) {
            $conditions[] = "wc.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['user_id'])) {
            $conditions[] = "wc.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if(isset($filters['emergency_id'])) {
            $conditions[] = "wc.emergency_id = :emergency_id";
            $params[':emergency_id'] = $filters['emergency_id'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY wc.reported_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get welfare check by ID
    public function getById($id) {
        $query = "SELECT wc.*, gu.first_name, gu.last_name, gu.email as user_email, 
                         e.emergency_type, e.description as emergency_description 
                  FROM welfare_checks wc 
                  LEFT JOIN general_users gu ON wc.user_id = gu.user_id 
                  LEFT JOIN emergencies e ON wc.emergency_id = e.emergency_id 
                  WHERE wc.welfare_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get welfare checks by emergency
    public function getByEmergency($emergency_id) {
        $query = "SELECT wc.*, gu.first_name, gu.last_name, gu.email as user_email 
                  FROM welfare_checks wc 
                  LEFT JOIN general_users gu ON wc.user_id = gu.user_id 
                  WHERE wc.emergency_id = :emergency_id 
                  ORDER BY wc.reported_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":emergency_id", $emergency_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get welfare checks by user
    public function getByUser($user_id) {
        $query = "SELECT wc.*, e.emergency_type, e.description as emergency_description 
                  FROM welfare_checks wc 
                  LEFT JOIN emergencies e ON wc.emergency_id = e.emergency_id 
                  WHERE wc.user_id = :user_id 
                  ORDER BY wc.reported_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get welfare checks by status
    public function getByStatus($status) {
        $query = "SELECT wc.*, gu.first_name, gu.last_name, gu.email as user_email, 
                         e.emergency_type, e.description as emergency_description 
                  FROM welfare_checks wc 
                  LEFT JOIN general_users gu ON wc.user_id = gu.user_id 
                  LEFT JOIN emergencies e ON wc.emergency_id = e.emergency_id 
                  WHERE wc.status = :status 
                  ORDER BY wc.reported_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update welfare check
    public function update($id, $data) {
        $query = "UPDATE welfare_checks SET 
                  status = :status, 
                  remarks = :remarks 
                  WHERE welfare_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":remarks", $data['remarks']);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Welfare check updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update welfare check");
    }
    
    // Delete welfare check
    public function delete($id) {
        $query = "DELETE FROM welfare_checks WHERE welfare_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Welfare check deleted successfully");
        }
        
        return array("success" => false, "message" => "Unable to delete welfare check");
    }
    
    // Get welfare check statistics
    public function getStatistics() {
        $query = "SELECT 
                  COUNT(*) as total_checks,
                  COUNT(CASE WHEN status = 'SAFE' THEN 1 END) as safe_count,
                  COUNT(CASE WHEN status = 'NEEDS_HELP' THEN 1 END) as needs_help_count,
                  COUNT(CASE WHEN status = 'NO_RESPONSE' THEN 1 END) as no_response_count,
                  DATE(reported_at) as check_date,
                  COUNT(*) as checks_per_day
                  FROM welfare_checks 
                  WHERE reported_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY DATE(reported_at)
                  ORDER BY check_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get users without welfare checks for an emergency
    public function getUsersWithoutChecks($emergency_id) {
        $query = "SELECT gu.user_id, gu.first_name, gu.last_name, gu.email 
                  FROM general_users gu 
                  LEFT JOIN welfare_checks wc ON gu.user_id = wc.user_id AND wc.emergency_id = :emergency_id
                  WHERE wc.welfare_id IS NULL AND gu.status = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":emergency_id", $emergency_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Bulk create welfare checks for emergency
    public function createBulkForEmergency($emergency_id) {
        // Get all active users
        $users_query = "SELECT user_id FROM general_users WHERE status = 1";
        $users_stmt = $this->conn->prepare($users_query);
        $users_stmt->execute();
        $users = $users_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Check which users already have welfare checks for this emergency
        $existing_query = "SELECT user_id FROM welfare_checks WHERE emergency_id = :emergency_id";
        $existing_stmt = $this->conn->prepare($existing_query);
        $existing_stmt->bindParam(":emergency_id", $emergency_id);
        $existing_stmt->execute();
        $existing_users = $existing_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Create welfare checks for users who don't have them
        $insert_query = "INSERT INTO welfare_checks (user_id, emergency_id, status) VALUES (:user_id, :emergency_id, 'NO_RESPONSE')";
        $insert_stmt = $this->conn->prepare($insert_query);
        
        $created_count = 0;
        foreach($users as $user_id) {
            if(!in_array($user_id, $existing_users)) {
                $insert_stmt->bindParam(":user_id", $user_id);
                $insert_stmt->bindParam(":emergency_id", $emergency_id);
                if($insert_stmt->execute()) {
                    $created_count++;
                }
            }
        }
        
        return array(
            "success" => true,
            "message" => "Created $created_count welfare checks",
            "created_count" => $created_count
        );
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new WelfareCheckController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'POST':
        if(end($path_parts) == 'welfare-checks') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->create($data);
            http_response_code(201);
            echo json_encode($result);
        } elseif(end($path_parts) == 'bulk-create' && is_numeric($path_parts[count($path_parts)-2])) {
            $result = $controller->createBulkForEmergency($path_parts[count($path_parts)-2]);
            echo json_encode($result);
        }
        break;
        
    case 'GET':
        if(end($path_parts) == 'welfare-checks') {
            $filters = array();
            if(isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if(isset($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
            if(isset($_GET['emergency_id'])) $filters['emergency_id'] = $_GET['emergency_id'];
            
            $checks = $controller->getAll($filters);
            echo json_encode($checks);
        } elseif(end($path_parts) == 'statistics') {
            $stats = $controller->getStatistics();
            echo json_encode($stats);
        } elseif(end($path_parts) == 'missing-users' && is_numeric($path_parts[count($path_parts)-2])) {
            $users = $controller->getUsersWithoutChecks($path_parts[count($path_parts)-2]);
            echo json_encode($users);
        } elseif(is_numeric(end($path_parts))) {
            $check = $controller->getById(end($path_parts));
            if($check) {
                echo json_encode($check);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Welfare check not found"));
            }
        } elseif(end($path_parts) == 'emergency' && is_numeric($path_parts[count($path_parts)-2])) {
            $checks = $controller->getByEmergency($path_parts[count($path_parts)-2]);
            echo json_encode($checks);
        } elseif(end($path_parts) == 'user' && is_numeric($path_parts[count($path_parts)-2])) {
            $checks = $controller->getByUser($path_parts[count($path_parts)-2]);
            echo json_encode($checks);
        } elseif(in_array(end($path_parts), ['SAFE', 'NEEDS_HELP', 'NO_RESPONSE'])) {
            $checks = $controller->getByStatus(end($path_parts));
            echo json_encode($checks);
        }
        break;
        
    case 'PUT':
        if(is_numeric(end($path_parts))) {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->update(end($path_parts), $data);
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