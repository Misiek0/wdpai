<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/VehicleRepository.php';
require_once __DIR__.'/../repository/DriverRepository.php';

class DashboardController extends AppController {
    private VehicleRepository $vehicleRepository;
    private DriverRepository $driverRepository;

    public function __construct() {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
        $this->driverRepository = new DriverRepository();
    }

    public function dashboard()
    {
        $this->authorize();

        $vehiclesStats = $this->vehicleRepository->getVehiclesStats();
        $driversStats = $this->driverRepository->getDriversStats();
        $vehicles = $this->vehicleRepository->getAllVehicles();

        $this->render('dashboard',[
            'vehiclesStats'=> $vehiclesStats,
            'driversStats'=> $driversStats,
            'vehicles'=>$vehicles
        ]);
    }
}