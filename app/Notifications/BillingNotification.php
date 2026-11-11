<?php

namespace App\Notifications;

// Billing events (fees / vouchers / payments / salary): ek hi class, type key (BillingNotifier::FEES_GENERATED etc.) constructor se aati hai
// Type key notifications.data['type'] mein jati hai (naya column nahi); bell + push ka text same rehta hai
class BillingNotification extends AppNotification
{
    // Sirf plain strings: queue job mein model dobara load nahi hota
    public function __construct(
        protected string $type,
        protected string $titleText,
        protected string $bodyText,
        protected string $urlPath
    ) {
        parent::__construct();
    }

    public function type(): string
    {
        return $this->type;
    }

    // Bell row + push payload: AppNotification ka array + type key
    public function toArray(object $notifiable): array
    {
        return parent::toArray($notifiable) + ['type' => $this->type];
    }

    protected function title(object $notifiable): string
    {
        return $this->titleText;
    }

    protected function body(object $notifiable): string
    {
        return $this->bodyText;
    }

    protected function url(object $notifiable): string
    {
        return $this->urlPath;
    }
}
