# Proteq Mobile App Backend

A PHP and MySQL backend for the Proteq emergency response mobile application, designed to work with the existing database structure.

## Features

- **User Management**: Registration, authentication, and profile management for students, faculty, and staff
- **Incident Management**: Report, track, and manage emergency incidents with validation
- **Emergency Management**: Create and manage emergency situations
- **Alert System**: Broadcast emergency alerts with location-based targeting
- **Evacuation Centers**: Manage emergency shelters and evacuation points with resources
- **Safety Protocols**: Store and retrieve emergency procedures by type
- **Welfare Checks**: Track safety status of users during emergencies
- **Staff Management**: Manage emergency responders and their locations
- **RESTful API**: Complete CRUD operations for all entities

## Database Structure

The backend works with the existing `proteq_db` database which includes:

- **admin**: System administrators
- **general_users**: Students, faculty, and university employees
- **staff**: Emergency responders (nurses, paramedics, security, firefighters)
- **incident_reports**: Emergency incident reports with validation
- **emergencies**: Emergency situations triggered by admins
- **alerts**: Emergency alerts with location-based targeting
- **evacuation_centers**: Emergency shelters with capacity management
- **resources**: Resources available at evacuation centers
- **safety_protocols**: Emergency procedures by type
- **welfare_checks**: User safety status tracking during emergencies
- **user_locations**: Real-time user location tracking
- **staff_locations**: Real-time staff location tracking

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

1. **Import the existing database**:
   ```sql
   mysql -u root -p < proteq_db.sql
   ```

2. **Configure database connection**:
   Edit `config/database.php` and update the database credentials:
   ```php
   private $host = "localhost";
   private $db_name = "proteq_db";
   private $username = "your_username";
   private $password = "your_password";
   ```

3. **Set up web server**:
   - Ensure mod_rewrite is enabled (Apache)
   - Point your web server to the backend directory
   - Make sure the `.htaccess` file is accessible

4. **Test the API**:
   ```bash
   curl http://your-domain.com/api/test.php
   ```

## API Endpoints

### Authentication & Users

#### Register User
```
POST /api/users/register
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "user_type": "STUDENT",
  "password": "secure_password",
  "email": "john@example.com",
  "department": "CICS",
  "college": "BSIT"
}
```

#### Login
```
POST /api/users/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "secure_password"
}
```

#### Get User Profile
```
GET /api/users/{user_id}
```

#### Update User Location
```
PUT /api/users/{user_id}
Content-Type: application/json

{
  "latitude": 14.0771328,
  "longitude": 121.1596800
}
```

#### Get All Staff (Responders)
```
GET /api/users/staff
```

#### Get Staff Locations
```
GET /api/users/staff-locations
```

### Incidents

#### Report Incident
```
POST /api/incidents
Content-Type: application/json

{
  "incident_type": "fire",
  "description": "Fire in building A",
  "longitude": 121.1596800,
  "latitude": 14.0771328,
  "reported_by": 1,
  "priority_level": "high",
  "reporter_safe_status": "safe"
}
```

#### Get All Incidents
```
GET /api/incidents
GET /api/incidents?status=pending
GET /api/incidents?type=fire
GET /api/incidents?validation=unvalidated
```

#### Get Incident Details
```
GET /api/incidents/{incident_id}
```

#### Update Incident Status
```
PUT /api/incidents/{incident_id}
Content-Type: application/json

{
  "status": "in_progress",
  "assigned_to": 1
}
```

#### Update Validation Status
```
PUT /api/incidents/{incident_id}
Content-Type: application/json

{
  "validation_status": "validated",
  "validation_notes": "Confirmed by security"
}
```

### Emergencies

#### Create Emergency
```
POST /api/emergencies
Content-Type: application/json

{
  "emergency_type": "EARTHQUAKE",
  "description": "Major earthquake detected",
  "triggered_by": 1
}
```

#### Get All Emergencies
```
GET /api/emergencies
GET /api/emergencies?active=1
GET /api/emergencies/active
GET /api/emergencies/resolved
```

#### Resolve Emergency
```
PUT /api/emergencies/{emergency_id}
Content-Type: application/json

{
  "resolve": true,
  "resolution_reason": "Situation under control",
  "resolved_by": 1
}
```

### Alerts

#### Create Alert
```
POST /api/alerts
Content-Type: application/json

{
  "alert_type": "typhoon",
  "title": "Typhoon Warning",
  "description": "Typhoon approaching the area",
  "latitude": 14.0771328,
  "longitude": 121.1596800,
  "radius_km": 25.0
}
```

#### Get Nearby Alerts
```
GET /api/alerts?nearby=1&lat=14.0771328&lng=121.1596800&radius=50
```

#### Update Alert Status
```
PUT /api/alerts/{alert_id}
Content-Type: application/json

{
  "status": "resolved"
}
```

### Evacuation Centers

#### Get All Centers
```
GET /api/evacuation-centers
GET /api/evacuation-centers?status=open
```

#### Get Nearby Centers
```
GET /api/evacuation-centers?nearby=1&lat=14.0771328&lng=121.1596800&radius=10
```

#### Update Center Occupancy
```
PUT /api/evacuation-centers/{center_id}
Content-Type: application/json

{
  "occupancy": 150
}
```

#### Get Center Resources
```
GET /api/evacuation-centers/{center_id}/resources
```

#### Update Center Resources
```
PUT /api/evacuation-centers/{center_id}
Content-Type: application/json

{
  "resources": [
    {"type": "food", "quantity": 500},
    {"type": "water", "quantity": 300},
    {"type": "medical", "quantity": 50}
  ]
}
```

### Safety Protocols

#### Get All Protocols
```
GET /api/safety-protocols
GET /api/safety-protocols?type=fire
GET /api/safety-protocols?search=emergency
```

#### Get Protocols by Type
```
GET /api/safety-protocols/fire
GET /api/safety-protocols/earthquake
GET /api/safety-protocols/medical
```

#### Create Protocol
```
POST /api/safety-protocols
Content-Type: application/json

{
  "title": "Fire Emergency Response",
  "description": "Immediate actions during fire emergency",
  "type": "fire",
  "file_attachment": "fire_protocol.pdf",
  "created_by": 1
}
```

### Welfare Checks

#### Create Welfare Check
```
POST /api/welfare-checks
Content-Type: application/json

{
  "user_id": 1,
  "emergency_id": 1,
  "status": "SAFE",
  "remarks": "User confirmed safe"
}
```

#### Get Welfare Checks by Emergency
```
GET /api/welfare-checks/emergency/{emergency_id}
```

#### Get Welfare Checks by User
```
GET /api/welfare-checks/user/{user_id}
```

#### Bulk Create Welfare Checks
```
POST /api/welfare-checks/bulk-create/{emergency_id}
```

#### Get Missing Users
```
GET /api/welfare-checks/missing-users/{emergency_id}
```

## User Types

- **STUDENT**: University students
- **FACULTY**: Teaching staff
- **UNIVERSITY_EMPLOYEE**: Administrative staff
- **admin**: System administrators
- **staff**: Emergency responders (nurse, paramedic, security, firefighter, others)

## Emergency Types

- **FIRE**: Fire emergencies
- **EARTHQUAKE**: Earthquake emergencies
- **TYPHOON**: Typhoon/storm emergencies
- **OTHER**: Other emergency types

## Alert Types

- **typhoon**: Typhoon warnings
- **fire**: Fire alerts
- **flood**: Flood warnings
- **earthquake**: Earthquake alerts
- **other**: Other emergency alerts

## Incident Types

- **fire**: Fire incidents
- **medical**: Medical emergencies
- **accident**: Accidents
- **security**: Security incidents
- **other**: Other incidents

## Status Values

### Incident Status
- **pending**: Awaiting response
- **in_progress**: Being handled
- **resolved**: Completed
- **closed**: Closed

### Validation Status
- **unvalidated**: Not yet verified
- **validated**: Confirmed
- **rejected**: False alarm

### Welfare Check Status
- **SAFE**: User is safe
- **NEEDS_HELP**: User needs assistance
- **NO_RESPONSE**: No response from user

### Evacuation Center Status
- **open**: Available
- **full**: At capacity
- **closed**: Not available

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- CORS headers for cross-origin requests
- Input validation and sanitization
- Protected sensitive files via .htaccess

## Error Handling

The API returns appropriate HTTP status codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 405: Method Not Allowed
- 500: Internal Server Error

## Sample Data

The database includes sample data for testing:
- Admin user (email: admin1@gmail.com, password: hashed)
- Sample students, faculty, and staff accounts
- Example evacuation centers with resources
- Basic safety protocols
- Sample incidents and emergencies

## Development

To add new features:
1. Create the database table(s) if needed
2. Add the controller class in `api/`
3. Update the routing in `.htaccess`
4. Test the endpoints

## Support

For issues or questions, please check the API documentation or contact the development team. 