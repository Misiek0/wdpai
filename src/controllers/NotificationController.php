<?php

require_once 'AppController.php';
require_once __DIR__.'/../models/Notification.php';
require_once __DIR__.'/../repository/NotificationRepository.php';

class NotificationController extends AppController {

    private NotificationRepository $notificationRepository;

    public function __construct() {
        parent::__construct();
        $this->notificationRepository = new NotificationRepository();
    }

       public function api_notifications() {
        if ($this->isGet()) {
            $this->getNotifications();
        } 
    }

    public function getNotifications(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user']['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }

        $notifications = $this->notificationRepository->getNotificationsForUser($_SESSION['user']['id']);

        $data = array_map(fn($n) => [
            'id' => $n->getId(),
            'message' => $n->getMessage(),
            'is_read' => $n->isRead(),
            'created_at' => $n->getCreatedAt()
        ], $notifications);

        echo json_encode($data);
    }

    public function markAsRead(): void {
        if (!$this->isPost()) {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Only POST allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = $input['id'] ?? null;

        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing notification ID']);
            return;
        }

        $this->notificationRepository->markNotificationAsRead((int)$notificationId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function checkExpiringDriverDocs(bool $return = false): void {
        if (!isset($_SESSION['user']['id'])) {
            if ($return) return;
            http_response_code(403);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $expiringDrivers = $this->notificationRepository->getDriversWithExpiringDocuments($userId);

        foreach ($expiringDrivers as $driver) {
            if (!empty($driver['license_expiry']) && $driver['license_expiry'] <= date('Y-m-d', strtotime('+30 days'))) {
                $this->notificationRepository->createNotification($userId,
                    new Notification("Driver #{$driver['id']} license is expiring on {$driver['license_expiry']}"));
            }
            if (!empty($driver['medical_exam_expiry']) && $driver['medical_exam_expiry'] <= date('Y-m-d', strtotime('+30 days'))) {
                $this->notificationRepository->createNotification($userId,
                    new Notification("Driver #{$driver['id']} medical exam expires on {$driver['medical_exam_expiry']}"));
            }
        }

        if (!$return) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'checked' => count($expiringDrivers)]);
        }
    }


    public function checkExpiringVehicleDocs(bool $return = false): void {
        if (!isset($_SESSION['user']['id'])) {
            if ($return) return;
            http_response_code(403);
            echo json_encode(['error' => 'User not logged in']);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $expiringVehicles = $this->notificationRepository->getVehiclesWithExpiringDocuments($userId);

        foreach ($expiringVehicles as $vehicle) {
            if (!empty($vehicle['vehicle_inspection_expiry']) && $vehicle['vehicle_inspection_expiry'] <= date('Y-m-d', strtotime('+30 days'))) {
                $this->notificationRepository->createNotification($userId,
                    new Notification("Vehicle #{$vehicle['id']} inspection expires on {$vehicle['vehicle_inspection_expiry']}"));
            }
            if (!empty($vehicle['oc_ac_expiry']) && $vehicle['oc_ac_expiry'] <= date('Y-m-d', strtotime('+30 days'))) {
                $this->notificationRepository->createNotification($userId,
                    new Notification("Vehicle #{$vehicle['id']} OC/AC expires on {$vehicle['oc_ac_expiry']}"));
            }
        }

        if (!$return) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'checked' => count($expiringVehicles)]);
        }
    }



}
