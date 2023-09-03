<?php

namespace Cheesegrits\FilamentPhoneNumbers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Sushi\Sushi;

class Country extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'iso';

    protected $casts = [
        'mask' => 'array',
    ];

    protected $schema = [
        'name' => 'string',
        'code' => 'string',
        'iso' => 'string',
        'flag' => 'string',
        'mask' => 'json',
    ];

    public function getAlpineMaskAttribute(): string
    {
        return Str::of($this->mask[0])
            ->replace('#', '9', )
            ->replace(')', ') ')
            ->toString();
    }

    public function getRows(): array
    {
        $rows = array_map(
            function (array $row) {
                $row['mask'] = json_encode(Arr::wrap($row['mask']));

                return $row;
            },
            File::json(__DIR__ . '/countries.json')
        );

        return $rows;
    }

    protected function sushiShouldCache(): bool
    {
        return false;
    }

    protected function sushiCacheReferencePath(): string
    {
        return __DIR__ . '/countries.json';
    }
}
