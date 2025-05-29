<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/Driver.php';


class DriverRepository extends Repository{
    public function addDriver(Driver $driver): int {
        $conn   = $this->database->connect();
        $userId = $_SESSION['user']['id']; 
        
        try {
            $stmt = $conn->prepare('
                INSERT INTO drivers (user_id, name, surname, phone, email, license_expiry, medical_exam_expiry, driver_status, photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                $driver->getName(),
                $driver->getSurname(),
                $driver->getPhone(),
                $driver->getEmail(),
                $driver->getLicenseExpiry(),
                $driver->getMedicalExamExpiry(),
                $driver->getDriverStatus(),
                $driver->getPhoto(),
            ]);

    
            return (int) $conn->lastInsertId('drivers_id_seq');

        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new Exception("Driver already exists!");
            }
            throw $e;
        }
    }

    public function getDriversStats(): array {
        $stmt = $this->database->connect()->prepare("
            SELECT
            COUNT(*)                                AS total_drivers,
            SUM(CASE WHEN driver_status = 'available' THEN 1 ELSE 0 END) AS available,
            SUM(CASE WHEN driver_status = 'on_road'    THEN 1 ELSE 0 END) AS on_the_road,
            SUM(CASE WHEN driver_status = 'on_leave' THEN 1 ELSE 0 END) AS on_leave
            FROM drivers
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user']['id']]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns tab
    }

    public function getAllDrivers(): array {
        $result = [];

        $stmt = $this->database->connect()->prepare('
            SELECT id, name, surname, phone, email, license_expiry,
                medical_exam_expiry, driver_status, photo
            FROM drivers 
            WHERE user_id = ?
        ');

        $stmt->execute([$_SESSION['user']['id']]);
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($drivers as $driver){
            $result[] = new Driver(
                $driver['id'],
                $driver['name'],
                $driver['surname'],
                $driver['phone'],
                $driver['email'],
                $driver['license_expiry'],
                $driver['medical_exam_expiry'],
                $driver['driver_status'],
                $driver['photo']
            );
        }
        return $result;
    }

    public function getDriverById(int $id): ?Driver {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, surname, phone, email, license_expiry,
                medical_exam_expiry, driver_status, photo
            FROM drivers 
            WHERE id = ? AND user_id = ?
        ');
    
        $stmt->execute([$id, $_SESSION['user']['id']]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$driver) {
            return null;
        }
    
        return new Driver(
            $driver['id'],
            $driver['name'],
            $driver['surname'],
            $driver['phone'],
            $driver['email'],
            $driver['license_expiry'],
            $driver['medical_exam_expiry'],
            $driver['driver_status'],
            $driver['photo']
        );
    }

    public function deleteDriverById(int $id): bool {
        $stmt = $this->database->connect()->prepare('
        DELETE FROM drivers WHERE id = :id'
        );
        return $stmt->execute(['id' => $id]);
    }

    public function updateDriverDate($driverId, $type, $newDate): bool {
        try {
            $stmt = $this->database->connect()->prepare('
                UPDATE drivers SET ' . $type . ' = ? WHERE id = ?
            ');
            $stmt->execute([$newDate, $driverId]);
    
            return $stmt->rowCount() > 0; 
        } catch (PDOException $e) {
            throw new Exception("Error updating date: " . $e->getMessage());
        }
    }


    public function assignVehicle(int $driverId, int $vehicleId): bool {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO driver_vehicle_assignments (driver_id, vehicle_id)
            VALUES (:driver, :vehicle)
        ');
        return $stmt->execute([
        'driver'  => $driverId,
        'vehicle' => $vehicleId,
        ]);
    }

    public function updateDriverStatus(int $id, string $status): bool {
        $stmt = $this->database->connect()->prepare('
        UPDATE drivers SET driver_status = ? WHERE id = ?
        ');
        return $stmt->execute([$status, $id]);
    }

    public function getAssignedVehicleForDriver(int $driverId): ?Vehicle
    {
        $sql = "
        SELECT v.id, v.brand, v.model, v.reg_number, v.mileage,
                v.vehicle_inspection_expiry, v.oc_ac_expiry, v.vin,
                v.photo, v.status, v.avg_fuel_consumption,
                v.current_latitude, v.current_longitude
        FROM driver_vehicle_assignments a
        JOIN vehicles v     ON v.id = a.vehicle_id
        WHERE a.driver_id = :did
        ORDER BY a.assignment_date DESC
        LIMIT 1
        ";
        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute(['did' => $driverId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Vehicle(
            brand: $row['brand'],
            model: $row['model'],
            reg_number: $row['reg_number'],
            mileage: (int)$row['mileage'],
            vehicle_inspection_expiry: $row['vehicle_inspection_expiry'],
            oc_ac_expiry: $row['oc_ac_expiry'],
            vin: $row['vin'],
            photo: $row['photo'],
            id: $row['id'],
            status: $row['status'],
            avg_fuel_consumption: (float)$row['avg_fuel_consumption'],
            current_latitude: isset($row['current_latitude']) ? (float)$row['current_latitude'] : null,
            current_longitude: isset($row['current_longitude']) ? (float)$row['current_longitude'] : null
        );
    }

    public function getAssignedVehicleId(int $driverId): ?int
    {
        $sql = "
          SELECT vehicle_id
          FROM driver_vehicle_assignments
          WHERE driver_id = :driver_id
          ORDER BY assignment_date DESC
          LIMIT 1
        ";
        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute(['driver_id' => $driverId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['vehicle_id'] : null;
    }
}
