<?php

require_once 'AppController.php';
require_once __DIR__.'/../models/Driver.php';
require_once __DIR__.'/../repository/DriverRepository.php';
require_once __DIR__.'/../repository/VehicleRepository.php';
require_once __DIR__.'/../service/StatusGenerator.php';
require_once __DIR__.'/../service/Validator.php';



class DriverController extends AppController{

    const UPLOAD_DIRECTORY = '/../public/uploads/';

    private $messages = [];
    private $driverRepository;
    private $vehicleRepository;
    private $statusGenerator;
    private $validator;

    public function __construct()
    {
        parent::__construct();
        $this->driverRepository = new DriverRepository();
        $this->vehicleRepository = new VehicleRepository();
        $this->statusGenerator = new StatusGenerator();
        $this->validator = new Validator();
        
    }

    public function drivers(){
        $this->authorize();

        $driversStats = $this->driverRepository->getDriversStats();
        $drivers = $this->driverRepository->getAllDrivers();

        $this->render('drivers', [
            'driversStats' => $driversStats,
            'drivers' => $drivers,
            'messages' => $this->messages,
            'validator' => $this->validator
        ]);
    }

    public function addDriver(){
    
        if (
            $this->isPost() &&
            is_uploaded_file($_FILES['file']['tmp_name']) &&
            $this->validator->validate($_FILES['file'], $this->messages)
        ) {

            move_uploaded_file(
                $_FILES['file']['tmp_name'],
                dirname(__DIR__).self::UPLOAD_DIRECTORY.$_FILES['file']['name']
            );

        $name = $this->validator->normalizeString($_POST['name'] ?? '');
        $surname = $this->validator->normalizeString($_POST['surname'] ?? '');
        $email =  $this->validator->validateEmail($_POST['email'] ?? '');
        $driver_status = $this->statusGenerator->generateDriverStatus();

        $driver = new Driver(
            id: null,
            name: $name,
            surname: $surname,
            phone: $_POST['phone'] ?? '',
            email: $email,
            license_expiry: $_POST['license_expiry'],
            medical_exam_expiry: $_POST['medical_exam_expiry'],
            driver_status: $driver_status,
            photo: $_FILES['file']['name'] ?? 'avatar.jpg'
        );

        $newDriverId = $this->driverRepository->addDriver($driver);

            if($driver_status == 'available'){

                $allVehicles = $this->vehicleRepository->getAllVehicles();
                $available = array_filter($allVehicles, function($v) {
                    return $v->getStatus() === 'available';
                });

                if (count($available) > 0) {

                    $chosen = $available[array_rand($available)];

                    $this->driverRepository->assignVehicle($newDriverId, $chosen->getId());
                    $this->driverRepository->updateDriverStatus($newDriverId, 'on_road');
                    $this->vehicleRepository->updateVehicleStatus($chosen->getId(), 'on_road');
                }
            };
        $this->messages[] = "Driver added successfully!";
        
        header('Location: /drivers');
        exit;
        };

    }


    public function api_drivers() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getDriverData();
        } 
    }

    public function getDriverData() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Driver ID is required']);
            exit;
        }
        
        $driverId = (int)$_GET['id'];
        $driver = $this->driverRepository->getDriverById($driverId);
        $assignedVehicle = $this->driverRepository->getAssignedVehicleForDriver($driverId);
        
        if (!$driver) {
            http_response_code(404);
            echo json_encode(['error' => 'Driver not found']);
            exit;
        }
        
        $response = [
            'data' => [
                'id' => $driver->getId(),
                'name' => $driver->getName(),
                'surname' => $driver->getSurname(),
                'phone' => $driver->getPhone(),
                'email' => $driver->getEmail(),
                'license_expiry' => $driver->getLicenseExpiry(),
                'medical_exam_expiry' => $driver->getMedicalExamExpiry(),
                'driver_status' => $driver->getDriverStatus(),
                'photo' => $driver->getPhoto(),
                'assigned_vehicle' => $assignedVehicle
                    ? [
                        'id'          => $assignedVehicle->getId(),
                        'brand'       => $assignedVehicle->getBrand(),
                        'model'       => $assignedVehicle->getModel(),
                        'reg_number'  => $assignedVehicle->getRegNr(),
                    ]
                    : null,
            ]
        ];
        
        echo json_encode($response);
    }

        public function deleteDriver() { 
        if ($this->isPost()){
            $input = json_decode(file_get_contents('php://input'), true);
            $driverId = $input['id'] ?? null;
    
            if (!$driverId) {
                http_response_code(400);
                echo json_encode(['error' => 'Driver ID is required']);
                return;
            }

            $assignedVehicleId = $this->driverRepository->getAssignedVehicleId($driverId);
            if ($assignedVehicleId !== null) {
                $this->vehicleRepository->updateVehicleStatus($assignedVehicleId, 'available');
            }
    
            $deleted = $this->driverRepository->deleteDriverById((int)$driverId);
            if ($deleted) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Driver not found or could not be deleted']);
            }
        }
    }

    public function updateDriverDate() {
        if ($this->isPost()) {
            $input = json_decode(file_get_contents('php://input'), true);
            $driverId = $input['driverId'] ?? null;
            $newDate = $input['newDate'] ?? null;
            $type = $input['type'] ?? null;
            
            if (!$driverId || !$newDate || !$type) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid data']);
                return;
            }
            
            // date type validation
            if ($type !== 'license_expiry' && $type !== 'medical_exam_expiry') {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date type']);
                return;
            }
    
            // date update pdo
            $success = $this->driverRepository->updateDriverDate($driverId, $type, $newDate);
            
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update date']);
            }
        }
    }
}