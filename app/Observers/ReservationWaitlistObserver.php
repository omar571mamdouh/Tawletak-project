<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\Waitlist;

class ReservationWaitlistObserver
{
    public function saved(Reservation $reservation): void
{
    // ✅ اشتغل بس لو status اتغير لـ confirmed في السيف ده
    if (! $reservation->wasChanged('status') && ! $reservation->wasChanged('confirmed_at')) {
        return;
    }

    $shouldBeInWaitlist =
        $reservation->status === 'confirmed'
        && blank($reservation->seated_at)
        && blank($reservation->cancelled_at)
        && blank($reservation->completed_at);

    if ($shouldBeInWaitlist) {
        Waitlist::updateOrCreate(
            [
                'customer_id' => $reservation->customer_id,
                'branch_id'   => $reservation->branch_id,
                'status'      => 'waiting',
            ],
            [
                'party_size' => $reservation->party_size,
            ]
        );
    } else {
        Waitlist::where('customer_id', $reservation->customer_id)
            ->where('branch_id', $reservation->branch_id)
            ->whereIn('status', ['waiting', 'notified'])
            ->delete();
    }
  }
}
