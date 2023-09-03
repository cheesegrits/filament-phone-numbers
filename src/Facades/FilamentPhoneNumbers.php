<?php

namespace Cheesegrits\FilamentPhoneNumbers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cheesegrits\FilamentPhoneNumbers\FilamentPhoneNumbers
 */
class FilamentPhoneNumbers extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Cheesegrits\FilamentPhoneNumbers\FilamentPhoneNumbers::class;
    }
}
