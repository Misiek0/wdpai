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
    status driver_status NOT NULL DEFAULT 'available'
);

CREATE TYPE vehicle_status AS ENUM ('available', 'on_road', 'in_service'); 

CREATE TABLE vehicles(
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    reg_number VARCHAR(20) NOT NULL UNIQUE,
    mileage INTEGER CHECK(mileage >= 0), --in km
    vehicle_inspection_expiry DATE,
    oc_ac_expiry DATE,
    vin VARCHAR(17) UNIQUE CHECK (LENGTH(vin) = 17 ),
    avg_fuel_consumption FLOAT CHECK (avg_fuel_consumption > 0),
    status vehicle_status NOT NULL DEFAULT 'available',
    current_latitude DECIMAL(10, 8) CHECK (current_latitude BETWEEN -90 AND 90),
    current_longitude DECIMAL(11, 8) CHECK (current_longitude BETWEEN -180 AND 180),
    last_location_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicle_location_history(
    id SERIAL PRIMARY KEY,
    vehicle_id INTEGER REFERENCES vehicles(id) ON DELETE CASCADE,
    latitude DECIMAL(10, 8) NOT NULL CHECK (latitude BETWEEN -90 AND 90),
    longitude DECIMAL(11, 8) NOT NULL CHECK (longitude BETWEEN -180 AND 180),
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

);

CREATE TABLE driver_vehicle_assignments (
    driver_id INTEGER REFERENCES drivers(id),
    vehicle_id INTEGER REFERENCES vehicles(id),
    assignment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (driver_id, vehicle_id)
);

CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vehicle_services (
    id SERIAL PRIMARY KEY,
    vehicle_id INTEGER NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    service_date DATE NOT NULL CHECK (service_date <= CURRENT_DATE),
    next_service_date DATE CHECK (next_service_date >= service_date), 
    service_type VARCHAR(50) NOT NULL,
    mileage INTEGER CHECK (mileage >= 0), 
    cost DECIMAL(10,2) CHECK (cost >= 0),
    description TEXT,
    service_provider VARCHAR(100),
    invoice_number VARCHAR(50), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

