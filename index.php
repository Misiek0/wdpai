<?php
session_start();

require 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::get('index', 'DefaultController');
Routing::get('dashboard', 'DefaultController');

Routing::get('drivers','DefaultController');
Routing::get('map','DefaultController');
Routing::get('reports','DefaultController');
Routing::get('','DefaultController');

Routing::get('login', 'SecurityController');
Routing::post('login', 'SecurityController');

Routing::get('register', 'SecurityController');
Routing::post('register','SecurityController');

Routing::get('vehicles','VehicleController');
Routing::post('addVehicle','VehicleController');
Routing::post('deleteVehicle', 'VehicleController');
Routing::get('api/vehicles', 'VehicleController');
Routing::post('updateVehicleDate','VehicleController');



Routing::get('logout','SecurityController');




Routing::run($path);