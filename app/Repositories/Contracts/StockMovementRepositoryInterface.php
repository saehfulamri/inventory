<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Total kuantitas & jumlah catatan per tipe pergerakan pada rentang filter.
     * DB-agnostic: hanya memakai SUM/COUNT + group by movement_type (bukan DATE_FORMAT).
     *
     * @return array<string, array{quantity: float, count: int}>
     */
    public function typeTotals(array $filters = []): array;
}
