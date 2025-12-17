<?php

use Cheesegrits\FilamentPhoneNumbers\Forms\Components\PhoneNumber;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Cheesegrits\FilamentPhoneNumbers\Tests\Fixtures\BaseForm;
use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;
use Filament\Schemas\Schema;

use function Pest\Livewire\livewire;

it('can render a US format phone number', function () {
    $user = User::factory()->e164()->create();

    $formattedPhone = PhoneHelper::formatPhoneNumber(
        number: $user->phone,
        format: \Brick\PhoneNumber\PhoneNumberFormat::NATIONAL,
        region: 'US'
    );

    livewire(TestForm::class, [
        'id' => $user->id,
    ])
        ->assertFormFieldExists('phone', function (PhoneNumber $field): bool {
            return $field->isEnabled();
        })
        ->assertSchemaStateSet([
            'phone' => $formattedPhone,
        ]);
});

it('can render a US format phone number with correct mask', function () {
    $user = User::factory()->e164()->create();

    $formattedPhone = PhoneHelper::formatPhoneNumber(
        number: $user->phone,
        format: \Brick\PhoneNumber\PhoneNumberFormat::NATIONAL,
        region: 'US'
    );

    livewire(TestFormWithMask::class, [
        'id' => $user->id,
    ])
        ->assertFormFieldExists('phone', function (PhoneNumber $field): bool {
            return $field->getMask() === '(999) 999-9999';
        })
        ->assertSchemaStateSet([
            'phone' => $formattedPhone,
        ]);
});

it('can save a US format phone number to e164 format', function () {
    $user = User::factory()->e164()->create();

    $formattedPhone = PhoneHelper::formatPhoneNumber(
        number: $user->phone,
        format: \Brick\PhoneNumber\PhoneNumberFormat::NATIONAL,
        region: 'US'
    );

    $newFormattedPhone = PhoneHelper::formatPhoneNumber(
        number: '2345551212',
        format: \Brick\PhoneNumber\PhoneNumberFormat::NATIONAL,
        region: 'US'
    );

    livewire(TestFormWithMask::class, [
        'id' => $user->id,
    ])
        ->assertFormFieldExists('phone', function (PhoneNumber $field): bool {
            return $field->getMask() === '(999) 999-9999';
        })
        ->assertFormSet([
            'phone' => $formattedPhone,
        ])
        ->fillForm([
            'phone' => '2345551212',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->phone)->toBe('+12345551212');
});

class TestForm extends BaseForm
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                PhoneNumber::make('phone'),
            ]);
    }
}

class TestFormWithMask extends BaseForm
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //                TextInput::make('name'),
                //
                //                TextInput::make('email'),

                PhoneNumber::make('phone')
                    ->mask(),
            ]);
    }
}
