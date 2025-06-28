<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class UserController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // User registration
    public function register($data) {
        // Check if user already exists
        $check_query = "SELECT user_id FROM general_users WHERE email = :email";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":email", $data['email']);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            return array("success" => false, "message" => "User already exists");
        }
        
        // Hash password
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO general_users (first_name, last_name, user_type, password, email, department, college) 
                  VALUES (:first_name, :last_name, :user_type, :password, :email, :department, :college)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":first_name", $data['first_name']);
        $stmt->bindParam(":last_name", $data['last_name']);
        $stmt->bindParam(":user_type", $data['user_type']);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":department", $data['department']);
        $stmt->bindParam(":college", $data['college']);
        
        if($stmt->execute()) {
            $user_id = $this->conn->lastInsertId();
            return array(
                "success" => true,
                "message" => "User registered successfully",
                "user_id" => $user_id
            );
        }
        
        return array("success" => false, "message" => "Unable to register user");
    }
    
    // User login
    public function login($data) {
        $query = "SELECT user_id, first_name, last_name, user_type, password, email, department, college, status 
                  FROM general_users WHERE email = :email AND status = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $data['email']);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($data['password'], $user['password'])) {
                // Remove password from response
                unset($user['password']);
                
                return array(
                    "success" => true,
                    "message" => "Login successful",
                    "user" => $user
                );
            }
        }
        
        return array("success" => false, "message" => "Invalid credentials");
    }
    
    // Get user by ID
    public function getById($id) {
        $query = "SELECT user_id, first_name, last_name, user_type, email, department, college, created_at, status 
                  FROM general_users WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update user location
    public function updateLocation($user_id, $lat, $lng) {
        // Check if location exists
        $check_query = "SELECT location_id FROM user_locations WHERE user_id = :user_id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":user_id", $user_id);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            // Update existing location
            $query = "UPDATE user_locations SET latitude = :lat, longitude = :lng WHERE user_id = :user_id";
        } else {
            // Insert new location
            $query = "INSERT INTO user_locations (user_id, latitude, longitude) VALUES (:user_id, :lat, :lng)";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":lat", $lat);
        $stmt->bindParam(":lng", $lng);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Location updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update location");
    }
    
    // Get user location
    public function getLocation($user_id) {
        $query = "SELECT latitude, longitude, created_at FROM user_locations WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get all staff (responders)
    public function getStaff() {
        $query = "SELECT staff_id, name, email, role, availability, status FROM staff WHERE status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get staff locations
    public function getStaffLocations() {
        $query = "SELECT sl.*, s.name, s.role, s.availability 
                  FROM staff_locations sl 
                  JOIN staff s ON sl.staff_id = s.staff_id 
                  WHERE s.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update user profile
    public function updateProfile($id, $data) {
        $query = "UPDATE general_users SET first_name = :first_name, last_name = :last_name, department = :department, college = :college WHERE user_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":first_name", $data['first_name']);
        $stmt->bindParam(":last_name", $data['last_name']);
        $stmt->bindParam(":department", $data['department']);
        $stmt->bindParam(":college", $data['college']);
        
        if($stmt->execute()) {
            return array("success" => true, "message" => "Profile updated successfully");
        }
        
        return array("success" => false, "message" => "Unable to update profile");
    }
    
    // Change password
    public function changePassword($id, $current_password, $new_password) {
        // Verify current password
        $query = "SELECT password FROM general_users WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($current_password, $user['password'])) {
                // Hash new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_query = "UPDATE general_users SET password = :password WHERE user_id = :id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":id", $id);
                $update_stmt->bindParam(":password", $new_password_hash);
                
                if($update_stmt->execute()) {
                    return array("success" => true, "message" => "Password changed successfully");
                }
            }
        }
        
        return array("success" => false, "message" => "Current password is incorrect");
    }
    
    // Get all users
    public function getAllUsers() {
        $query = "SELECT user_id, first_name, last_name, user_type, email, department, college, status, created_at 
                  FROM general_users ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle API requests
$database = new Database();
$db = $database->getConnection();
$controller = new UserController($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

switch($method) {
    case 'POST':
        if(end($path_parts) == 'register') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->register($data);
            http_response_code(201);
            echo json_encode($result);
        } elseif(end($path_parts) == 'login') {
            $data = json_decode(file_get_contents("php://input"), true);
            $result = $controller->login($data);
            echo json_encode($result);
        }
        break;
        
    case 'GET':
        if(end($path_parts) == 'staff') {
            $staff = $controller->getStaff();
            echo json_encode($staff);
        } elseif(end($path_parts) == 'staff-locations') {
            $locations = $controller->getStaffLocations();
            echo json_encode($locations);
        } elseif(end($path_parts) == 'users') {
            $users = $controller->getAllUsers();
            echo json_encode($users);
        } elseif(is_numeric(end($path_parts))) {
            if(isset($_GET['location'])) {
                $location = $controller->getLocation(end($path_parts));
                echo json_encode($location);
            } else {
                $user = $controller->getById(end($path_parts));
                if($user) {
                    echo json_encode($user);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "User not found"));
                }
            }
        }
        break;
        
    case 'PUT':
        if(is_numeric(end($path_parts))) {
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(isset($data['latitude']) && isset($data['longitude'])) {
                $result = $controller->updateLocation(end($path_parts), $data['latitude'], $data['longitude']);
            } elseif(isset($data['first_name'])) {
                $result = $controller->updateProfile(end($path_parts), $data);
            } elseif(isset($data['current_password']) && isset($data['new_password'])) {
                $result = $controller->changePassword(end($path_parts), $data['current_password'], $data['new_password']);
            } else {
                $result = array("success" => false, "message" => "Invalid update data");
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