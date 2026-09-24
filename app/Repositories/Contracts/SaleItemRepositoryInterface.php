<?php

namespace App\Repositories\Contracts;

use App\Models\SaleItem;

interface SaleItemRepositoryInterface
{
    public function create(array $data): SaleItem;
}
