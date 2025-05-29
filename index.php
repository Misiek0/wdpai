<?php
session_start();

require 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::get('index', 'DefaultController');
Routing::get('reports','DefaultController');
Routing::get('','DefaultController');

Routing::get('login', 'SecurityController');
Routing::post('login', 'SecurityController');
Routing::get('register', 'SecurityController');
Routing::post('register','SecurityController');
Routing::get('logout','SecurityController');

Routing::get('dashboard', 'DashboardController');

Routing::get('vehicles','VehicleController');
Routing::post('addVehicle','VehicleController');
Routing::post('deleteVehicle', 'VehicleController');
Routing::get('api/vehicles', 'VehicleController');
Routing::post('updateVehicleDate','VehicleController');
Routing::post('updateVehicleStatus','VehicleController');


Routing::get('drivers','DriverController');
Routing::post('addDriver','DriverController');
Routing::post('deleteDriver', 'DriverController');
Routing::get('api/drivers', 'DriverController');
Routing::post('updateDriverDate','DriverController');
Routing::post('updateDriverStatus','DriverController');

Routing::get('map','MapController');




Routing::run($path);