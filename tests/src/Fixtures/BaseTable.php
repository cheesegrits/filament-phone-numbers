<?php

namespace Cheesegrits\FilamentPhoneNumbers\Tests\Fixtures;

use Cheesegrits\FilamentPhoneNumbers\Tests\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class BaseTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->headerActions($this->getTableHeaderActions())
            ->recordActions($this->getTableActions())
            ->toolbarActions($this->getTableBulkActions());
    }

    protected function getTableFilters(): array
    {
        return [
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
        ];
    }

    protected function getTableActions(): array
    {
        return [
            //			Tables\Actions\EditAction::make(),
            //			Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            // BulkActionGroup::make([
            //			Tables\Actions\DeleteBulkAction::make(),
            // ]),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return User::query();
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    public function render(): View
    {
        return view('columns.fixtures.table');
    }
}
