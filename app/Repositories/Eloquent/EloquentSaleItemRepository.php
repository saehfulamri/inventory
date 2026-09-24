<?php

namespace App\Repositories\Eloquent;

use App\Models\SaleItem;
use App\Repositories\Contracts\SaleItemRepositoryInterface;

class EloquentSaleItemRepository implements SaleItemRepositoryInterface
{
    public function create(array $data): SaleItem
    {
        return SaleItem::create($data);
    }
}
