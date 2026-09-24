<?php

namespace App\Repositories\Contracts;

use App\Models\Purchase;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurchaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Purchase;

    public function nextPurchaseNumber(DateTimeInterface $date): string;

    public function create(array $data): Purchase;

    public function update(Purchase $purchase, array $data): Purchase;

    /**
     * Ringkasan penerimaan selesai (completed) pada rentang tanggal.
     *
     * @return array{total: float, count: int}
     */
    public function summaryBetween(DateTimeInterface $from, DateTimeInterface $to): array;
}
