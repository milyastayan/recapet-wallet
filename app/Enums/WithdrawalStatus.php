<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    use BaseEnum;

    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
