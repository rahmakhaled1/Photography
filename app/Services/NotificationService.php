<?php

namespace App\Services;

use App\Models\Notification;
use App\Services\FirebaseNotificationService;

class NotificationService
{
    protected FirebaseNotificationService $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function notifyUser(int $userId, ?string $deviceToken, string $title, string $body): void
    {
        if ($deviceToken) {
            $this->firebase->sendNotification([
                'tokens' => [$deviceToken],
                'title'  => $title,
                'body'   => $body,
            ]);
        }

        Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'is_read' => false,
        ]);
    }

    // جلب الإشعارات الخاصة بالمستخدم فقط
    public function getNotifications(int $userId, int $limit = 20)
    {  
        return Notification::where('user_id', $userId)
                           ->latest()
                           ->take($limit)
                           ->get();
    }

    // جلب عدد الإشعارات غير المقروءة الخاصة بالمستخدم فقط
    public function getUnreadNotificationsCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
                           ->where('is_read', false)
                           ->count();
    }

    // تحديث حالة إشعار كمقروء، فقط إذا كان الإشعار تابع للمستخدم
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
                                    ->where('user_id', $userId)
                                    ->first();

        if ($notification) {
            $notification->update(['is_read' => true]);
            return true;
        }

        return false;
    }
}
