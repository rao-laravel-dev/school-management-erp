<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeResource extends JsonResource
{
   /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'student_id'  => $this->student_id,
            'fee_type'    => $this->whenLoaded('feeType', fn () => $this->feeType->name),
            'amount'      => (float) $this->amount,
            'discount'    => (float) $this->discount,
            'paid_amount' => (float) $this->paid_amount,
            'balance'     => (float) ($this->amount - $this->discount - $this->paid_amount),
            'status'      => $this->status,
            'due_date'    => $this->due_date,
            // Late fee (runtime): repeats on every row of the same month; total from the top-level late_fees
            'month'       => $this->fee_month,
            'late_fee'    => (float) $this->pending_late_fee,
            'max_payable' => (float) ($this->amount - $this->discount - $this->paid_amount + $this->pending_late_fee),

            // Sirf show() mein transactions load hote hain (index() mein nahi)
            'transactions' => $this->whenLoaded('transactions', function () {
                return $this->transactions->map(fn ($t) => [
                    'id'               => $t->id,
                    'amount'           => (float) $t->amount,
                    'payment_method'   => $t->payment_method,
                    'transaction_date' => $t->transaction_date,
                    'reference_no'     => $t->reference_no,
                    'fine'             => (float) $t->fine, // > 0 = late fee payment
                ]);
            }),
        ];
    }
}
