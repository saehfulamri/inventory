<?php

namespace App\Models;

use App\Enums\AdjustmentStatus;
use Database\Factories\StockAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @use HasFactory<StockAdjustmentFactory> */
#[Fillable(['product_id', 'user_id', 'quantity_before', 'quantity_after', 'difference', 'reason', 'status'])]
class StockAdjustment extends Model
{
    use HasFactory;

    public function casts(): array
    {
        return [
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'difference' => 'decimal:3',
            'status' => AdjustmentStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
