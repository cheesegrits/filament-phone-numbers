<?php

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Columns\PhoneNumberColumn;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Cheesegrits\FilamentPhoneNumbers\Tests\Fixtures\BaseTable;
use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;
use Filament\Tables;

use function Pest\Livewire\livewire;

it('can render formatted phone numbers', function () {
    $users = User::factory()->count(10)->e164()->create();
    $user = $users->first();
    $formattedPhone = PhoneHelper::formatPhoneNumber($user->phone);

    livewire(TestTable::class)
        ->assertTableColumnExists('phone')
        ->assertTableColumnFormattedStateSet('phone', $formattedPhone, $user);
});

it('can render formatted phone numbers with format override', function () {
    $users = User::factory()->count(10)->e164()->create();
    $user = $users->first();
    $formattedPhone = PhoneHelper::formatPhoneNumber($user->phone, format: PhoneNumberFormat::INTERNATIONAL);

    livewire(TestTableInternational::class)
        ->assertTableColumnExists('phone')
        ->assertTableColumnFormattedStateSet('phone', $formattedPhone, $user);
});

it('can render formatted phone numbers with dial', function () {
    $users = User::factory()->count(10)->e164()->create();
    $user = $users->first();
    $formattedPhone = PhoneHelper::formatPhoneNumber($user->phone);
    $dialNumber = PhoneHelper::formatPhoneNumber(number: $user->phone, format: PhoneNumberFormat::RFC3966);

    livewire(TestTableWithDial::class)
        ->assertTableColumnExists('phone')
        ->assertTableColumnFormattedStateSet('phone', $formattedPhone, $user)
        ->assertSee($dialNumber);
});

it('can can search for E164 normalized phone numbers', function () {
    $users = User::factory()->count(9)->e164()->create();
    $searchUser = User::factory()->phone('+12345551212')->create();
    $formattedPhone = PhoneHelper::formatPhoneNumber($searchUser->phone);
    $dialNumber = PhoneHelper::formatPhoneNumber(number: $searchUser->phone, format: PhoneNumberFormat::RFC3966);

    livewire(TestTableSearchable::class)
        ->assertTableColumnExists('phone')
        ->assertTableColumnFormattedStateSet('phone', $formattedPhone, $searchUser)
        ->searchTable('555-1212')
        ->assertCanSeeTableRecords([$searchUser])
        ->searchTable('(234) 555')
        ->assertCanSeeTableRecords([$searchUser])
        ->searchTable('(555)')
        ->assertCanNotSeeTableRecords([$searchUser]);
});

it('can bypass E164 search query modification', function () {
    $users = User::factory()->count(9)->e164()->create();
    $searchUser = User::factory()->phone('+12345551212')->create();
    $formattedPhone = PhoneHelper::formatPhoneNumber($searchUser->phone);
    $dialNumber = PhoneHelper::formatPhoneNumber(number: $searchUser->phone, format: PhoneNumberFormat::RFC3966);

    livewire(TestTableSearchableBypassed::class)
        ->assertTableColumnExists('phone')
        ->assertTableColumnFormattedStateSet('phone', $formattedPhone, $searchUser)
        ->searchTable('(234) 555')
        ->assertCanNotSeeTableRecords([$searchUser])
        ->searchTable('234555')
        ->assertCanSeeTableRecords([$searchUser]);
});

class TestTable extends BaseTable
{
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            PhoneNumberColumn::make('phone'),
        ];
    }
}

class TestTableWithDial extends BaseTable
{
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            PhoneNumberColumn::make('phone')
                ->dial(),
        ];
    }
}

class TestTableSearchable extends BaseTable
{
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            PhoneNumberColumn::make('phone')
                ->searchable(),
        ];
    }
}

class TestTableSearchableBypassed extends BaseTable
{
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            PhoneNumberColumn::make('phone')
                ->useDefaultSearch()
                ->searchable(),
        ];
    }
}

class TestTableInternational extends BaseTable
{
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
            PhoneNumberColumn::make('phone')
                ->displayFormat(PhoneNumberFormat::INTERNATIONAL),
        ];
    }
}
