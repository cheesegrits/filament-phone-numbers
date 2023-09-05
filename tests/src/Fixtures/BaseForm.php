<?php

namespace Cheesegrits\FilamentPhoneNumbers\Tests\Fixtures;

use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BaseForm extends Component implements HasForms
{
    use InteractsWithForms;

    public User $user;

    public $phone;

    public $data;

    public static function make(): static
    {
        return new static();
    }

    public function data($data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function mount($id): void
    {
        $this->record = $this->user = User::find($id);
        $this->form->fill(
            $this->user->toArray()
        );
    }

    //    protected function getFormStatePath(): string
    //    {
    //        return 'data';
    //    }

    protected function getFormModel(): User
    {
        return $this->user;
    }

    public function save()
    {
        $this->user->update(
            $this->form->getState(),
        );
    }

    public function render(): View
    {
        return view('forms.fixtures.form');
    }
}
