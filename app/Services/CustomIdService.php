<?php

namespace App\Services;

use Illuminate\Support\Str;

class CustomIdService
{
    public static function generateCustomId($model)
    {
        $name = self::$prefix ?? class_basename($model);
        $prefix = strtoupper(substr($name, 0, 4));
        $randomId = Str::random(19);
        return $prefix . '_' . $randomId;
    }
}
