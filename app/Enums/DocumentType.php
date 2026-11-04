<?php

namespace App\Enums;

enum DocumentType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';

    public function prefix(): string
    {
        return match ($this) {
            self::Purchase => 'PO',
            self::Sale => 'SO',
        };
    }
}
