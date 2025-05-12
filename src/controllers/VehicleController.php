<?php

require_once 'AppController.php';
require_once __DIR__.'/../models/Vehicle.php';
require_once __DIR__.'/../repository/VehicleRepository.php';


class VehicleController extends AppController{

    const MAX_FILE_SIZE = 1024*1024;
    const SUPPORTED_TYPES = ['image/png','image/jpeg','image/pjpeg'];
    const UPLOAD_DIRECTORY = '/../public/uploads/';

    private $messages = [];
    private $vehicleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
        
    }

    public function vehicles(){
        $this->authorize();

        $stats = $this->vehicleRepository->getVehiclesStats();
        $vehicles = $this->vehicleRepository->getAllVehicles();

        $this->render('vehicles', [
            'stats' => $stats,
            'vehicles' => $vehicles
        ]);
    }

    function generateRandomFuelConsumption() {
        $min = 5.0;
        $max = 13.0;
        $randomFuelConsumption = mt_rand() / mt_getrandmax() * ($max - $min) + $min;
        return round($randomFuelConsumption, 1);
    }

    function generateRandomStatus(): string {
        $statuses = ['available', 'on_road', 'in_service'];
        return $statuses[array_rand($statuses)];
    }
    
    function generateRandomCoordinatesInPoland(): array {
        $minLat = 49.0;
        $maxLat = 54.0;
        $minLon = 14.1;
        $maxLon = 23.0;
    
        $lat = mt_rand() / mt_getrandmax() * ($maxLat - $minLat) + $minLat;
        $lon = mt_rand() / mt_getrandmax() * ($maxLon - $minLon) + $minLon;
    
        return [round($lat, 6), round($lon, 6)];
    }

    public function addVehicle(){
        if($this->isPost() && is_uploaded_file($_FILES['file']['tmp_name']) && $this->validate($_FILES['file'])){
            move_uploaded_file(
                $_FILES['file']['tmp_name'],
                dirname(__DIR__).self::UPLOAD_DIRECTORY.$_FILES['file']['name']
            );
            
            
            $brand = $this->normalizeString($_POST['brand'] ?? '');
            $model = $this->normalizeString($_POST['model'] ?? '');
            $reg_number = $this->normalizeRegistration($_POST['reg_number'] ?? '');
            $vin = $this->validateVIN($_POST['vin'] ?? '');
            
            if (empty($brand) || empty($model) || !$reg_number || !$vin) {
                $this->messages[] = 'Invalid vehicle data';
                $vehicles = $this->vehicleRepository->getAllVehicles();
                $stats = $this->vehicleRepository->getVehiclesStats();

                return $this->render('vehicles', [
                    'messages' => $this->messages,
                    'vehicles' => $vehicles,
                    'stats' => $stats
                ]);
            }
            
            $avg_fuel_consumption = $this->generateRandomFuelConsumption(); 
            [$lat, $lon] = $this->generateRandomCoordinatesInPoland();

            $vehicle = new Vehicle(
                brand: $brand,
                model: $model,
                reg_number: $reg_number,
                mileage: (int)$_POST['mileage'],
                vehicle_inspection_expiry: $_POST['vehicle_inspection_expiry'],
                oc_ac_expiry: $_POST['oc_ac_expiry'],
                vin: $vin,
                photo: $_FILES['file']['name'],
                id: null,
                status: $this->generateRandomStatus(),
                avg_fuel_consumption: $avg_fuel_consumption,
                current_latitude: $lat,
                current_longitude: $lon
            );
            $userId = $_SESSION['user']['id'];
            
            $this->vehicleRepository->addVehicle($vehicle);
            $this->messages[] = "Vehicle added successfully!";

        };
        $vehicles = $this->vehicleRepository->getAllVehicles();
        $stats = $this->vehicleRepository->getVehiclesStats();

        return $this->render('vehicles', [
            'messages' => $this->messages,
            'vehicles' => $vehicles,
            'stats' => $stats
        ]);
    }

    private function normalizeString(string $input): string {
        return ucfirst(strtolower(trim($input))); 
    }
    
    private function normalizeRegistration(string $input): ?string {
        $normalized = strtoupper(preg_replace('/\s+/', '', $input)); 
        return preg_match('/^[A-Z]{2,3}[0-9A-Z]{3,5}$/', $normalized) ? $normalized : null;
    }
    private function validateVIN(string $vin): ?string {
        return (strlen($vin) === 17 && ctype_alnum($vin)) ? strtoupper($vin) : null;
    }

    private function validate(array $file): bool
    {
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->messages[] = 'File is too large for destination file system.';
            return false;
        }

        if (!isset($file['type']) || !in_array($file['type'], self::SUPPORTED_TYPES)) {
            $this->messages[] = 'File type is not supported.';
            return false;
        }
        return true;
    }

    public function api_vehicles() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getVehicleData();
        } 
    }

    public function getVehicleData() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Vehicle ID is required']);
            exit;
        }
        
        $vehicleId = (int)$_GET['id'];
        $vehicle = $this->vehicleRepository->getVehicleById($vehicleId);
        
        if (!$vehicle) {
            http_response_code(404);
            echo json_encode(['error' => 'Vehicle not found']);
            exit;
        }
        
        $response = [
            'data' => [
                'id' => $vehicle->getId(),
                'brand' => $vehicle->getBrand(),
                'model' => $vehicle->getModel(),
                'reg_number' => $vehicle->getRegNr(),
                'mileage' => $vehicle->getMileage(),
                'status' => $vehicle->getStatus(),
                'vin' => $vehicle->getVin(),
                'vehicle_inspection_expiry' => $vehicle->getInspectionDate(),
                'oc_ac_expiry'=>$vehicle->getOCACDate(),
                'photo'=>$vehicle->getPhoto(),
                'avg_fuel_consumption' => $vehicle->getAvgFuelConsumption() ?? null,
                'current_latitude'=>$vehicle->getCurrentLatitude(),
                'current_longitude'=>$vehicle->getCurrentLongitude()

            ]
        ];
        
        echo json_encode($response);
    }

    public function deleteVehicle() { 
        if ($this->isPost()){
            $input = json_decode(file_get_contents('php://input'), true);
            $vehicleId = $input['id'] ?? null;
    
            if (!$vehicleId) {
                http_response_code(400);
                echo json_encode(['error' => 'Vehicle ID is required']);
                return;
            }
    
            $deleted = $this->vehicleRepository->deleteVehicleById((int)$vehicleId);
            if ($deleted) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Vehicle not found or could not be deleted']);
            }
        }
    }
    
    public function updateVehicleDate() {
        if ($this->isPost()) {
            $input = json_decode(file_get_contents('php://input'), true);
            $vehicleId = $input['vehicleId'] ?? null;
            $newDate = $input['newDate'] ?? null;
            $type = $input['type'] ?? null;
            
            if (!$vehicleId || !$newDate || !$type) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid data']);
                return;
            }
            
            // date type validation
            if ($type !== 'vehicle_inspection_expiry' && $type !== 'oc_ac_expiry') {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date type']);
                return;
            }
    
            // date update pdo
            $success = $this->vehicleRepository->updateVehicleDate($vehicleId, $type, $newDate);
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update date']);
            }
        }
    }
    

}