<?php

namespace App\Observers;

use App\Models\OfferRedemption;
use App\Models\Reservation;
use App\Models\Visit;
use Illuminate\Validation\ValidationException;

class OfferRedemptionObserver
{
    public function creating(OfferRedemption $redemption): void
    {
        // لازم offer موجود + branch loaded
        $offer = $redemption->offer()->with('branch')->first();

        if (! $offer) {
            throw ValidationException::withMessages([
                'offer_id' => 'Offer not found.',
            ]);
        }

        // ✅ enforce redeemed_at
        if (blank($redemption->redeemed_at)) {
            $redemption->redeemed_at = now();
        }

        // ✅ Tier check
        if (! $offer->isEligibleForCustomer($redemption->customer_id)) {
            throw ValidationException::withMessages([
                'customer_id' => 'Customer is not eligible for this offer tier.',
            ]);
        }

        // ✅ لو مربوط بـ reservation / visit: تأكد نفس المطعم/الفرع + نفس العميل
        $offerBranchId = $offer->branch_id;
        $offerRestaurantId = $offer->branch?->restaurant_id;

        // ممنوع تربط الاتنين مع بعض (اختياري بس قوي)
        if (filled($redemption->reservation_id) && filled($redemption->visit_id)) {
            throw ValidationException::withMessages([
                'visit_id' => 'Choose either reservation or visit, not both.',
            ]);
        }

        if (filled($redemption->reservation_id)) {
            $reservation = Reservation::query()
                ->with('branch')
                ->find($redemption->reservation_id);

            if (! $reservation) {
                throw ValidationException::withMessages([
                    'reservation_id' => 'Reservation not found.',
                ]);
            }

            if ((int) $reservation->customer_id !== (int) $redemption->customer_id) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Customer must match reservation customer.',
                ]);
            }

            // لو عايزها strict على نفس الفرع:
            if ((int) $reservation->branch_id !== (int) $offerBranchId) {
                throw ValidationException::withMessages([
                    'offer_id' => 'Offer branch must match reservation branch.',
                ]);
            }

            // أو على الأقل نفس المطعم:
            if ($offerRestaurantId && (int) $reservation->branch?->restaurant_id !== (int) $offerRestaurantId) {
                throw ValidationException::withMessages([
                    'offer_id' => 'Offer restaurant must match reservation restaurant.',
                ]);
            }
        }

        if (filled($redemption->visit_id)) {
            $visit = Visit::query()
                ->with('branch')
                ->find($redemption->visit_id);

            if (! $visit) {
                throw ValidationException::withMessages([
                    'visit_id' => 'Visit not found.',
                ]);
            }

            if ((int) $visit->customer_id !== (int) $redemption->customer_id) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Customer must match visit customer.',
                ]);
            }

            // strict على نفس الفرع
            if ((int) $visit->branch_id !== (int) $offerBranchId) {
                throw ValidationException::withMessages([
                    'offer_id' => 'Offer branch must match visit branch.',
                ]);
            }

            // أو نفس المطعم
            if ($offerRestaurantId && (int) $visit->branch?->restaurant_id !== (int) $offerRestaurantId) {
                throw ValidationException::withMessages([
                    'offer_id' => 'Offer restaurant must match visit restaurant.',
                ]);
            }
        }
    }
}
