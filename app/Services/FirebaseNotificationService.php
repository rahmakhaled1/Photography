<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\Messaging\NotFound;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebaseCredentialsPath = base_path('photography-d2f09-firebase-adminsdk-fbsvc-f354da9daf.json');

        if (!file_exists($firebaseCredentialsPath)) {
            throw new \Exception("Firebase credentials file not found at {$firebaseCredentialsPath}");
        }

        $firebase = (new Factory)->withServiceAccount($firebaseCredentialsPath);
        $this->messaging = $firebase->createMessaging();
    }

    public function sendNotification(array $data)
    {
        if (empty($data['tokens'])) {
            throw new \Exception("No device tokens provided.");
        }

        $notificationData = [
            'title' => $data['title'],
            'body' => $data['body'],
        ];

        $message = CloudMessage::new()
            ->withData($notificationData)
            ->withNotification(
                Notification::create()
                    ->withTitle($data['title'])
                    ->withBody($data['body'])
            );

        try {
            $this->messaging->sendMulticast($message, $data['tokens']);

            return response()->json(['message' => 'Notification sent successfully']);
        } catch (NotFound $e) {
            logger()->error("Invalid token(s): " . json_encode($data['tokens']));
            return response()->json(['error' => 'Failed to send notification due to invalid token(s)'], 400);
        }
    }
}
