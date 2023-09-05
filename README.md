# A Filament PHP plugin for normalizing phone numbers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cheesegrits/filament-phone-numbers.svg?style=flat-square)](https://packagist.org/packages/cheesegrits/filament-phone-numbers)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cheesegrits/filament-phone-numbers/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cheesegrits/filament-phone-numbers/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cheesegrits/filament-phone-numbers/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cheesegrits/filament-phone-numbers/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cheesegrits/filament-phone-numbers.svg?style=flat-square)](https://packagist.org/packages/cheesegrits/filament-phone-numbers)



This package provides a PhoneNumber field and PhoneNumberColumn for formatting, masking and validating
phone numbers in the [Filament PHP](https://github.com/filamentphp/filament) application builder for Laravel.

It ensures that numbers persisted to the database are in a normalized format, typically
E164 (+12345551212), and displays them in National or International format.  It supports most of the
regional formats by ISO country code.

Think of phone numbers like dates.  Regardless of the format you display and input them, you always want
them stored in a standard format.

NOTE - if you would rather handle normalizing by casting attributes in your models, and don't need the additional
features like normalization of existing data,  improved searching and validation this plugin provides, you may prefer to use the
excellent [Laravel-Phone package](https://github.com/Propaganistas/Laravel-Phone).

## Installation

**NOTE** that this package is currently only compatible with Filament v3, and there are currently no plans to release a v2
compatible version.

You can install the package via composer:

```bash
composer require cheesegrits/filament-phone-numbers
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-phone-numbers-config"
```

This is the contents of the published config file:

```php
return [
    'defaults' => [
        'region' => env('FILAMENT_PHONE_NUMBERS_ISO_COUNTRY', 'US'),
        'database_format' => env('FILAMENT_PHONE_NUMBERS_DATABASE_FORMAT', PhoneNumberFormat::E164),
        'display_format' => env('FILAMENT_PHONE_NUMBERS_DISPLAY_FORMAT', PhoneNumberFormat::NATIONAL),
        'icon' => env('FILAMENT_PHONE_NUMBERS_ICON', 'heroicon-m-phone'),
    ],
];
```

These configuration values define the defaults for all usage of the field and column.  They
can be overridden on a per field or column basis.

Rather than publishing the config, we recommend using the environment variables.

FILAMENT_PHONE_NUMBERS_ISO_COUNTRY - the standard [two letter (alpha-2) ISO country code](https://www.iso.org/obp/ui/#search).

FILAMENT_PHONE_NUMBERS_DATABASE_FORMAT, FILAMENT_PHONE_NUMBERS_DDISPLAY_FORMAT - one of the following integers:

* 0 - E164
* 1 - International
* 2 - National
* 3 - RFC3966

We **strongly** recommend leaving the database format as E164.

FILAMENT_PHONE_NUMBERS_ICON - any valid Heroicons v2 icon name

## PhoneNumber Field

The simplest usage of the PhoneNumber field is:

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Forms\Components\PhoneNumber::make('phone')
```

This will use your globally configured region, database and display formats.

It will attempt to set a mask based on your region, and will automatically validate the number
according to the configured region in "permissive" mode, where it just checks that the number is
has the correct number of digits.

The full set of options is as follows.

To override the display or database formats, use one of the available PhoneFormat enums:

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Forms\Components\PhoneNumber::make('phone')
    ->displayFormat(FilamentPhoneNumbers\Enums\PhoneFormat::INTERNATIONAL)
    ->databaseFormat(FilamentPhoneNumbers\Enums\PhoneFormat::INTERNATIONAL)
```

To enforce a stricter validation, which uses published metadata to determine if a number is "possible",
use the strict() method.

Take care using this feature, as metadata may not be fully up-to-date.

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Forms\Components\PhoneNumber::make('phone')
    ->strict()
```

To override the globally configured region, use the region() method with a valid [two letter (alpha-2) ISO country code](https://www.iso.org/obp/ui/#search).

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Forms\Components\PhoneNumber::make('phone')
    ->region('GB')
```

If the mask automatically applied by the field doesn't do what you want, you can override it with the 
standard Filament mask() method:

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Forms\Components\PhoneNumber::make('phone')
    ->mask('99 99-99-99-99')
```

## PhoneNumberColumn

The basic usage of the PhoneNumberColumn is:

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Columns\PhoneNumberColumn::make('phone'),
```

With no modification, this will use the global default for display format and region.

You may override the format and region, and optionally specify the dial() method, which will render the number as a
clickable 'tel' URI:

```php
use Cheesegrits\FilamentPhoneNumbers;

FilamentPhoneNumbers\Columns\PhoneNumberColumn::make('phone')
    ->displayFormat(FilamentPhoneNumbers\Enums\PhoneFormat::NATIONAL)
    ->region('CA')
    ->dial(),
```

### Searching

With phone numbers normalized to E164, searching for formatted numbers wouldn't usually work out-of-box.  For example
searching for 555-1212 wouldn't find the number +12345551212.  Likewise, searching for (234) won't find it.

To overcome this, we override the default behavior of searchable().  First we check to see if the first character the user
typed is an open paren, '('.  If so, we assume they are looking for an area code.  So we strip any parens, get the country
dial code for the currently configured region, and prepend that to the search string.  Then we strip all other
non-numeric characters from the string.  So a search for '(2' with a region of 'US' would get converted to +12.
Or '(234) 5' would be '+12345'.  If no () are used, we don't prepend
the country code, and simply strip non-numeric characters.  So 555-1212 becomes 5551212.

If you do not wish to use this behavior (for example if you are not using E164 for your database format, although we
**strongly** recommend that you do) you can add the useDefaultSearch() method, which will bypass the search query modification.
Also, if you specify your own $query in a searchable() method, this will bypass our modification.

```php
use Cheesegrits\FilamentPhoneNumbers;
use Illuminate\Database\Query\Builder;

// uses modified search query described above
FilamentPhoneNumbers\Columns\PhoneNumberColumn::make('phone')
    ->searchable(),

// bypasses query modification to use standard Filament query
FilamentPhoneNumbers\Columns\PhoneNumberColumn::make('phone')
    ->useDefaultSearch()
    ->searchable(),

// uses your own query, bypassing custom query
FilamentPhoneNumbers\Columns\PhoneNumberColumn::make('phone')
    ->searchable(query: function (Builder $query, string $search) { 
        // your query here
    }),
```

## Artisan Command

We provide an Artisan command for normalizing phone numbers you have already collected in your database table(s).

```sh
php artisan filament-phone-numbers:normalize
```

This will run the command in test mode, whereby no actual changes will be made.

You will be prompted for:

Model (e.g. `Location` or `Maps/Dealership`)
Phone attribute to normalize (e.g. phone or phone_number)
Attribute to normalize to (e.g. normalized_phone, leave blank to modify in-place)
Phone Number Format (use E164 unless you have a very good reason not to)
Two letter (alpha-2) ISO country code (e.g. US or GB)

The command will output feedback like this:

```shell
No change: +15555551212
No change: +12561231234
No change: +14444564657
No change: +441332412251
No change: +12569909359
Normalizing: 2569909359 => +12569909359
Invalid number, no change: 465746
```

If you wish to remove invalid nunbers from your table, you can provide the --delete-invalid option, which will set invalid
numbers to null.  NOTE that your database field must be nullable for this to work.

Once you are satisfied that the changes are correct, you can call the command with the --commit option:

```shell
php artisan filament-phone-numbers:normalize --commit
```

... or ...

```shell
php artisan filament-phone-numbers:normalize --commit --delete-invalid
```

You can also provide all of the arguments on the command line, if you would rather not be prompted for them, as per
the following examples.

```shell
# --format is e164 (strongly recommended), international, national or rfc8966 (not recommended)
php artisan filament-phone-numbers::normalize --commit --model=Contacts/Customer --field=phone --target=new_phone --format=e164 --region=US

# --target is optional, if not given you should add the --in-place option (normalize in-place to same field name)
## If the --delete-invalid option will 
php artisan filament-phone-numbers::normalize --commit --delete-invalid --model=User --field=mobile_phone --in-place --format=e164 --region=US
```

## Testing

Clone the repo and run ...

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Hugh Messenger](https://github.com/cheesegrits)
- [All Contributors](../../contributors)

This plugin also uses the [Brick\PhoneNumber](https://github.com/brick/phonenumber) package by Benjamin Morel, which itself
iss a wrapper around the [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php) package,
which in turn is based on Google's [google/libphonenumber](https://github.com/google/libphonenumber) package.  Turtles all the way down.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
