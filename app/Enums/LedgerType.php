<?php

namespace App\Enums;

enum LedgerType: string
{
    use BaseEnum;
    case Credit = 'credit';
    case Debit = 'debit';
    case Fee = 'fee';
}
