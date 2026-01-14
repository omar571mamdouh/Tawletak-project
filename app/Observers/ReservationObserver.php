<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Models\ReservationEvent;
use App\Models\Visit;
use App\Models\CustomerLoyalty;
use App\Models\LoyaltyTier;
use Illuminate\Support\Facades\DB;
use App\Models\TableStatus;

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

        $this->syncTableStatus($reservation);
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

            $this->syncTableStatus($reservation);

            // ✅ Visits + Loyalty hooks
            if ($to === 'seated') {
                $this->syncVisitAndLoyaltyOnSeated($reservation);
            }

            if ($to === 'completed') {
                $this->closeVisitOnCompleted($reservation);
            }

            if ($to === 'cancelled') {
                $this->cancelVisitOnCancelled($reservation);
            }

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

    /**
     * لما Reservation تبقى seated:
     * - create Visit مرة واحدة (idempotent by reservation_id)
     * - update customer_loyalty (visit_count + tier_id + last_visit_at)
     */
    private function syncVisitAndLoyaltyOnSeated(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {

            // ✅ اضمن seated_at موجود
            $seatedAt = $reservation->seated_at ?? now();

            // ✅ Create visit once per reservation
            $visit = Visit::firstOrCreate(
                ['reservation_id' => $reservation->id],
                [
                    'customer_id' => $reservation->customer_id,
                    'branch_id'   => $reservation->branch_id,
                    'table_id'    => $reservation->table_id,
                    'seated_at'   => $seatedAt,
                    'status'      => 'active',
                ]
            );

            // لو الزيارة موجودة قبل كده، متحسبش loyalty تاني
            if (! $visit->wasRecentlyCreated) {
                return;
            }

            // ✅ restaurant_id من branch (لازم علاقة branch موجودة وفيها restaurant_id)
            // تأكد في Reservation Model عندك:
            // public function branch(){ return $this->belongsTo(RestaurantBranch::class,'branch_id'); }
            $branch = $reservation->branch;
            $restaurantId = $branch?->restaurant_id;

            if (! $restaurantId) {
                // لو حصل لأي سبب branch مش محمّل أو restaurant_id مش موجود
                // هنوقف هنا عشان مانكسرش العملية
                return;
            }

            // ✅ default Bronze tier
            $bronzeTierId = LoyaltyTier::where('name', 'Bronze')->value('id');

            // ✅ Get / Create loyalty row for (customer, restaurant)
            $loyalty = CustomerLoyalty::firstOrCreate(
                [
                    'customer_id'   => $reservation->customer_id,
                    'restaurant_id' => $restaurantId,
                ],
                [
                    'visit_count'   => 0,
                    'tier_id'       => $bronzeTierId,
                    'last_visit_at' => null,
                ]
            );

            // ✅ Update count + last visit
            $loyalty->visit_count = ($loyalty->visit_count ?? 0) + 1;
            $loyalty->last_visit_at = $visit->seated_at;

            // ✅ Resolve tier based on min_visits
            // أعلى min_visits <= visit_count
            $tierId = LoyaltyTier::query()
                ->where('min_visits', '<=', $loyalty->visit_count)
                ->orderByDesc('min_visits')
                ->value('id');

            if ($tierId) {
                $loyalty->tier_id = $tierId;
            } elseif ($bronzeTierId) {
                $loyalty->tier_id = $bronzeTierId;
            }

            $loyalty->save();
        });
    }

    /**
     * لما Reservation تبقى completed:
     * - اقفل Visit المرتبط بالحجز لو كان active
     */
    private function closeVisitOnCompleted(Reservation $reservation): void
    {
        Visit::where('reservation_id', $reservation->id)
            ->where('status', 'active')
            ->update([
                'status'  => 'completed',
                'left_at' => now(),
            ]);
    }

    /**
     * لما Reservation تبقى cancelled:
     * - اقفل Visit المرتبط بالحجز لو كان active
     * - ملاحظة: هنا مش بننقص loyalty (اختيار شائع في المطاعم)
     */
    private function cancelVisitOnCancelled(Reservation $reservation): void
    {
        Visit::where('reservation_id', $reservation->id)
            ->where('status', 'active')
            ->update([
                'status'  => 'cancelled',
                'left_at' => now(),
            ]);
    }


private function syncTableStatus(Reservation $reservation): void
{
    if (! $reservation->table_id) return;

    $statusRow = TableStatus::firstOrCreate(
        ['table_id' => $reservation->table_id],
        ['status' => 'available']
    );

    $now = now();

    match ($reservation->status) {
        'confirmed' => $statusRow->update([
            'status'                 => 'reserved',
            'current_reservation_id' => $reservation->id,
            'occupied_since'         => null,
            'estimated_free_at'      => $this->calcEstimatedFreeAt($reservation),
        ]),

        'seated' => $statusRow->update([
            'status'                 => 'occupied',
            'current_reservation_id' => $reservation->id,
            'occupied_since'         => $reservation->seated_at ?? $now,
            'estimated_free_at'      => $this->calcEstimatedFreeAt($reservation),
        ]),

        'cancelled', 'completed' => (
            (int) $statusRow->current_reservation_id === (int) $reservation->id
                ? $statusRow->update([
                    'status' => 'available',
                    'current_reservation_id' => $reservation->id,     
                    'occupied_since' => $statusRow->occupied_since,   
                    'estimated_free_at' => $statusRow->estimated_free_at,

                ])
                : null
        ),

        default => null,
    };
}


private function calcEstimatedFreeAt(Reservation $reservation)
{
    $minutes = (int) ($reservation->expected_duration_minutes ?? 0);
    if ($minutes <= 0) $minutes = 90;
    $minutes = max(15, min($minutes, 300));

    $base = $reservation->seated_at
        ?? $reservation->reservation_time
        ?? now();

    return $base->copy()->addMinutes($minutes);
}


}
