<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/VehicleRepository.php';

class MapController extends AppController {
    private $vehicleRepository;

    public function __construct() {
        parent::__construct();
        $this->vehicleRepository = new VehicleRepository();
    }

    public function map(){
        $this->authorize();

        $vehicles = $this->vehicleRepository->getAllVehicles();

        $this->render('map',[
            'vehicles'=>$vehicles
        ]);
    }
}