<?php

namespace App\Services;

use App\Enums\AdjustmentStatus;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function __construct(
        protected StockAdjustmentRepositoryInterface $adjustments,
        protected StockService $stockService,
    ) {}

    public function adjust(Product $product, float $newStock, string $reason, User $user): StockAdjustment
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penyesuaian wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($product, $newStock, $reason, $user) {
            $movement = $this->stockService->adjust(
                $product,
                $newStock,
                $user,
                "Penyesuaian stok: {$reason}",
            );

            return $this->adjustments->create([
                'product_id' => $product->getKey(),
                'user_id' => $user->getKey(),
                'quantity_before' => $movement->stock_before,
                'quantity_after' => $movement->stock_after,
                'difference' => $movement->quantity,
                'reason' => $reason,
                'status' => AdjustmentStatus::Completed,
            ]);
        });
    }
}
