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

CREATE TYPE vehicle_status AS ENUM ('available', 'on_road', 'in_service'); 

CREATE TABLE vehicles(
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    reg_number VARCHAR(9) NOT NULL UNIQUE,
    mileage INTEGER CHECK(mileage >= 0), --in km
    vehicle_inspection_expiry DATE NOT NULL,
    oc_ac_expiry DATE NOT NULL,
    vin VARCHAR(17) UNIQUE CHECK (LENGTH(vin) = 17 ) NOT NULL,
    avg_fuel_consumption FLOAT CHECK (avg_fuel_consumption > 0),
    status vehicle_status NOT NULL DEFAULT 'available',
    current_latitude DECIMAL(10, 8) CHECK (current_latitude BETWEEN -90 AND 90),
    current_longitude DECIMAL(11, 8) CHECK (current_longitude BETWEEN -180 AND 180),
    last_location_update TIMESTAMPTZ NOT NULL
        DEFAULT timezone('Europe/Warsaw', now()),
    photo VARCHAR(255)
);

--table for location history in future project grow
CREATE TABLE vehicle_location_history(
    id SERIAL PRIMARY KEY,
    vehicle_id INTEGER REFERENCES vehicles(id) ON DELETE CASCADE,
    latitude DECIMAL(10, 8) NOT NULL CHECK (latitude BETWEEN -90 AND 90),
    longitude DECIMAL(11, 8) NOT NULL CHECK (longitude BETWEEN -180 AND 180),
    recorded_at TIMESTAMPTZ NOT NULL
        DEFAULT timezone('Europe/Warsaw', now())

);

CREATE TABLE driver_vehicle_assignments (
  id              SERIAL PRIMARY KEY,
  driver_id       INTEGER REFERENCES drivers(id)  ON DELETE CASCADE,
  vehicle_id      INTEGER REFERENCES vehicles(id) ON DELETE CASCADE,
  assignment_date TIMESTAMPTZ NOT NULL
      DEFAULT timezone('Europe/Warsaw', now())
);

CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL
        DEFAULT timezone('Europe/Warsaw', now())
);


CREATE VIEW vehicles_with_drivers AS
SELECT 
    v.*,
    d.id AS driver_id,
    d.name AS driver_name,
    d.surname AS driver_surname,
    d.phone AS driver_phone,
    d.driver_status
FROM vehicles v
LEFT JOIN (
    SELECT DISTINCT ON (vehicle_id) 
        vehicle_id, driver_id, assignment_date
    FROM driver_vehicle_assignments
    ORDER BY vehicle_id, assignment_date DESC
) a ON a.vehicle_id = v.id
LEFT JOIN drivers d ON d.id = a.driver_id;

CREATE VIEW drivers_with_vehicles AS
SELECT 
    d.*,
    v.id AS vehicle_id,
    v.brand AS vehicle_brand,
    v.model AS vehicle_model,
    v.reg_number AS vehicle_reg_number,
    v.status AS vehicle_status
FROM drivers d
LEFT JOIN (
    SELECT DISTINCT ON (driver_id) 
        driver_id, vehicle_id, assignment_date
    FROM driver_vehicle_assignments
    ORDER BY driver_id, assignment_date DESC
) a ON a.driver_id = d.id
LEFT JOIN vehicles v ON v.id = a.vehicle_id;

CREATE VIEW expiring_documents AS
SELECT 
    'driver_license' AS document_type,
    d.id AS owner_id,
    d.name || ' ' || d.surname AS owner_name,
    d.license_expiry AS expiry_date,
    (d.license_expiry - CURRENT_DATE) AS days_remaining
FROM drivers d
WHERE d.license_expiry BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '30 days'

UNION ALL

SELECT 
    'driver_medical' AS document_type,
    d.id AS owner_id,
    d.name || ' ' || d.surname AS owner_name,
    d.medical_exam_expiry AS expiry_date,
    (d.medical_exam_expiry - CURRENT_DATE) AS days_remaining
FROM drivers d
WHERE d.medical_exam_expiry BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '30 days'

UNION ALL

SELECT 
    'vehicle_inspection' AS document_type,
    v.id AS owner_id,
    v.brand || ' ' || v.model || ' (' || v.reg_number || ')' AS owner_name,
    v.vehicle_inspection_expiry AS expiry_date,
    (v.vehicle_inspection_expiry - CURRENT_DATE) AS days_remaining
FROM vehicles v
WHERE v.vehicle_inspection_expiry BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '30 days'

UNION ALL

SELECT 
    'vehicle_insurance' AS document_type,
    v.id AS owner_id,
    v.brand || ' ' || v.model || ' (' || v.reg_number || ')' AS owner_name,
    v.oc_ac_expiry AS expiry_date,
    (v.oc_ac_expiry - CURRENT_DATE) AS days_remaining
FROM vehicles v
WHERE v.oc_ac_expiry BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '30 days';