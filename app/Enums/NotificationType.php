<?php

namespace App\Enums;

enum NotificationType: string
{
    case ReservationCreated   = 'reservation_created';
    case ReservationConfirmed = 'reservation_confirmed';
    case ReservationCancelled = 'reservation_cancelled';

    case OfferCreated         = 'offer_created';
    case OfferRedeemed        = 'offer_redeemed';

    case SystemAlert          = 'system_alert';
}
