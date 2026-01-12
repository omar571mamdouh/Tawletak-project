<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\ReservationEvent;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        // أول ما يتعمل حجز
        ReservationEvent::create([
            'reservation_id' => $reservation->id,
            'event_type'     => 'created',
            'event_time'     => now(),
            'actor_type'     => 'system',
            'actor_id'       => null,
            'meta_json'      => [
                'status' => $reservation->status,
                'source' => $reservation->source,
            ],
        ]);
    }

    public function updated(Reservation $reservation): void
    {
        // لو الـ status اتغير
        if ($reservation->wasChanged('status')) {
            $from = $reservation->getOriginal('status');
            $to   = $reservation->status;

            // timestamps تلقائي
            if ($to === 'confirmed' && blank($reservation->confirmed_at)) {
                $reservation->confirmed_at = now();
            }
            if ($to === 'cancelled' && blank($reservation->cancelled_at)) {
                $reservation->cancelled_at = now();
            }
            if ($to === 'seated' && blank($reservation->seated_at)) {
                $reservation->seated_at = now();
            }
            if ($to === 'completed' && blank($reservation->completed_at)) {
                $reservation->completed_at = now();
            }

            // مهم: ما تعملش save() هنا عشان ما تعملش loop
            // بديل آمن: updateQuietly
            $reservation->updateQuietly([
                'confirmed_at' => $reservation->confirmed_at,
                'cancelled_at' => $reservation->cancelled_at,
                'seated_at'    => $reservation->seated_at,
                'completed_at' => $reservation->completed_at,
            ]);

            ReservationEvent::create([
                'reservation_id' => $reservation->id,
                'event_type'     => $to, // confirmed/cancelled/seated/completed...
                'event_time'     => now(),
                'actor_type'     => 'system',
                'actor_id'       => null,
                'meta_json'      => [
                    'from' => $from,
                    'to'   => $to,
                ],
            ]);
        }

        // (اختياري) لو table_id اتغير
        if ($reservation->wasChanged('table_id')) {
            ReservationEvent::create([
                'reservation_id' => $reservation->id,
                'event_type'     => 'table_changed',
                'event_time'     => now(),
                'actor_type'     => 'system',
                'actor_id'       => null,
                'meta_json'      => [
                    'from_table_id' => $reservation->getOriginal('table_id'),
                    'to_table_id'   => $reservation->table_id,
                ],
            ]);
        }
    }
}
