<?php

namespace App\Support\Enums;

use Illuminate\Support\Arr;

trait EnumCodeable
{
    public static function get(int|string $code): ?self
    {
        $first = Arr::first(self::cases())->name;
        $prefix = explode('_', $first)[0];

        $code = $prefix .'_'. $code;

        foreach (self::cases() as $constant) {
            if ($constant->name == $code) {
                return $constant;
            }
        }

        return null;
    }
}
