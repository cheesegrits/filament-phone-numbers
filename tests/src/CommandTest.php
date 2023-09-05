<?php

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;

it('asks the right questions for the artisan normaalize command', function () {
    $this->artisan('filament-phone-numbers:normalize')
        ->expectsQuestion(
            'Model (e.g. `Location` or `Maps/Dealership`)',
            'Cheesegrits/FilamentPhoneNumbers/Tests/Models/User'
        )
        ->expectsQuestion(
            'Phone attribute to normalize (eg. phone or phone_number)',
            'phone'
        )
        ->expectsQuestion(
            'Attribute to normalize to (eg. normalized_phone, leave blank to modify in-place)',
            ''
        )
        ->expectsQuestion(
            'Phone Number Format (use E164 unless you have a very good reason not to)',
            'e164'
        )
        ->expectsQuestion(
            'Two letter (alpha-2) ISO country code (eg. US or GB)',
            'US'
        );
});

it('does not update the table if the commit flag is not given', function () {
    $users = User::factory()->count(10)->create();
    $phones = $users->pluck('phone');

    $this->artisan(
        'filament-phone-numbers:normalize',
        [
            '--model' => 'Cheesegrits/FilamentPhoneNumbers/Tests/Models/User',
            '--field' => 'phone',
            '--target' => 'normalized_phone',
            '--format' => 'e164',
            '--region' => 'US',
        ]
    );

    expect(User::whereNull('normalized_phone')->count())->toBe(10);
});

it('does updates the table to target field if the commit flag is given', function () {
    $users = User::factory()->count(10)->nationalPhone()->create();
    $phones = $users->pluck('phone');

    $this->artisan(
        'filament-phone-numbers:normalize',
        [
            '--model' => 'Cheesegrits/FilamentPhoneNumbers/Tests/Models/User',
            '--field' => 'phone',
            '--target' => 'normalized_phone',
            '--format' => 'e164',
            '--region' => 'US',
            '--commit' => true,
        ]
    );

    foreach (User::all() as $user) {
        expect(($user->normalized_phone))->toBe(PhoneHelper::formatPhoneNumber($user->phone, format: PhoneNumberFormat::E164));
    }
});

it('does updates the table in-place if the commit flag is given', function () {
    $users = User::factory()->count(10)->nationalPhone()->create();
    $phones = $users->pluck('phone', 'id');

    $this->artisan(
        'filament-phone-numbers:normalize',
        [
            '--model' => 'Cheesegrits/FilamentPhoneNumbers/Tests/Models/User',
            '--field' => 'phone',
            '--target' => '',
            '--format' => 'e164',
            '--region' => 'US',
            '--commit' => true,
        ]
    );

    foreach (User::all() as $user) {
        expect(($user->phone))->toBe(PhoneHelper::formatPhoneNumber($phones[$user->id], format: PhoneNumberFormat::E164));
    }
});

it('sets invalid numbers to null if the delete-invalid flag is set', function () {
    $users = User::factory()->count(5)->nationalPhone()->create();
    $usersInvalid = User::factory()->count(5)->invalidPhone()->create();

    $this->artisan(
        'filament-phone-numbers:normalize',
        [
            '--model' => 'Cheesegrits/FilamentPhoneNumbers/Tests/Models/User',
            '--field' => 'phone',
            '--target' => 'normalized_phone',
            '--format' => 'e164',
            '--region' => 'US',
            '--commit' => true,
            '--delete-invalid' => true,
        ]
    );

    expect(User::whereNull('normalized_phone')->count())->toBeGreaterThanOrEqual(5);
});

function convertNewlines($text)
{
    $text = implode("\n", explode("\r\n", $text));

    return $text;
}
