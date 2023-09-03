<?php

namespace Cheesegrits\FilamentPhoneNumbers\Forms\Components;

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Enums\PhoneFormat;
use Cheesegrits\FilamentPhoneNumbers\Models\Country;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Closure;
use Filament\Forms;
use Filament\Support\RawJs;

class PhoneNumber extends Forms\Components\TextInput
{
    protected int | Closure | null $databaseFormat = null;

    protected int | Closure | null $displayFormat = null;

    protected string | Closure | null $region = null;

    protected bool | Closure $strict = false;

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

    public function databaseFormat(int | PhoneFormat $format = PhoneNumberFormat::NATIONAL): static
    {
        $this->databaseFormat = $format instanceof PhoneFormat ? $format->value : $format;

        return $this;
    }

    public function getDatabaseFormat(): int
    {
        return $this->databaseFormat ? $this->evaluate($this->databaseFormat)
            : config('filament-phone-numbers.defaults.database_format');
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

    public function strict(bool $strict = true): static
    {
        $this->strict = $strict;

        return $this;
    }

    public function getStrict(): bool
    {
        return $this->evaluate($this->strict);
    }

    public function mask(Closure | string | RawJs | null $mask = null): static
    {
        if ($mask) {
            return parent::mask($mask);
        }

        if ($this->getRegion() === 'GB') {
            return parent::mask(
                RawJs::make(
                    <<<'JS'
                    $input.startsWith('01') ? '01999 999999'
                        : $input.startsWith('02') ? '029 9999 9999'
                        : $input.startsWith('03') ? '0399 999 9999'
                        : $input.startsWith('07') ? '07999 999999'
                        : $input.startsWith('08') ? '0899 999 9999'
                        : ''
JS
                )
            );
        } else {
            $cnt = Country::where(['iso' => $this->getRegion()])->first();

            return parent::mask($cnt->alpine_mask);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (PhoneNumber $component, ?string $state, string $operation) {
            if ($state) {
                $component->state(
                    PhoneHelper::formatPhoneNumber(
                        number: $state,
                        strict: false,
                        format: $this->getDisplayFormat(),
                        region: $this->getRegion()
                    )
                );
            }
        });

        $this->dehydrateStateUsing(static function (PhoneNumber $component, ?string $state): ?string {
            return PhoneHelper::normalizePhoneNumber(
                number: $state,
                strict: false,
                format: $component->getDatabaseFormat(),
                region: $component->getRegion()
            );
        });

        //        $this->placeholder();

        $this->rules([
            function () {
                return function (string $attribute, $value, Closure $fail) {
                    if (! PhoneHelper::isValidPhoneNumber(
                        number: $value,
                        strict: $this->getStrict(),
                        allowEmpty: true,
                        region: $this->getRegion()
                    )) {
                        $fail(__(
                            $this->getStrict() ?
                                'filament-phone-numbers::phone-numbers.errors.impossible'
                                : 'filament-phone-numbers::phone-numbers.errors.invalid'
                        ));
                    }
                };
            },
        ]);

        $this->type('tel');

        $this->prefixIcon(config('filament-phone-numbers.defaults.icon', 'heroicon-m-phone'));
    }
}
