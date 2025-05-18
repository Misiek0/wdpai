<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/VehicleRepository.php';

class DashboardController extends AppController {
    private $vehicleRepository;

    public function __construct() {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
    }

    public function dashboard()
    {
        $this->authorize();

        $stats = $this->vehicleRepository->getVehiclesStats();
        $vehicles = $this->vehicleRepository->getAllVehicles();

        $this->render('dashboard',[
            'stats'=> $stats,
            'vehicles'=>$vehicles
        ]);
    }
}