<?php

namespace App\Repositories\Contracts;

use App\Models\StockAdjustment;

interface StockAdjustmentRepositoryInterface
{
    public function create(array $data): StockAdjustment;
}
