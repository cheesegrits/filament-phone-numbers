<?php

namespace Cheesegrits\FilamentPhoneNumbers\Enums;

use Brick\PhoneNumber\PhoneNumberFormat;
use Filament\Support\Contracts\HasLabel;

enum PhoneFormat: int implements HasLabel
{
    case E164 = PhoneNumberFormat::E164;

    case INTERNATIONAL = PhoneNumberFormat::INTERNATIONAL;

    case NATIONAL = PhoneNumberFormat::NATIONAL;

    case RFC3966 = PhoneNumberFormat::RFC3966;

    public function getLabel(): string
    {
        return match ($this) {
            self::E164 => 'E164',
            self::INTERNATIONAL => 'International',
            self::NATIONAL => 'National',
            self::RFC3966 => 'RFC3966',
        };
    }
}
