<?php

declare(strict_types=1);

use Tipoff\Support\Enums\AppliesTo;

return [

    'nova_class' => [
        'user' => \App\Nova\User::class,
    ],

    'applications' => [
        AppliesTo::ORDER => 'Order',
        AppliesTo::PARTICIPANT => 'Each Participant in Bookings',
        // AppliesTo::SINGLE_PARTICIPANT => 'Single Participant in Bookings',
        // AppliesTo::BOOKING => 'Each Booking in Order',
        // AppliesTo::PRODUCT => 'Each Product in Order',
        // AppliesTo::BOOKING_AND_PRODUCT => 'Each Booking & Product in Order',
    ],
];
