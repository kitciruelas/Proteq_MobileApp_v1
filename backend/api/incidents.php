<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class IncidentController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new incident
    public function create($data) {
        $query = "INSERT INTO incident_reports (incident_type, description, longitude, latitude, reported_by, priority_level, reporter_safe_status) 
                  VALUES (:incident_type, :description, :longitude, :latitude, :reported_by, :priority_level, :reporter_safe_status)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":incident_type", $data['incident_type']);
        $stmt->bindParam(":description", $data['description']);
        $stmt->bindParam(":longitude", $data['longitude']);
        $stmt->bindParam(":latitude", $data['latitude']);
        $stmt->bindParam(":reported_by", $data['reported_by']);
        $stmt->bindParam(":priority_level", $data['priority_level']);
        $stmt->bindParam(":reporter_safe_status", $data['reporter_safe_status']);
        
        if($stmt->execute()) {
            $incident_id = $this->conn->lastInsertId();
            
            return array(
                "success" => true,
                "message" => "Incident reported successfully",
                "incident_id" => $incident_id
            );
        }
        
        return array("success" => false, "message" => "Unable to report incident");
    }
    
    // Get all incidents
    public function getAll($filters = array()) {
        $query = "SELECT ir.*, gu.first_name, gu.last_name, gu.email as reporter_email, 
                         s.name as assigned_staff_name, s.role as assigned_staff_role 
                  FROM incident_reports ir 
                  LEFT JOIN general_users gu ON ir.reported_by = gu.user_id 
                  LEFT JOIN staff s ON ir.assigned_to = s.staff_id";
        
        $conditions = array();
        $params = array();
        
        if(isset($filters['status'])) {
            $conditions[] = "ir.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(isset($filters['incident_type'])) {
            $conditions[] = "ir.incident_type = :incident_type";
            $params[':incident_type'] = $filters['incident_type'];
        }
        
        if(isset($filters['validation_status'])) {
            $conditions[] = "ir.validation_status = :validation_status";
            $params[':validation_status'] = $filters['validation_status'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY ir.date_reported DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get incident by ID
    public function getById($id) {
        $query = "SELECT ir.*, gu.first_name, gu.last_name, gu.email as reporter_email, 
                         s.name as assigned_staff_name, s.role as assigned_staff_role 
                  FROM incident_reports ir 
                  LEFT JOIN general_users gu ON ir.reported_by = gu.user_id 
                  LEFT JOIN staff s ON ir.assigned_to = s.staff_id 
                  WHERE ir.incident_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update incident status
    public function updateStatus($id, $status, $assigned_to = null) {
        $query = "UPDATE incident_reports SET status = :status";
        $params = array(":id" => $id, ":status" => $status);
        
        if($assigned_to) {
            $query .= ", assigned_to = :assigned_to";
            $params[':assigned_to'] = $assigned_to;
        }
        
        $query .= " WHERE incident_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Status updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update status");
    }
    
    // Update validation status
    public function updateValidation($id, $validation_status, $validation_notes = null) {
        $query = "UPDATE incident_reports SET validation_status = :validation_status";
        $params = array(":id" => $id, ":validation_status" => $validation_status);
        
        if($validation_notes) {
            $query .= ", validation_notes = :validation_notes";
            $params[':validation_notes'] = $validation_notes;
        }
        
        $query .= " WHERE incident_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Validation status updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update validation status");
    }
    
    // Get incidents by user
    public function getByUser($user_id) {
        $query = "SELECT ir.*, s.name as assigned_staff_name, s.role as assigned_staff_role 
                  FROM incident_reports ir 
                  LEFT JOIN staff s ON ir.assigned_to = s.staff_id 
                  WHERE ir.reported_by = :user_id 
                  ORDER BY ir.date_reported DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get incidents by staff
    public function getByStaff($staff_id) {
        $query = "SELECT ir.*, gu.first_name, gu.last_name, gu.email as reporter_email 
                  FROM incident_reports ir 
                  LEFT JOIN general_users gu ON ir.reported_by = gu.user_id 
                  WHERE ir.assigned_to = :staff_id 
                  ORDER BY ir.date_reported DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":staff_id", $staff_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get incident statistics
    public function getStatistics() {
        $query = "SELECT 
                  COUNT(*) as total_incidents,
                  COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                  COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
                  COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
                  COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_count,
                  COUNT(CASE WHEN validation_status = 'unvalidated' THEN 1 END) as unvalidated_count,
                  COUNT(CASE WHEN validation_status = 'validated' THEN 1 END) as validated_count,
                  COUNT(CASE WHEN validation_status = 'rejected' THEN 1 END) as rejected_count,
                  incident_type,
                  COUNT(*) as count_by_type
                  FROM incident_reports 
                  GROUP BY incident_type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new IncidentController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'POST':
        if(end($path_parts) == 'incidents') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->create($data);
            http_response_code(201);
            echo json_encode($result);
        }
        break;
        
    case 'GET':
        if(end($path_parts) == 'incidents') {
            $filters = array();
            if(isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if(isset($_GET['type'])) $filters['incident_type'] = $_GET['type'];
            if(isset($_GET['validation'])) $filters['validation_status'] = $_GET['validation'];
            
            $incidents = $controller->getAll($filters);
            echo json_encode($incidents);
        } elseif(end($path_parts) == 'statistics') {
            $stats = $controller->getStatistics();
            echo json_encode($stats);
        } elseif(is_numeric(end($path_parts))) {
            $incident = $controller->getById(end($path_parts));
            if($incident) {
                echo json_encode($incident);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Incident not found"));
            }
        } elseif(end($path_parts) == 'user' && is_numeric($path_parts[count($path_parts)-2])) {
            $incidents = $controller->getByUser($path_parts[count($path_parts)-2]);
            echo json_encode($incidents);
        } elseif(end($path_parts) == 'staff' && is_numeric($path_parts[count($path_parts)-2])) {
            $incidents = $controller->getByStaff($path_parts[count($path_parts)-2]);
            echo json_encode($incidents);
        }
        break;
        
    case 'PUT':
        if(is_numeric(end($path_parts))) {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(isset($data['validation_status'])) {
                $result = $controller->updateValidation(end($path_parts), $data['validation_status'], $data['validation_notes'] ?? null);
            } else {
                $result = $controller->updateStatus(end($path_parts), $data['status'], $data['assigned_to'] ?? null);
            }
            
            echo json_encode($result);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}
?> 