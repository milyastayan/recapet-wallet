<?php

declare(strict_types=1);

namespace App\Enums;

trait BaseEnum
{
    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getArrayableCases(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_column(self::cases(), 'value')
        );
    }
}
