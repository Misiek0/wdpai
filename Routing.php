<?php

require_once 'src/controllers/DefaultController.php';
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/VehicleController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/MapController.php';

class Routing {
    public static $routes;

    public static function get($url, $view) {
        self::$routes['GET'][$url] = $view;
    }

    public static function post($url, $view) {
        self::$routes['POST'][$url] = $view;
    }

    public static function run($url) {
        // del params?#
        $url = strtok($url, '?#');
        
        // check if API request
        if (strpos($url, 'api/') === 0) {
            $action = 'api/' . explode('/', $url)[1]; // np. 'api/vehicles'
        } else {
            $action = explode('/', $url)[0] ?: 'index';
        }
    
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!isset(self::$routes[$method][$action])) {
            http_response_code(404);
            die("Endpoint not found");
        }
    
        $controllerClass = self::$routes[$method][$action];
        $controller = new $controllerClass();
        
        if ($action === 'api/vehicles') {
            // special API
            if ($method === 'GET') {
                $controller->getVehicleData();
            } elseif ($method === 'POST') {
                $controller->addVehicle();
            }
        } else {
            // Standard
            $methodName = $action ?: 'index';
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            }
        }
    }
}