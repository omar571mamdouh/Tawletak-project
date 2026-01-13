<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\Waitlist;

class ReservationWaitlistObserver
{
    public function saved(Reservation $reservation): void
    {
        $shouldBeInWaitlist =
            $reservation->status === 'confirmed'
            && blank($reservation->seated_at)
            && blank($reservation->cancelled_at)
            && blank($reservation->completed_at);

        if ($shouldBeInWaitlist) {
            // ✅ Upsert by customer + branch (لأن مفيش reservation_id)
            Waitlist::updateOrCreate(
                [
                    'customer_id' => $reservation->customer_id,
                    'branch_id'   => $reservation->branch_id,
                    'status'      => 'waiting',
                ],
                [
                    'party_size' => $reservation->party_size,
                    // تقدر تحط estimated_wait_minutes لو عندك logic
                ]
            );
        } else {
            // ✅ أول ما يبقى seated/cancelled/completed: نشيل من waitlist
            Waitlist::where('customer_id', $reservation->customer_id)
                ->where('branch_id', $reservation->branch_id)
                ->whereIn('status', ['waiting', 'notified'])
                ->delete();
        }
    }
}
