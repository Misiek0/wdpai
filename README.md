AutoFlow

AutoFlow is a PHP web application for managing a fleet of vehicles and drivers, running in Docker containers. It allows user registration and login, browsing and editing vehicle and driver data, displaying the fleet on a map, and handling notifications about expiring documents.

Table of Contents

1. Requirements

2. Installation

3. Usage

4. Directory Structure

5. Database Schema

------------------------------------------------------------------------------------------------------

1. Requirements

  * Docker ≥ 20.x and Docker Compose ≥ 1.29.x

  * (Optional) psql or pgAdmin 4 for database browsing

  * A browser that supports Leaflet JS (OpenStreetMap maps)

2. Installation

      1. Clone the repository
      git clone https://…/wdpai.git
      cd wdpai

      2. Configure the database connection – edit config.php: //fill ''  with actual data
      define('USERNAME', '');
      define('PASSWORD', '');
      define('HOST', '');
      define('DATABASE', '');

      3. Create the PostgreSQL tables

      * Start only the database service:
      docker-compose up -d db

      * Run schema.sql:

        * In pgAdmin 4: open an SQL window, paste the contents of schema.sql, and execute.

        * Or locally in the terminal:
        psql -h localhost -p 5433 -U docker -d db -f schema.sql

      4. Run the entire project
      docker-compose up --build -d
      After a moment, the application will be available at http://localhost:8080

3. Usage

    1. Navigate to http://localhost:8080/register – fill out the registration form.

    2. Log in at http://localhost:8080/login.

    3. After logging in, access:

      * Dashboard – overview of fleet statistics and notifications.

      * Vehicles – add/edit/remove vehicles (the form validates VIN, image file, inspection dates, and insurance expiration).

      * Drivers – add/edit/remove drivers (validates phone number, email, license and medical exam dates).

      * Map – displays all vehicle locations on an interactive Leaflet map.

      * Use the “Log out” button in the header to sign out.

    Notifications (about adding/deleting records and upcoming document expirations) are fetched via AJAX from /api/notifications and marked as read through /markAsRead.

    At the time of adding an object (vehicle or driver) there is a 70% chance of ‘available’ status and 30% of ‘in service/on leave’ (if yes, end of adding logic), if an object is added with ‘available’ status, it checks if there is an opposite object with the same status, if yes – both objects are assigned to each other and their status changes to ‘on_road’. At the time of deleting one of these objects – it disappears and the object assigned to it changes its status from ‘on_road’ to ‘available’.

4. Directory Structure

    ├── docker/  
    │   ├── nginx/Dockerfile  
    │   ├── php/Dockerfile  
    │   └── db/Dockerfile  
    │
    ├── public/                       # Web server root
    │   ├── views/                    # PHP views (dashboard, vehicles, drivers, map, login, etc.)
    │   ├── styles/                   # CSS files  
    │   ├── scripts/                  # JavaScript files (Leaflet, AJAX, form validation)  
    │   ├── images/                   # Icons, logos, markers  
    │   ├── uploads/                  # Uploaded vehicle/driver photos  
    │   └── index.php                 # Entry point (Routing)  
    │
    ├── src/                          # Application source code
    │   ├── controllers/              # Controllers: DashboardController, VehicleController, DriverController, SecurityController, MapController, NotificationController, DefaultController  
    │   ├── models/                   # Models: User, Driver, Vehicle, Notification  
    │   ├── repository/               # Repositories: UserRepository, DriverRepository, VehicleRepository, NotificationRepository  
    │   ├── service/                  # Services: Validator, StatusGenerator, FuelGenerator, LocationGenerator  
    │   └── Routing.php               # Route definitions and dispatcher  
    │
    ├── config.php                    # Database config (USERNAME, PASSWORD, HOST, DATABASE)  
    ├── Database.php                  # PostgreSQL connection class (PDO)  
    ├── docker-compose.yml            # Service definitions: web (nginx + PHP), php, db (PostgreSQL), pgAdmin4  
    ├── schema.sql                    # Creates tables and ENUM types  
    └── README.md                     # This file  

5. Database Schema
The schema.sql file creates the following tables and ENUM types:

CREATE TABLE users (
id SERIAL PRIMARY KEY,
name VARCHAR(100) NOT NULL,
surname VARCHAR(100) NOT NULL,
email VARCHAR(255) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL
);

CREATE TYPE driver_status AS ENUM('available','on_road','on_leave');

CREATE TABLE drivers (
id SERIAL PRIMARY KEY,
user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
name VARCHAR(100) NOT NULL,
surname VARCHAR(100) NOT NULL,
phone VARCHAR(12),
email VARCHAR(255),
license_expiry DATE,
medical_exam_expiry DATE,
driver_status driver_status NOT NULL DEFAULT 'available',
photo VARCHAR(255)
);

CREATE TYPE vehicle_status AS ENUM('available','on_road','in_service');

CREATE TABLE vehicles (
id SERIAL PRIMARY KEY,
user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
brand VARCHAR(50) NOT NULL,
model VARCHAR(50) NOT NULL,
reg_number VARCHAR(9) NOT NULL UNIQUE,
mileage INTEGER CHECK(mileage >= 0),
vehicle_inspection_expiry DATE NOT NULL,
oc_ac_expiry DATE NOT NULL,
vin VARCHAR(17) UNIQUE CHECK(LENGTH(vin) = 17) NOT NULL,
avg_fuel_consumption FLOAT CHECK(avg_fuel_consumption > 0),
status vehicle_status NOT NULL DEFAULT 'available',
current_latitude DECIMAL(10,8) CHECK(current_latitude BETWEEN -90 AND 90),
current_longitude DECIMAL(11,8) CHECK(current_longitude BETWEEN -180 AND 180),
last_location_update TIMESTAMPTZ NOT NULL DEFAULT timezone('Europe/Warsaw', now()),
photo VARCHAR(255)
);

CREATE TABLE driver_vehicle_assignments (
id SERIAL PRIMARY KEY,
driver_id INTEGER REFERENCES drivers(id) ON DELETE CASCADE,
vehicle_id INTEGER REFERENCES vehicles(id) ON DELETE CASCADE,
assignment_date TIMESTAMPTZ NOT NULL DEFAULT timezone('Europe/Warsaw', now())
);

CREATE TABLE notifications (
id SERIAL PRIMARY KEY,
user_id INTEGER REFERENCES users(id) NOT NULL,
message TEXT NOT NULL,
is_read BOOLEAN DEFAULT FALSE,
created_at TIMESTAMPTZ NOT NULL DEFAULT timezone('Europe/Warsaw', now())
);










