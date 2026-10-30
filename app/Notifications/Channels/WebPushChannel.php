<?php

namespace App\Notifications\Channels;

use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

// minishlink/web-push se user ke har subscribed browser par push; expired (404/410) subscription delete
class WebPushChannel
{
    public function send(object $notifiable, AppNotification $notification): void
    {
        if (! method_exists($notifiable, 'pushSubscriptions')) {
            return;
        }

        $subscriptions = $notifiable->pushSubscriptions()->get();
        if ($subscriptions->isEmpty()) {
            return; // koi device subscribed nahi: VAPID / network kaam hi nahi
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('webpush.subject'),
                'publicKey'  => config('webpush.public_key'),
                'privateKey' => config('webpush.private_key'),
            ],
        ], ['TTL' => 86400]); // 1 din: device offline ho to itni der tak deliver ho sakta hai

        $payload = json_encode($notification->toWebPush($notifiable));

        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->public_key,
                'authToken'       => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding,
            ]), $payload);
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                continue;
            }

            // Browser ne subscription khatam kar di (404/410): row hata do, dobara try nahi
            if ($report->isSubscriptionExpired()) {
                $notifiable->pushSubscriptions()->where('endpoint', $report->getEndpoint())->delete();
                continue;
            }

            Log::warning('Web push failed', [
                'user_id'  => $notifiable->id,
                'endpoint' => $report->getEndpoint(),
                'reason'   => $report->getReason(),
            ]);
        }
    }
}
