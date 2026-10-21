<?php

namespace App\Console\Commands;

use App\Models\BillingSetting;
use App\Services\BillingService;
use Illuminate\Console\Command;

class BillingGenerateMonthly extends Command
{
    protected $signature = 'billing:generate-monthly {--type=all : fees, salary or all} {--month= : YYYY-MM (one month, ignores the auto switches)}';

    protected $description = 'Generate monthly student fees and staff salary slips from the billing settings. Safe to run again: nothing is created twice.';

    public function handle(BillingService $billing): int
    {
        $type  = $this->option('type');
        $month = $this->option('month');

        if (!in_array($type, ['fees', 'salary', 'all'], true)) {
            $this->error('--type must be fees, salary or all.');
            return self::FAILURE;
        }
        if ($month && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('--month must be in YYYY-MM format.');
            return self::FAILURE;
        }

        $types = $type === 'all' ? BillingService::TYPES : [$type];
        $rows  = [];

        foreach ($types as $t) {
            if ($month) {
                // Ek mahina: jis academic year mein ye mahina aata hai us ki setting
                $settings = BillingSetting::with('academicYear')->get()
                    ->filter(fn($s) => in_array($month, $billing->billingMonths($s->academicYear), true));

                foreach ($settings as $setting) {
                    $rows[] = $billing->runMonth($setting, $t, $month);
                }
            } else {
                // Catch-up: sirf jin ka auto switch on hai
                $settings = BillingSetting::with('academicYear')
                    ->where($t === 'fees' ? 'auto_fees' : 'auto_salary', true)
                    ->get();

                foreach ($settings as $setting) {
                    foreach ($billing->catchUp($setting, $t) as $row) {
                        $rows[] = $row;
                    }
                }
            }
        }

        if ($rows) {
            $this->table(['Year', 'Type', 'Month', 'Created', 'Skipped', 'Note'], array_map('array_values', $rows));
        } else {
            $this->line('Nothing due.');
        }

        return self::SUCCESS;
    }
}
