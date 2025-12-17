<?php

namespace Cheesegrits\FilamentPhoneNumbers\Tests\Fixtures;

use Cheesegrits\FilamentPhoneNumbers\Infolists\Components\PhoneNumberEntry;
use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BaseInfolist extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    
    public User $user;

    public function mount($id): void
    {
        $this->user = User::find($id);
    }

    public function testInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->user)
            ->components($this->getInfolistComponents());
    }

    public function getInfolistComponents(): array
    {
        return [
            PhoneNumberEntry::make('phone'),
        ];
    }

    public function render(): View
    {
        return view('infolists.fixtures.infolist');
    }
}
