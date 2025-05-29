<?php

require_once 'src/controllers/DefaultController.php';
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/VehicleController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/MapController.php';
require_once 'src/controllers/DriverController.php';

class Routing {
    public static $routes = [];

    public static function get(string $url, string $view): void {
        self::$routes['GET'][$url] = $view;
    }

    public static function post(string $url, string $view): void {
        self::$routes['POST'][$url] = $view;
    }

    public static function run(string $url): void {
        // Remove query string and fragment
        $url = strtok($url, '?#');
        
        // Determine action key
        if (strpos($url, 'api/') === 0) {
            // e.g. '/api/drivers' or 'api/drivers'
            $parts = explode('/', ltrim($url, '/'));
            $action = implode('/', array_slice($parts, 0, 2));
        } else {
            $parts = explode('/', trim($url, '/'));
            $action = $parts[0] ?: 'index';
        }
    
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (!isset(self::$routes[$method][$action])) {
            http_response_code(404);
            die("Endpoint not found: [$method] $action");
        }
    
        $controllerClass = self::$routes[$method][$action];
        $controller = new $controllerClass();
        
        // Handle API endpoints specially
        if ($method === 'GET' && $action === 'api/vehicles') {
            $controller->getVehicleData();
            return;
        }
        if ($method === 'POST' && $action === 'api/vehicles') {
            $controller->addVehicle();
            return;
        }
        if ($method === 'GET' && $action === 'api/drivers') {
            $controller->getDriverData();
            return;
        }
        if ($method === 'POST' && $action === 'api/drivers') {
            // If you have a method for creating/updating drivers, call it here
            if (method_exists($controller, 'addDriver')) {
                $controller->addDriver();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            return;
        }

        // Standard page/controller actions
        $methodName = $action ?: 'index';
        if (method_exists($controller, $methodName)) {
            $controller->$methodName();
        } else {
            http_response_code(404);
            die("Method not found: $methodName");
        }
    }
}