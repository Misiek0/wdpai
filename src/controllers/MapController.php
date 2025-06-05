<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/VehicleRepository.php';

class MapController extends AppController {
    private VehicleRepository $vehicleRepository;

    public function __construct() {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
    }

    public function map(){
        $this->authorize();

        $vehiclesRaw = $this->vehicleRepository->getAllVehicles();
        $vehicles = [];

        foreach ($vehiclesRaw as $vehicle) {
            $assignedDriver = $this->vehicleRepository->getAssignedDriverForVehicle($vehicle->getId());

            $vehicles[] = [
                'id' => $vehicle->getId(),
                'current_latitude' => $vehicle->getCurrentLatitude(),
                'current_longitude' => $vehicle->getCurrentLongitude(),
                'reg_number' => $vehicle->getRegNr(),
                'brand' => $vehicle->getBrand(),
                'model' => $vehicle->getModel(),
                'assigned_driver' => $assignedDriver ? [
                    'name' => $assignedDriver->getName(),
                    'surname' => $assignedDriver->getSurname()
                ] : null
            ];
        }

        $this->render('map',[
            'vehicles'=>$vehicles
        ]);
    }
}