<?php

namespace Cheesegrits\FilamentPhoneNumbers\Commands;

use Brick\PhoneNumber\PhoneNumberFormat;
use Cheesegrits\FilamentPhoneNumbers\Support\PhoneHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class FilamentPhoneNumbersCommand extends Command
{
    public $signature = 'filament-phone-numbers:normalize {--commit} {--model=} {--field=} {--target=} {--format=} {--region=} {--delete-invalid} {--in-place}';

    public $description = 'My command';

    public function handle(): int
    {
        $commit = $this->option('commit');
        $deleteInvalid = $this->option('delete-invalid');
        $inPlace = $this->option('in-place');

        if (! $commit) {
            warning('The --commit option was not given, so no actual changes will be made');
        } else {
            info('The --commit option was given, so changes WILL BE MADE to your table');
        }

        if (! $deleteInvalid) {
            warning('The --delete-invalid option was not given, so invalid numbers will be left untouched');
        } else {
            info('The --delete-invalid option was given, so invalid numbers WILL BE REMOVED from your table (field must be nullable)');
        }

        $ogModelName = $modelName = (string) Str::of($this->option('model')
            ?? text(label: 'Model (e.g. `Location` or `Maps/Dealership`)', placeholder: 'Location', required: true))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        try {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $model = new ('\\App\\Models\\' . $modelName)();
            $modelName = '\\App\\Models\\' . $modelName;
        } catch (\Throwable) {
            try {
                $model = new $modelName;
            } catch (\Throwable) {
                echo "Can't find class $modelName or \\App\\Models\\$modelName\n";

                return static::INVALID;
            }
        }

        $fieldName = $this->option('field')
            ?? text(label: 'Phone attribute to normalize (eg. phone or phone_number)', placeholder: 'phone', required: true);

        if ($inPlace) {
            $targetFieldName = $fieldName;
        } else {
            $targetFieldName = $this->option('target')
                ?? text(label: 'Attribute to normalize to (eg. normalized_phone, leave blank to modify in-place)', placeholder: 'phone', required: false);
        }

        if (empty($targetFieldName)) {
            $targetFieldName = $fieldName;
        }

        $format = $this->option('format')
            ?? select(
                label: 'Phone Number Format (use E164 unless you have a very good reason not to)',
                options: [
                    'e164' => 'E164 (recommended)',
                    'international' => 'International',
                    'national' => 'National',
                    'rfc8966' => 'RFC3966 (not recommended)',
                ],
                default: 0
            );

        $format = match ($format) {
            'e164' => PhoneNumberFormat::E164,
            'international' => PhoneNumberFormat::INTERNATIONAL,
            'national' => PhoneNumberFormat::NATIONAL,
            'rfc8966' => PhoneNumberFormat::RFC3966,
        };

        $region = $this->option('region')
            ?? text(label: 'Two letter (alpha-2) ISO country code (eg. US or GB)', placeholder: 'US', default: 'US', required: true);

        $model::chunk(100, function ($records) use ($fieldName, $targetFieldName, $region, $format, $commit, $deleteInvalid) {
            /** @var Model $record */
            foreach ($records as $record) {
                $phone = $record->getAttribute($fieldName);

                if (! PhoneHelper::isValidPhoneNumber(number: $phone, region: $region)) {
                    if ($deleteInvalid) {
                        if ($targetFieldName === $fieldName) {
                            $this->warn('Invalid number, deleting: ' . $phone);

                            if ($commit) {
                                $record->update([
                                    $targetFieldName => null,
                                ]);
                            }
                        } else {
                            $this->warn('Invalid number, not copying: ' . $phone);
                        }
                    } else {
                        $this->warn('Invalid number, no change: ' . $phone);
                    }
                } else {
                    $normalizedPhone = PhoneHelper::normalizePhoneNumber(
                        number: $phone,
                        region: $region,
                        format: $format,
                    );

                    if ($normalizedPhone) {
                        if ($phone !== $normalizedPhone) {
                            $this->line('Normalizing: ' . $phone . ' => ' . $normalizedPhone);

                            if ($commit) {
                                $record->update([
                                    $targetFieldName => $normalizedPhone,
                                ]);
                            }
                        } else {
                            $this->line('No change: ' . $phone);
                        }
                    }
                }
            }
        });

        return self::SUCCESS;
    }
}
