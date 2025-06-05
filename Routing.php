<?php

require_once 'src/controllers/DefaultController.php';
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/VehicleController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/MapController.php';
require_once 'src/controllers/DriverController.php';
require_once 'src/controllers/NotificationController.php';

class Routing {
    public static $routes = [];

    public static function get(string $url, string $controller): void {
        self::$routes['GET'][$url] = $controller;
    }

    public static function post(string $url, string $controller): void {
        self::$routes['POST'][$url] = $controller;
    }

    public static function run(string $url): void {
        $url = strtok($url, '?#');
        $method = $_SERVER['REQUEST_METHOD'];

        $parts = explode('/', trim($url, '/'));
        $action = $parts[0] ?: 'index';

        // for endpoint type /api/xyz
        if ($action === 'api' && isset($parts[1])) {
            $endpoint = $parts[1]; // np. 'vehicles'
            $action = "api/$endpoint";
        }

        if (!isset(self::$routes[$method][$action])) {
            http_response_code(404);
            die("Endpoint not found: [$method] $action");
        }

        $controllerClass = self::$routes[$method][$action];
        $controller = new $controllerClass();

        // default method = action name
        $methodName = match([$method, $action]) {
            ['GET', 'api/vehicles']        => 'getVehicleData',
            ['GET', 'api/drivers']         => 'getDriverData',
            ['GET', 'api/notifications']   => 'getNotifications',
            ['POST', 'api/notifications']  => 'markAsRead',
            default                        => $action
        };

        if (method_exists($controller, $methodName)) {
            $controller->$methodName();
        } else {
            http_response_code(404);
            die("Method not found: $methodName");
        }
    }
}
