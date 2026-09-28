<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contract for Sale repository.
 */
interface SaleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15, ?int $page = null): LengthAwarePaginator;

    public function findById(int $id): ?Sale;

    public function create(array $data): Sale;

    public function update(Sale $sale, array $data): Sale;

    /** @return array{total: float, count: int} */
    public function summaryForDate(string $date): array;

    /** @return array<string, array{total: float, count: int}> */
    public function chartBetween(DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * Ringkasan penjualan selesai (completed) pada rentang tanggal.
     *
     * @return array{total: float, count: int}
     */
    public function summaryBetween(DateTimeInterface $from, DateTimeInterface $to): array;

    /** Get today’s sales with items and eager‑loaded product relation. */
    public function todayWithItems(): Collection;
}
