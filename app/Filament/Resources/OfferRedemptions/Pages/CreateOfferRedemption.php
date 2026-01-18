<?php

namespace App\Filament\Resources\OfferRedemptions\Pages;

use App\Filament\Resources\OfferRedemptions\OfferRedemptionResource;
use App\Models\Customer;
use App\Models\OfferRedemption;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOfferRedemption extends CreateRecord
{
    protected static string $resource = OfferRedemptionResource::class;

    protected function handleRecordCreation(array $data): OfferRedemption
    {
        return DB::transaction(function () use ($data) {
            $customerIds = [];

            if (!empty($data['all_customers'])) {
                $customerIds = Customer::query()->pluck('id')->all();
            } else {
                $customerIds = $data['customer_ids'] ?? [];
            }

            $rows = collect($customerIds)->map(fn ($customerId) => [
                'offer_id'       => $data['offer_id'],
                'customer_id'    => $customerId,
                'reservation_id' => $data['reservation_id'] ?? null,
                'visit_id'       => $data['visit_id'] ?? null,
                'redeemed_at'    => $data['redeemed_at'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ])->all();

            // ✅ يمنع التكرار لو ضفتي unique index (أنصح بيه)
            OfferRedemption::query()->insertOrIgnore($rows);

            // Filament لازم يرجّع Record واحد
            return OfferRedemption::query()
                ->where('offer_id', $data['offer_id'])
                ->whereIn('customer_id', $customerIds)
                ->orderByDesc('id')
                ->firstOrFail();
        });
    }
}
