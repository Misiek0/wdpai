<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/Notification.php';

class NotificationRepository extends Repository {

    public function createNotification(int $userId, Notification $notification): void {
        $conn = $this->database->connect();

        $stmt = $conn->prepare('
            INSERT INTO notifications (user_id, message)
            VALUES (?, ?)
        ');
        $stmt->execute([
            $userId,
            $notification->getMessage(),
        ]);

    }

    public function getNotificationsForUser(int $userId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT id, message, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([$userId]);

        $notifications = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = new Notification(
                message: $row['message'],
                is_read: (bool)$row['is_read'],
                created_at: (new DateTime($row['created_at']))->format('Y-m-d H:i:s'),
                id: (int)$row['id']
            );
        }

        return $notifications;
    }

    public function markNotificationAsRead(int $notificationId): void {
        $stmt = $this->database->connect()->prepare('
            UPDATE notifications SET is_read = TRUE WHERE id = ?
        ');
        $stmt->execute([$notificationId]);
    }

    public function getDriversWithExpiringDocuments(int $userId, int $days = 30): array {
        $stmt = $this->database->connect()->prepare("
            SELECT id, name, surname, license_expiry, medical_exam_expiry
            FROM drivers
            WHERE user_id = ?
            AND (
                license_expiry <= CURRENT_DATE + INTERVAL '$days days'
                OR medical_exam_expiry <= CURRENT_DATE + INTERVAL '$days days'
            )
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVehiclesWithExpiringDocuments(int $userId, int $days = 30): array {
        $stmt = $this->database->connect()->prepare("
            SELECT id, vehicle_inspection_expiry, oc_ac_expiry
            FROM vehicles
            WHERE user_id = ?
            AND (
                vehicle_inspection_expiry <= CURRENT_DATE + INTERVAL '$days days'
                OR oc_ac_expiry <= CURRENT_DATE + INTERVAL '$days days'
            )
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
