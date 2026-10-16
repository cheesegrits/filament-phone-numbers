<?php

namespace Cheesegrits\FilamentPhoneNumbers\Infolists\Components;

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Closure;
use Filament\Infolists\Components;
use Illuminate\Database\Eloquent\Model;

class PhoneNumberEntry extends Components\TextEntry
{
    protected PhoneNumberFormat | Closure | null $displayFormat = null;

    protected string | Closure | null $region = null;

    protected bool | Closure $dial = false;

    public function displayFormat(PhoneNumberFormat $format = PhoneNumberFormat::NATIONAL): static
    {
        $this->displayFormat = $format;

        return $this;
    }

    public function getDisplayFormat(): PhoneNumberFormat
    {
        return $this->displayFormat ? $this->evaluate($this->displayFormat)
            : config('filament-phone-numbers.defaults.display_format');
    }

    public function region(string $region = 'US'): static
    {
        $this->region = strtoupper($region);

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
            format: PhoneNumberFormat::RFC3966,
            region: $this->getRegion()
        ));

        return $this;
    }

    public function getDial(): string
    {
        return $this->evaluate($this->dial);
    }

    /**
     * Filament 5.10 / 4.15 added the optional $relatedRecord parameter to
     * TextEntry::formatState(); declaring it here keeps this override
     * compatible with those and with the earlier signatures.
     */
    public function formatState(mixed $state, ?Model $relatedRecord = null): mixed
    {
        $state = PhoneHelper::formatPhoneNumber(
            number: $state,
            strict: false,
            format: $this->getDisplayFormat(),
            region: $this->getRegion()
        );

        return $state;
    }
}
