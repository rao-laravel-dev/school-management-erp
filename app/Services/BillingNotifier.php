<?php

namespace App\Services;

use App\Models\FeeVoucher;
use App\Models\SalarySlip;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BillingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

// Billing notifications (bell + push): fees / vouchers / fee paid -> parents, salary -> staff, admin copy / cron summary -> admins.
// Controllers aur services sirf ek line call karte hain; har public method ka try/catch + Log hai, taake notification ki ghalti
// fee, voucher, salary ya payment kabhi na roke (data pehle hi commit hota hai). Bulk sends ek queued closure se (id-only payload)
class BillingNotifier
{
    // Fixed type keys (notifications.data['type']); future superadmin on/off page inhi par chalega (notify() mein)
    public const FEES_GENERATED    = 'billing.fees_generated';
    public const VOUCHER_GENERATED = 'billing.voucher_generated';
    public const FEE_PAID          = 'billing.fee_paid';
    public const SALARY_GENERATED  = 'billing.salary_generated';
    public const SALARY_PAID       = 'billing.salary_paid';
    public const ADMIN_ACTIVITY    = 'billing.admin_activity';

    // Boss ka rule: voucher notification sirf pehli dafa banne par. Regenerate par bhi chahiye to true kar do
    public const NOTIFY_ON_REGENERATE = false;

    // ---------------------------------------------------------------- fees generated (auto / Generate Now / Catch up)

    // $studentIds = is run mein NAYI fee row wale students; $voucherStudentIds = jin ka voucher isi run mein bana
    public function feesGenerated(string $month, array $studentIds, array $voucherStudentIds): void
    {
        try {
            $studentIds = array_values(array_unique($studentIds));
            if (!$studentIds) {
                return;
            }

            dispatch(static function () use ($month, $studentIds, $voucherStudentIds) {
                app(BillingNotifier::class)->sendFeesGenerated($month, $studentIds, $voucherStudentIds);
            })->afterCommit();
        } catch (\Throwable $e) {
            Log::error('billing notify fees_generated: ' . $e->getMessage());
        }
    }

    public function sendFeesGenerated(string $month, array $studentIds, array $voucherStudentIds): void
    {
        try {
            $label  = $this->monthLabel($month);
            $groups = $this->parentGroups(Student::with('parent')->whereIn('id', $studentIds)->get());
            $users  = User::whereIn('id', $groups->keys())->get()->keyBy('id');

            foreach ($groups as $userId => $children) {
                $body  = "{$label} fees generated for " . $this->names($children) . '.';
                $ready = $children->filter(fn($s) => in_array($s->id, $voucherStudentIds));
                if ($ready->isNotEmpty()) {
                    $body .= ' Voucher ready for ' . $this->names($ready) . '.';
                }

                $this->notify($users[$userId], self::FEES_GENERATED, 'Fees generated', $body, $this->parentUrl($children, '#pills-voucher'));
            }
        } catch (\Throwable $e) {
            Log::error('billing notify fees_generated send: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------- voucher generated (manual Generate / Bulk Generate)

    // $newIds = pehli dafa bane vouchers; $updatedIds = regenerate hue (sirf NOTIFY_ON_REGENERATE true ho to)
    public function vouchersGenerated(array $newIds, ?User $actor = null, array $updatedIds = []): void
    {
        try {
            $ids = array_values(array_unique(array_merge($newIds, self::NOTIFY_ON_REGENERATE ? $updatedIds : [])));
            if (!$ids) {
                return;
            }

            $actorId = $actor ? $actor->id : null;

            if (count($ids) === 1) {
                $this->sendVouchersGenerated($ids, $actorId);
                return;
            }

            dispatch(static function () use ($ids, $actorId) {
                app(BillingNotifier::class)->sendVouchersGenerated($ids, $actorId);
            })->afterCommit();
        } catch (\Throwable $e) {
            Log::error('billing notify voucher_generated: ' . $e->getMessage());
        }
    }

    public function sendVouchersGenerated(array $voucherIds, ?int $actorId): void
    {
        try {
            $vouchers = FeeVoucher::with('student.parent')->whereIn('id', $voucherIds)->get();
            $students = $vouchers->map(fn($v) => $v->student)->filter()->unique('id');
            $groups   = $this->parentGroups($students);
            $users    = User::whereIn('id', $groups->keys())->get()->keyBy('id');

            foreach ($groups as $userId => $children) {
                $lines = [];
                foreach ($vouchers->whereIn('student_id', $children->pluck('id')) as $v) {
                    $lines[] = "Voucher #{$v->voucher_no} ({$this->monthLabel($v->period)}) for {$v->student->full_name}: Rs "
                        . number_format((float) $v->amount_before_due) . ', due ' . $v->due_date->format('d M') . '.';
                }

                $this->notify($users[$userId], self::VOUCHER_GENERATED, 'Fee voucher ready', implode(' ', $lines), $this->parentUrl($children, '#pills-voucher'));
            }

            $actor = $actorId ? User::find($actorId) : null;
            $url   = $students->count() === 1
                ? route('fees.collect.show', $students->first()->id, false)
                : route('fees.collect.index', [], false);
            $this->adminCopy($actor, 'Fee vouchers generated', 'Fee vouchers generated by ' . ($actor ? $this->actorLabel($actor) : '') . ': '
                . $vouchers->count() . ' voucher(s) for ' . $this->names($students) . '.', $url);
        } catch (\Throwable $e) {
            Log::error('billing notify voucher_generated send: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------- fee paid

    // $receiptId = receipt ka id (payment_group_id, ya group na ho to transaction id): late fee wali rows isi ka hissa hain
    public function feePaid(int $receiptId, ?User $actor = null): void
    {
        try {
            $rows = Transaction::with('studentFee')
                ->where('category', 'fee')
                ->where(fn($q) => $q->where('id', $receiptId)->orWhere('payment_group_id', $receiptId))
                ->get()
                ->filter(fn($t) => $t->studentFee);

            // student_id => is receipt mein us ki total raqam
            $byStudent = $rows->groupBy(fn($t) => $t->studentFee->student_id)->map(fn($g) => $g->sum('amount'));
            if ($byStudent->isEmpty()) {
                return;
            }

            $students = Student::with('parent')->whereIn('id', $byStudent->keys())->get();
            $groups   = $this->parentGroups($students);
            $users    = User::whereIn('id', $groups->keys())->get()->keyBy('id');

            foreach ($groups as $userId => $children) {
                $total = $children->sum(fn($s) => $byStudent[$s->id]);

                $this->notify(
                    $users[$userId],
                    self::FEE_PAID,
                    'Fee payment received',
                    'Rs ' . number_format((float) $total) . ' received for ' . $this->names($children) . " (receipt #{$receiptId}). Thank you.",
                    $this->parentUrl($children, '#pills-finance')
                );
            }

            $url = $students->count() === 1
                ? route('fees.collect.show', $students->first()->id, false)
                : route('fees.collect.index', [], false);
            $this->adminCopy($actor, 'Fees collected', 'Fees collected by ' . ($actor ? $this->actorLabel($actor) : '') . ': Rs '
                . number_format((float) $byStudent->sum()) . ' for ' . $this->names($students) . " (receipt #{$receiptId}).", $url);
        } catch (\Throwable $e) {
            Log::error('billing notify fee_paid: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------- salary slips generated / paid

    public function salarySlipsGenerated(array $slipIds, ?User $actor = null): void
    {
        try {
            if (!$slipIds) {
                return;
            }

            $actorId = $actor ? $actor->id : null;

            dispatch(static function () use ($slipIds, $actorId) {
                app(BillingNotifier::class)->sendSalarySlipsGenerated($slipIds, $actorId);
            })->afterCommit();
        } catch (\Throwable $e) {
            Log::error('billing notify salary_generated: ' . $e->getMessage());
        }
    }

    public function sendSalarySlipsGenerated(array $slipIds, ?int $actorId): void
    {
        try {
            $slips   = SalarySlip::with('user')->whereIn('id', $slipIds)->get();
            $allowed = User::permission('access-my-salary-slips')->where('status', 1)->whereIn('id', $slips->pluck('user_id'))->pluck('id');

            foreach ($slips->filter(fn($s) => $s->user && $allowed->contains($s->user_id)) as $slip) {
                $this->notify(
                    $slip->user,
                    self::SALARY_GENERATED,
                    'Salary slip ready',
                    "Your salary slip for {$this->monthLabel($slip->month)} is ready: net Rs " . number_format((float) $slip->net_salary) . '.',
                    route('my.salary_slips.show', $slip->id, false)
                );
            }

            $actor = $actorId ? User::find($actorId) : null;
            $first = $slips->first();
            if ($first) {
                $this->adminCopy($actor, 'Salary slips generated', 'Salary slips generated by ' . ($actor ? $this->actorLabel($actor) : '') . ': '
                    . $slips->count() . " slip(s) for {$this->monthLabel($first->month)}.", route('salary_slips.index', ['month' => $first->month], false));
            }
        } catch (\Throwable $e) {
            Log::error('billing notify salary_generated send: ' . $e->getMessage());
        }
    }

    public function salaryPaid(SalarySlip $slip, ?User $actor = null): void
    {
        try {
            $user = $slip->user;
            if ($user && $user->status == 1 && $user->can('access-my-salary-slips')) {
                $this->notify(
                    $user,
                    self::SALARY_PAID,
                    'Salary paid',
                    "Your {$this->monthLabel($slip->month)} salary (Rs " . number_format((float) $slip->net_salary) . ') was paid.',
                    route('my.salary_slips.show', $slip->id, false)
                );
            }

            $this->adminCopy($actor, 'Salary paid', 'Salary paid by ' . ($actor ? $this->actorLabel($actor) : '') . ': Rs '
                . number_format((float) $slip->net_salary) . ' to ' . ($user ? $user->name : 'staff') . " ({$this->monthLabel($slip->month)}).", route('salary_slips.show', $slip->id, false));
        } catch (\Throwable $e) {
            Log::error('billing notify salary_paid: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------- cron summary (admins only, never per student)

    // $rows = billing:generate-monthly ki result rows (BillingService::runMonth()); sirf jab kuch bana ho
    public function cronSummary(array $rows): void
    {
        try {
            $lines  = [];
            $hasFee = false;

            foreach ($rows as $r) {
                if ($r['type'] === 'fees' && ($r['created'] > 0 || $r['vouchers'] > 0)) {
                    $lines[] = "{$this->monthLabel($r['month'])} fees: {$r['created']} rows, {$r['vouchers']} vouchers created";
                    $hasFee  = true;
                } elseif ($r['type'] === 'salary' && $r['created'] > 0) {
                    $lines[] = "{$this->monthLabel($r['month'])} salary: {$r['created']} slips created";
                }
            }

            if (!$lines) {
                return;
            }

            $this->notify(
                $this->adminRecipients(),
                self::ADMIN_ACTIVITY,
                'Auto billing',
                implode('. ', $lines) . '.',
                $hasFee ? route('fees.collect.index', [], false) : route('salary_slips.index', [], false)
            );
        } catch (\Throwable $e) {
            Log::error('billing notify cron_summary: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------- helpers

    // Ek jagah se sab bheje jate hain: type key ke hisab se on/off check yahan aayega
    private function notify($to, string $type, string $title, string $body, string $url): void
    {
        Notification::send($to, new BillingNotification($type, $title, $body, $url));
    }

    // Actor admin/superadmin na ho (jaise accountant) to admins ko copy; actor khud nahi
    private function adminCopy(?User $actor, string $title, string $body, string $url): void
    {
        if (!$actor || $actor->hasAnyRole(['admin', 'superadmin'])) {
            return;
        }

        $this->notify($this->adminRecipients($actor->id), self::ADMIN_ACTIVITY, $title, $body, $url);
    }

    // Active admin + superadmin (attendance wali recipients() jaisi query)
    private function adminRecipients(?int $exceptUserId = null)
    {
        return User::role(['admin', 'superadmin'])
            ->where('status', 1)
            ->when($exceptUserId, fn($q) => $q->where('id', '!=', $exceptUserId))
            ->get();
    }

    // Students ko parent user ke hisab se group karo (ek parent = ek notification); sirf active parent jis ke paas access-parent-fees ho
    private function parentGroups($students)
    {
        $students = $students->filter(fn($s) => $s->parent && $s->parent->user_id);

        $allowed = User::permission('access-parent-fees')
            ->where('status', 1)
            ->whereIn('id', $students->map(fn($s) => $s->parent->user_id)->unique()->values())
            ->pluck('id');

        return $students->filter(fn($s) => $allowed->contains($s->parent->user_id))
            ->groupBy(fn($s) => $s->parent->user_id);
    }

    // 1 bacha = us ka dashboard (tab anchor ke saath), kai bachay = parent dashboard
    private function parentUrl($children, string $anchor): string
    {
        if ($children->count() === 1) {
            return route('parent.students.child-dashboard', $children->first()->id, false) . $anchor;
        }

        return route('parent.dashboard', [], false);
    }

    private function names($students): string
    {
        return $students->map(fn($s) => $s->full_name)->implode(', ');
    }

    private function monthLabel(?string $month): string
    {
        return $month ? Carbon::parse($month . '-01')->format('F Y') : '';
    }

    private function actorLabel(User $actor): string
    {
        return $actor->name . ' (' . ucfirst(str_replace('_', ' ', (string) $actor->getRoleNames()->first())) . ')';
    }
}
