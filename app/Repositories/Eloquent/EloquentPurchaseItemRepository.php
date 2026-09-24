<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseItem;
use App\Repositories\Contracts\PurchaseItemRepositoryInterface;

class EloquentPurchaseItemRepository implements PurchaseItemRepositoryInterface
{
    public function create(array $data): PurchaseItem
    {
        return PurchaseItem::create($data);
    }
}
