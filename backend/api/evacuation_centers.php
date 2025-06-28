<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class EvacuationCenterController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new evacuation center
    public function create($data) {
        $query = "INSERT INTO evacuation_centers (name, latitude, longitude, capacity, contact_person, contact_number) 
                  VALUES (:name, :latitude, :longitude, :capacity, :contact_person, :contact_number)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":latitude", $data['latitude']);
        $stmt->bindParam(":longitude", $data['longitude']);
        $stmt->bindParam(":capacity", $data['capacity']);
        $stmt->bindParam(":contact_person", $data['contact_person']);
        $stmt->bindParam(":contact_number", $data['contact_number']);
        
        if($stmt->execute()) {
            $center_id = $this->conn->lastInsertId();
            return array(
                "success" => true,
                "message" => "Evacuation center created successfully",
                "center_id" => $center_id
            );
        }
        
        return array("success" => false, "message" => "Unable to create evacuation center");
    }
    
    // Get all evacuation centers
    public function getAll($filters = array()) {
        $query = "SELECT * FROM evacuation_centers";
        
        $conditions = array();
        $params = array();
        
        if(isset($filters['status'])) {
            $conditions[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get evacuation center by ID
    public function getById($id) {
        $query = "SELECT * FROM evacuation_centers WHERE center_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get nearby evacuation centers
    public function getNearby($lat, $lng, $radius = 10) {
        // Using Haversine formula to calculate distance
        $query = "SELECT *, 
                  (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * 
                   cos(radians(longitude) - radians(:lng)) + 
                   sin(radians(:lat)) * sin(radians(latitude)))) AS distance 
                  FROM evacuation_centers 
                  WHERE status != 'closed' 
                  HAVING distance <= :radius 
                  ORDER BY distance ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":lat", $lat);
        $stmt->bindParam(":lng", $lng);
        $stmt->bindParam(":radius", $radius);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update evacuation center
    public function update($id, $data) {
        $query = "UPDATE evacuation_centers SET 
                  name = :name, 
                  latitude = :latitude, 
                  longitude = :longitude, 
                  capacity = :capacity, 
                  contact_person = :contact_person, 
                  contact_number = :contact_number, 
                  status = :status 
                  WHERE center_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":latitude", $data['latitude']);
        $stmt->bindParam(":longitude", $data['longitude']);
        $stmt->bindParam(":capacity", $data['capacity']);
        $stmt->bindParam(":contact_person", $data['contact_person']);
        $stmt->bindParam(":contact_number", $data['contact_number']);
        $stmt->bindParam(":status", $data['status']);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Evacuation center updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update evacuation center");
    }
    
    // Update occupancy
    public function updateOccupancy($id, $occupancy) {
        $query = "UPDATE evacuation_centers SET current_occupancy = :occupancy WHERE center_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":occupancy", $occupancy);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Occupancy updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update occupancy");
    }
    
    // Delete evacuation center
    public function delete($id) {
        $query = "DELETE FROM evacuation_centers WHERE center_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Evacuation center deleted successfully");
        }
        
        return array("success" => false, "message" => "Unable to delete evacuation center");
    }
    
    // Get center statistics
    public function getStatistics() {
        $query = "SELECT 
                  COUNT(*) as total_centers,
                  SUM(capacity) as total_capacity,
                  SUM(current_occupancy) as total_occupancy,
                  AVG(current_occupancy * 100.0 / capacity) as avg_occupancy_rate,
                  COUNT(CASE WHEN status = 'open' THEN 1 END) as open_centers,
                  COUNT(CASE WHEN status = 'full' THEN 1 END) as full_centers,
                  COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_centers
                  FROM evacuation_centers";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get center resources
    public function getResources($center_id) {
        $query = "SELECT * FROM resources WHERE center_id = :center_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":center_id", $center_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update center resources
    public function updateResources($center_id, $resources) {
        // First delete existing resources
        $delete_query = "DELETE FROM resources WHERE center_id = :center_id";
        $delete_stmt = $this->conn->prepare($delete_query);
        $delete_stmt->bindParam(":center_id", $center_id);
        $delete_stmt->execute();
        
        // Insert new resources
        $insert_query = "INSERT INTO resources (center_id, type, quantity) VALUES (:center_id, :type, :quantity)";
        $insert_stmt = $this->conn->prepare($insert_query);
        
        foreach($resources as $resource) {
            $insert_stmt->bindParam(":center_id", $center_id);
            $insert_stmt->bindParam(":type", $resource['type']);
            $insert_stmt->bindParam(":quantity", $resource['quantity']);
            $insert_stmt->execute();
        }
        
        return array("success" => true, "message" => "Resources updated successfully");
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new EvacuationCenterController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'POST':
        if(end($path_parts) == 'evacuation-centers') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->create($data);
            http_response_code(201);
            echo json_encode($result);
        }
        break;
        
    case 'GET':
        if(end($path_parts) == 'evacuation-centers') {
            $filters = array();
            if(isset($_GET['status'])) $filters['status'] = $_GET['status'];
            
            if(isset($_GET['nearby']) && isset($_GET['lat']) && isset($_GET['lng'])) {
                $radius = isset($_GET['radius']) ? $_GET['radius'] : 10;
                $centers = $controller->getNearby($_GET['lat'], $_GET['lng'], $radius);
            } else {
                $centers = $controller->getAll($filters);
            }
            
            echo json_encode($centers);
        } elseif(end($path_parts) == 'statistics') {
            $stats = $controller->getStatistics();
            echo json_encode($stats);
        } elseif(is_numeric(end($path_parts))) {
            if(end($path_parts) == 'resources' && is_numeric($path_parts[count($path_parts)-2])) {
                $resources = $controller->getResources($path_parts[count($path_parts)-2]);
                echo json_encode($resources);
            } else {
                $center = $controller->getById(end($path_parts));
                if($center) {
                    echo json_encode($center);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Evacuation center not found"));
                }
            }
        }
        break;
        
    case 'PUT':
        if(is_numeric(end($path_parts))) {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(isset($data['occupancy'])) {
                $result = $controller->updateOccupancy(end($path_parts), $data['occupancy']);
            } elseif(isset($data['resources'])) {
                $result = $controller->updateResources(end($path_parts), $data['resources']);
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