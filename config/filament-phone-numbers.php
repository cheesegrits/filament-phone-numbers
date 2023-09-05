<?php

// config for Cheesegrits/FilamentPhoneNumbers
use Brick\PhoneNumber\PhoneNumberFormat;

return [
    'defaults' => [
        'region' => env('FILAMENT_PHONE_NUMBERS_ISO_COUNTRY', 'US'),
        'database_format' => env('FILAMENT_PHONE_NUMBERS_DATABASE_FORMAT', PhoneNumberFormat::E164),
        'display_format' => env('FILAMENT_PHONE_NUMBERS_DISPLAY_FORMAT', PhoneNumberFormat::NATIONAL),
        'icon' => env('FILAMENT_PHONE_NUMBERS_ICON', 'heroicon-m-phone'),
    ],
];
