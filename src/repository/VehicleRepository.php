<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/Vehicle.php';


class VehicleRepository extends Repository{

    public function addVehicle(Vehicle $vehicle): int {
        $conn   = $this->database->connect();
        $userId = $_SESSION['user']['id']; 
        

        try {
            $stmt = $conn->prepare('
                INSERT INTO vehicles (user_id, brand, model, reg_number, mileage, vehicle_inspection_expiry, oc_ac_expiry, vin, photo, avg_fuel_consumption, status, current_latitude, current_longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)
            ');
            $stmt->execute([
                $userId,
                $vehicle->getBrand(),
                $vehicle->getModel(),
                $vehicle->getRegNr(),
                $vehicle->getMileage(),
                $vehicle->getInspectionDate(),
                $vehicle->getOCACDate(),
                $vehicle->getVin(),
                $vehicle->getPhoto(),
                $vehicle->getAvgFuelConsumption(),
                $vehicle->getStatus(),
                $vehicle->getCurrentLatitude(),
                $vehicle->getCurrentLongitude(),
            ]);

    
            return (int) $conn->lastInsertId('vehicles_id_seq');

        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new Exception("Vehicle already exists!");
            }
            throw $e;
        }
    }

    public function getVehiclesStats(): array {
        $stmt = $this->database->connect()->prepare("
            SELECT
            COUNT(*)                                AS total_vehicles,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
            SUM(CASE WHEN status = 'on_road'    THEN 1 ELSE 0 END) AS on_the_road,
            SUM(CASE WHEN status = 'in_service' THEN 1 ELSE 0 END) AS in_service,
            SUM(mileage)                            AS total_mileage,
            ROUND(AVG(mileage))                     AS avg_mileage,
            ROUND(SUM(avg_fuel_consumption)::numeric, 1)  AS total_fuel,
            ROUND(AVG(avg_fuel_consumption)::numeric, 1)  AS avg_fuel
            FROM vehicles
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user']['id']]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns tab
    }

    public function getAllVehicles(): array {
        $result = [];

        $stmt = $this->database->connect()->prepare('
            SELECT id, brand, model, reg_number, mileage, vehicle_inspection_expiry,
                oc_ac_expiry, vin, photo, status, avg_fuel_consumption, current_latitude, current_longitude
            FROM vehicles 
            WHERE user_id = ?
        ');

        $stmt->execute([$_SESSION['user']['id']]);
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($vehicles as $vehicle){
            $result[] = new Vehicle(
                $vehicle['brand'],
                $vehicle['model'],
                $vehicle['reg_number'],
                $vehicle['mileage'],
                $vehicle['vehicle_inspection_expiry'],
                $vehicle['oc_ac_expiry'],
                $vehicle['vin'],
                $vehicle['photo'],
                $vehicle['id'],
                $vehicle['status'],
                $vehicle['avg_fuel_consumption'],
                $vehicle['current_latitude'],
                $vehicle['current_longitude']
            );
        }
        return $result;
    }

    public function getVehicleById(int $id): ?Vehicle {
        $stmt = $this->database->connect()->prepare('
            SELECT id, brand, model, reg_number, mileage, vehicle_inspection_expiry,
                oc_ac_expiry, vin, photo, status, current_latitude, current_longitude, avg_fuel_consumption
            FROM vehicles 
            WHERE id = ? AND user_id = ?
        ');
    
        $stmt->execute([$id, $_SESSION['user']['id']]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$vehicle) {
            return null;
        }
    
        return new Vehicle(
            $vehicle['brand'],
            $vehicle['model'],
            $vehicle['reg_number'],
            $vehicle['mileage'],
            $vehicle['vehicle_inspection_expiry'],
            $vehicle['oc_ac_expiry'],
            $vehicle['vin'],
            $vehicle['photo'],
            $vehicle['id'],
            $vehicle['status'],
            $vehicle['avg_fuel_consumption'],
            $vehicle['current_latitude'],
            $vehicle['current_longitude']
        );
    }

    public function deleteVehicleById(int $id): bool {
        $stmt = $this->database->connect()->prepare('
        DELETE FROM vehicles WHERE id = :id'
    );
        return $stmt->execute(['id' => $id]);
    }

    public function updateVehicleDate($vehicleId, $type, $newDate): bool {
        try {
            $stmt = $this->database->connect()->prepare('
                UPDATE vehicles SET ' . $type . ' = ? WHERE id = ?
            ');
            $stmt->execute([$newDate, $vehicleId]);
    
            return $stmt->rowCount() > 0; 
        } catch (PDOException $e) {
            throw new Exception("Error updating date: " . $e->getMessage());
        }
    }

    public function assignDriver(int $vehicleId, int $driverId): bool {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO driver_vehicle_assignments (vehicle_id, driver_id)
            VALUES (:vehicle, :driver)
        ');
        return $stmt->execute([
        'vehicle' => $vehicleId,
        'driver'  => $driverId,
        ]);
    }

    public function updateVehicleStatus(int $id, string $status): bool {
        $stmt = $this->database->connect()->prepare('
        UPDATE vehicles SET status = ? WHERE id = ?
        ');
        return $stmt->execute([$status, $id]);
    } 


    public function getAssignedDriverForVehicle(int $vehicleId): ?Driver
    {
        $sql = "
        SELECT d.id, d.name, d.surname, d.phone, d.email, d.license_expiry,
                d.medical_exam_expiry, d.driver_status, d.photo
        FROM driver_vehicle_assignments a
        JOIN drivers d ON d.id = a.driver_id
        WHERE a.vehicle_id = :vid
        ORDER BY a.assignment_date DESC
        LIMIT 1
        ";
        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute(['vid' => $vehicleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Driver(
            id:   (int)$row['id'],
            name: $row['name'],
            surname: $row['surname'],
            phone: $row['phone'],
            email: $row['email'],
            license_expiry: $row['license_expiry'],
            medical_exam_expiry: $row['medical_exam_expiry'],
            driver_status: $row['driver_status'],
            photo: $row['photo'],
        );
    }

    public function getAssignedDriverId(int $vehicleId): ?int
    {
        $sql = "
          SELECT driver_id
          FROM driver_vehicle_assignments
          WHERE vehicle_id = :vehicle_id
          ORDER BY assignment_date DESC
          LIMIT 1
        ";
        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute(['vehicle_id' => $vehicleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['driver_id'] : null;
    }

}