<?php

namespace App\Enums;

enum MovementType: string
{
    case PurchaseIn = 'purchase_in';
    case SaleOut = 'sale_out';
    case Adjustment = 'adjustment';
    case ReturnIn = 'return_in';
    case VoidReversal = 'void_reversal';

    public function label(): string
    {
        return match ($this) {
            self::PurchaseIn => 'Penerimaan',
            self::SaleOut => 'Penjualan',
            self::Adjustment => 'Penyesuaian',
            self::ReturnIn => 'Retur Masuk',
            self::VoidReversal => 'Reversal Batal',
        };
    }
}
