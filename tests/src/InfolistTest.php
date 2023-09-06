<?php

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Infolists\Components\PhoneNumberEntry;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Cheesegrits\FilamentPhoneNumbers\Tests\Fixtures\BaseInfolist;
use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;

use function Pest\Livewire\livewire;

it('can render infolist entry', function () {
    $user = User::factory()->e164()->create();
    $formattedPhone = PhoneHelper::formatPhoneNumber($user->phone);

    livewire(BaseInfolist::class, [
        'id' => $user->id,
    ])
        ->assertSee($formattedPhone);
});

it('can render infolist entry with dial', function () {
    $user = User::factory()->e164()->create();
    $formattedPhone = PhoneHelper::formatPhoneNumber($user->phone);
    $dialNumber = PhoneHelper::formatPhoneNumber(number: $user->phone, format: PhoneNumberFormat::RFC3966);

    livewire(TestInfolistWithDial::class, [
        'id' => $user->id,
    ])
        ->assertSee($formattedPhone)
        ->assertSee($dialNumber);
});

class TestInfolist extends BaseInfolist
{
    public function getInfolistSchema(): array
    {
        return [
            PhoneNumberEntry::make('phone'),
        ];
    }
}

class TestInfolistWithDial extends BaseInfolist
{
    public function getInfolistSchema(): array
    {
        return [
            PhoneNumberEntry::make('phone')
                ->dial(),
        ];
    }
}
