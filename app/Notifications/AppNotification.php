<?php

namespace App\Notifications;

use App\Notifications\Channels\WebPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

// Base class: har naya notification type sirf title/body/url likhe, phir Notification::send($users, new X(...))
// Bell (database) foran sync likha jata hai; web push queue se jata hai taake kiosk scan slow na ho
abstract class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Push dobara try na ho (warna jin devices ko pehle mil chuka unhein duplicate)
    public $tries = 1;

    public function __construct()
    {
        // DB::transaction ke andar call ho to commit ke baad hi bhejo (rollback = koi notification nahi)
        $this->afterCommit();
    }

    abstract protected function title(object $notifiable): string;

    abstract protected function body(object $notifiable): string;

    // Relative path (route(..., false)), bell click aur push click dono yahi kholte hain
    abstract protected function url(object $notifiable): string;

    // Optional avatar: public/ ke andar relative path (jaise 'uploads/students/x.jpg'), default null. Absolute URL/asset() nahi
    protected function image(object $notifiable): ?string
    {
        return null;
    }

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function viaConnections(): array
    {
        return [
            'database'            => 'sync',
            WebPushChannel::class => config('queue.default'),
        ];
    }

    // Bell row (notifications.data) aur push payload dono yahi shape use karte hain (icon sirf toWebPush() mein)
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title($notifiable),
            'body'  => $this->body($notifiable),
            'url'   => $this->url($notifiable),
            'image' => $this->image($notifiable),
        ];
    }

    // Absolute URLs sirf push payload mein (image/icon/url); bell row mein save nahi hote kyunke live local DB import karta hai
    public function toWebPush(object $notifiable): array
    {
        $data  = $this->toArray($notifiable);
        $image = !empty($data['image']) ? asset($data['image']) : null;

        return array_merge($data, [
            // Push click: bell ka open route (read mark + safe redirect); id database row wala hi hai
            'url'   => $this->id ? route('notifications.open', $this->id) : url($data['url'] ?? '/'),
            'image' => $image,
            'icon'  => $image ?: asset('backend/assets/images/favicon-32x32.png'),
        ]);
    }
}
