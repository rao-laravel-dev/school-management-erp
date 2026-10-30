<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// Header bell: har user sirf apni (auth()->user()) notifications dekhta aur read karta hai
class NotificationController extends Controller
{
    // Bell list (AJAX): unread count + latest 10
    public function index()
    {
        $user = Auth::user();

        // notifications() relation pehle se latest() order mein hai
        $items = $user->unreadNotifications()->limit(10)->get()->map(function ($notification) {
            return [
                'title'    => $notification->data['title'] ?? '',
                'body'     => $notification->data['body'] ?? '',
                'image'    => $this->notificationImage($notification->data['image'] ?? null),
                'time'     => $notification->created_at->diffForHumans(),
                'unread'   => is_null($notification->read_at),
                'read_url' => route('notifications.read', $notification->id),
                'open_url' => route('notifications.open', $notification->id),
            ];
        });

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items'  => $items,
        ]);
    }
    // End Method


    // Ek notification read (sirf apni, warna 404)
    public function markRead(string $id)
    {
        $user = Auth::user();
        $user->notifications()->findOrFail($id)->markAsRead();

        return response()->json(['unread' => $user->unreadNotifications()->count()]);
    }
    // End Method


    // Saari unread read
    public function markAllRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['unread' => 0]);
    }
    // End Method


    // Click: read mark karo, phir sirf apni site ka relative path kholo (open redirect na ho)
    public function open(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? '';

        // "/" se shuru, "//" nahi, backslash/whitespace nahi (browser "/\x" ko "//x" samajhta hai)
        if (is_string($url) && preg_match('#^/(?![/\\\\])[^\\\\\s]*$#', $url)) {
            return redirect($url);
        }

        // Warna role ka apna dashboard (/dashboard har role ko uske dashboard par bhejta hai)
        return redirect()->route('dashboard');
    }
    // End Method


    // Avatar URL: data['image'] public/ mein mojood ho to wo, warna project ka default no-image
    private function notificationImage($path): string
    {
        // Sirf public/ ke andar relative path: "..", leading slash/backslash ya scheme (http:, data:) reject
        if (is_string($path) && $path !== '' && !str_contains($path, '..')
            && !preg_match('#^(/|\\\\|[a-z][a-z0-9+.-]*:)#i', $path) && is_file(public_path($path))) {
            return asset($path);
        }

        return asset('uploads/no_image.jpg');
    }
    // End Method
}
