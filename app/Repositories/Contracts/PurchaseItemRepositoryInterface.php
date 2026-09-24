<?php

namespace App\Repositories\Contracts;

use App\Models\PurchaseItem;

interface PurchaseItemRepositoryInterface
{
    public function create(array $data): PurchaseItem;
}
