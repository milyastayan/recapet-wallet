<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
