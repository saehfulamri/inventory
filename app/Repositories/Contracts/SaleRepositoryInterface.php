<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SaleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Sale;

    public function nextSaleNumber(DateTimeInterface $date): string;

    public function create(array $data): Sale;

    public function update(Sale $sale, array $data): Sale;

    /**
     * @return array{total: float, count: int}
     */
    public function summaryForDate(string $date): array;

    /**
     * @return array<string, array{total: float, count: int}>
     */
    public function chartBetween(DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * Ringkasan penjualan selesai (completed) pada rentang tanggal.
     *
     * @return array{total: float, count: int}
     */
    public function summaryBetween(DateTimeInterface $from, DateTimeInterface $to): array;
}
