<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class SafetyProtocolController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new safety protocol
    public function create($data) {
        $query = "INSERT INTO safety_protocols (title, description, type, file_attachment, created_by) 
                  VALUES (:title, :description, :type, :file_attachment, :created_by)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":type", $data['type']);
        $stmt->bindParam(":file_attachment", $data['file_attachment']);
        $stmt->bindParam(":created_by", $data['created_by']);
        
        if($stmt->execute()) {
            $protocol_id = $this->conn->lastInsertId();
            return array(
                "success" => true,
                "message" => "Safety protocol created successfully",
                "protocol_id" => $protocol_id
            );
        }
        
        return array("success" => false, "message" => "Unable to create safety protocol");
    }
    
    // Get all safety protocols
    public function getAll($filters = array()) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM safety_protocols sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id";
        
        $conditions = array();
        $params = array();
        
        if(isset($filters['type'])) {
            $conditions[] = "sp.type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY sp.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get safety protocol by ID
    public function getById($id) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM safety_protocols sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE sp.protocol_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get protocols by type
    public function getByType($type) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM safety_protocols sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE sp.type = :type 
                  ORDER BY sp.title ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":type", $type);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Search protocols
    public function search($search_term) {
        $query = "SELECT sp.*, a.name as created_by_name 
                  FROM safety_protocols sp 
                  LEFT JOIN admin a ON sp.created_by = a.admin_id 
                  WHERE (sp.title LIKE :search OR sp.description LIKE :search) 
                  ORDER BY sp.title ASC";
        
        $search_pattern = "%$search_term%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search", $search_pattern);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update safety protocol
    public function update($id, $data) {
        $query = "UPDATE safety_protocols SET 
                  title = :title, 
                  description = :description, 
                  type = :type, 
                  file_attachment = :file_attachment 
                  WHERE protocol_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":title", $data['title']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":type", $data['type']);
        $stmt->bindParam(":file_attachment", $data['file_attachment']);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Safety protocol updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update safety protocol");
    }
    
    // Delete safety protocol
    public function delete($id) {
        $query = "DELETE FROM safety_protocols WHERE protocol_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Safety protocol deleted successfully");
        }
        
        return array("success" => false, "message" => "Unable to delete safety protocol");
    }
    
    // Get protocol types
    public function getTypes() {
        $query = "SELECT DISTINCT type FROM safety_protocols ORDER BY type ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Get protocol statistics
    public function getStatistics() {
        $query = "SELECT 
                  COUNT(*) as total_protocols,
                  type,
                  COUNT(*) as count_by_type
                  FROM safety_protocols 
                  GROUP BY type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new SafetyProtocolController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'POST':
        if(end($path_parts) == 'safety-protocols') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->create($data);
            http_response_code(201);
            echo json_encode($result);
        }
        break;
        
    case 'GET':
        if(end($path_parts) == 'safety-protocols') {
            $filters = array();
            if(isset($_GET['type'])) $filters['type'] = $_GET['type'];
            
            if(isset($_GET['search'])) {
                $protocols = $controller->search($_GET['search']);
            } else {
                $protocols = $controller->getAll($filters);
            }
            
            echo json_encode($protocols);
        } elseif(end($path_parts) == 'types') {
            $types = $controller->getTypes();
            echo json_encode($types);
        } elseif(end($path_parts) == 'statistics') {
            $stats = $controller->getStatistics();
            echo json_encode($stats);
        } elseif(is_numeric(end($path_parts))) {
            $protocol = $controller->getById(end($path_parts));
            if($protocol) {
                echo json_encode($protocol);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Safety protocol not found"));
            }
        } elseif(in_array(end($path_parts), ['fire', 'earthquake', 'medical', 'intrusion', 'general'])) {
            $protocols = $controller->getByType(end($path_parts));
            echo json_encode($protocols);
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