<?php

use Tipoff\Discounts\Enums\AppliesTo;

return [

    'model' => [
        'user' => \App\User::class,
        'order' => \App\Order::class,
        'cart' => \App\Cart::class,
    ],

    'nova' => [
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
