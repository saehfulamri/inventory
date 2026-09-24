<?php

namespace App\Repositories\Eloquent;

use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;

class EloquentStockAdjustmentRepository implements StockAdjustmentRepositoryInterface
{
    public function create(array $data): StockAdjustment
    {
        return StockAdjustment::create($data);
    }
}
