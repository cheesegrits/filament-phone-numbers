<?php

namespace Cheesegrits\FilamentPhoneNumbers\Columns;

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Enums\PhoneFormat;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Closure;
use Filament\Tables\Columns\Concerns\CanBeSearchable;
use Filament\Tables\Columns\TextColumn;

class PhoneNumberColumn extends TextColumn
{
    use CanBeSearchable;

    protected int | Closure | null $displayFormat = null;

    protected bool | Closure $dial = false;

    protected string | Closure | null $region = null;

    public function displayFormat(int | PhoneFormat $format = PhoneNumberFormat::NATIONAL): static
    {
        $this->displayFormat = $format instanceof PhoneFormat ? $format->value : $format;

        return $this;
    }

    public function getDisplayFormat(): int
    {
        return $this->displayFormat ? $this->evaluate($this->displayFormat)
            : config('filament-phone-numbers.defaults.display_format');
    }

    public function region(string $region = 'US'): static
    {
        $this->region = $region;

        return $this;
    }

    public function getRegion(): string
    {
        return $this->region ? $this->evaluate($this->region)
            : config('filament-phone-numbers.defaults.region');
    }

    public function dial(bool $dial = true): static
    {
        $this->dial = $dial;

        $this->url(fn (?string $state) => PhoneHelper::formatPhoneNumber(
            number: $state,
            strict: false,
            format: PhoneFormat::RFC3966->value,
            region: $this->getRegion()
        ));

        return $this;
    }

    public function getDial(): string
    {
        return $this->evaluate($this->dial);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(static function (PhoneNumberColumn $column, $state): ?string {
            if (blank($state)) {
                return null;
            }

            return PhoneHelper::formatPhoneNumber(
                number: $state,
                strict: false,
                format: $column->getDisplayFormat(),
                region: $column->getRegion()
            );
        });

        //        $this->searchQuery = static function ($searh, $query, $searchQuery) {
        //            $foo = 1;
        //        };

        if ($this->getDial()) {
            $this->url(fn (string $state) => PhoneHelper::formatPhoneNumber(
                number: $state,
                strict: false,
                format: PhoneNumberFormat::RFC3966,
                region: $this->getRegion()
            ));
        }
    }
}
