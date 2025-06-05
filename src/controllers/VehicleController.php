<?php

require_once 'AppController.php';
require_once __DIR__.'/../models/Vehicle.php';
require_once __DIR__.'/../repository/VehicleRepository.php';
require_once __DIR__.'/../service/FuelGenerator.php';
require_once __DIR__.'/../service/LocationGenerator.php';
require_once __DIR__.'/../service/StatusGenerator.php';
require_once __DIR__.'/../service/Validator.php';
require_once __DIR__.'/../repository/NotificationRepository.php';
require_once __DIR__.'/../models/Notification.php';


class VehicleController extends AppController{

    const UPLOAD_DIRECTORY = '/../public/uploads/';
    
    private $messages = [];
    private VehicleRepository $vehicleRepository;
    private DriverRepository $driverRepository;
    private FuelGenerator $fuelGenerator;
    private LocationGenerator $locationGenerator;
    private StatusGenerator $statusGenerator;
    private Validator $validator;
    private NotificationRepository $notificationRepository;

    public function __construct()
    {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
        $this->driverRepository = new DriverRepository();
        $this->fuelGenerator = new FuelGenerator();
        $this->locationGenerator = new LocationGenerator();
        $this->statusGenerator = new StatusGenerator();
        $this->validator = new Validator();
        $this->notificationRepository = new NotificationRepository();
    }

    public function vehicles(){
        $this->authorize();

        $vehiclesStats = $this->vehicleRepository->getVehiclesStats();
        $vehicles = $this->vehicleRepository->getAllVehicles();

        $this->render('vehicles', [
            'messages' => $this->messages,
            'vehiclesStats' => $vehiclesStats,
            'vehicles' => $vehicles
        ]);
    }

    public function addVehicle(){
        if (
            $this->isPost() &&
            is_uploaded_file($_FILES['file']['tmp_name']) &&
            $this->validator->validate($_FILES['file'], $this->messages)
        ) {

            move_uploaded_file(
                $_FILES['file']['tmp_name'],
                dirname(__DIR__).self::UPLOAD_DIRECTORY.$_FILES['file']['name']
            );
            
            
            $brand = $this->validator->normalizeString($_POST['brand'] ?? '');
            $model =  $this->validator->normalizeString($_POST['model'] ?? '');
            $reg_number =  $this->validator->normalizeRegistration($_POST['reg_number'] ?? '');
            $vin =  $this->validator->validateVIN($_POST['vin'] ?? '');
            
            if (empty($brand) || empty($model) || !$reg_number || !$vin) {
                $this->messages[] = 'Invalid vehicle data';
                $vehicles = $this->vehicleRepository->getAllVehicles();
                $vehiclesStats = $this->vehicleRepository->getVehiclesStats();

                return $this->render('vehicles', [
                    'messages' => $this->messages,
                    'vehicles' => $vehicles,
                    'vehiclesStats' => $vehiclesStats,
                ]);
            }
            
            $avg_fuel_consumption = $this->fuelGenerator->generate();
            [$lat, $lon] = $this->locationGenerator->generate();
            $status = $this->statusGenerator->generateVehicleStatus();

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
                status: $status,
                avg_fuel_consumption: $avg_fuel_consumption,
                current_latitude: $lat,
                current_longitude: $lon
            );
            
            $newVehicleId = $this->vehicleRepository->addVehicle($vehicle);
            
            if($status == 'available'){

                    $allDrivers = $this->driverRepository->getAllDrivers();
                    $available = array_filter($allDrivers, function($d) {
                    return $d->getDriverStatus() === 'available';
                });

                if (count($available) > 0) {

                    $chosen = $available[array_rand($available)];

                    $this->vehicleRepository->assignDriver($newVehicleId, $chosen->getId());
                    $this->vehicleRepository->updateVehicleStatus($newVehicleId, 'on_road');
                    $this->driverRepository->updateDriverStatus($chosen->getId(), 'on_road');
                }
            };
            $this->messages[] = "Vehicle added successfully!";

            $this->notificationRepository->createNotification(
                $_SESSION['user']['id'],
                new Notification("Vehicle id #{$newVehicleId} successfully added")
            );


            header('Location: /vehicles');
            exit;

        };
    }

    public function api_vehicles() {
        if ($this->isGet()) {
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
        $assignedDriver = $this->vehicleRepository->getAssignedDriverForVehicle($vehicleId);
        
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
                'current_longitude'=>$vehicle->getCurrentLongitude(),
                'assigned_driver' => $assignedDriver
                    ? [
                        'id'      => $assignedDriver->getId(),
                        'name'    => $assignedDriver->getName(),
                        'surname' => $assignedDriver->getSurname(),
                    ]
                    : null,
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

            $assignedDriverId = $this->vehicleRepository->getAssignedDriverId($vehicleId);
            if ($assignedDriverId !== null) {
                $this->driverRepository->updateDriverStatus($assignedDriverId, 'available');
            }

            $deleted = $this->vehicleRepository->deleteVehicleById((int)$vehicleId);
            if ($deleted) {
                $this->notificationRepository->createNotification(
                    $_SESSION['user']['id'],
                    new Notification("Vehicle id #{$vehicleId} was deleted")
                );
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