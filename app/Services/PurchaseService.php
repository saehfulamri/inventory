<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\User;
use App\Repositories\Contracts\PurchaseItemRepositoryInterface;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchases,
        protected PurchaseItemRepositoryInterface $items,
        protected StockService $stockService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchases->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Purchase
    {
        return $this->purchases->findById($id);
    }

    public function create(array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($data, $user) {
            $date = Carbon::parse($data['purchase_date']);
            $items = $this->normalizeItems($data['items']);

            $purchase = $this->purchases->create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->getKey(),
                'purchase_number' => $this->purchases->nextPurchaseNumber($date),
                'purchase_date' => $date->toDateString(),
                'status' => PurchaseStatus::Draft,
                'total_amount' => array_sum(array_column($items, 'subtotal')),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $this->items->create([
                    'purchase_id' => $purchase->getKey(),
                    ...$item,
                ]);
            }

            return $purchase;
        });
    }

    public function finalize(Purchase $purchase, User $user): Purchase
    {
        if ($purchase->status !== PurchaseStatus::Draft) {
            throw ValidationException::withMessages([
                'purchase' => 'Penerimaan ini sudah difinalisasi.',
            ]);
        }

        return DB::transaction(function () use ($purchase, $user) {
            $purchase->loadMissing(['items.product']);

            foreach ($purchase->items as $item) {
                $this->stockService->increase(
                    $item->product,
                    (float) $item->quantity,
                    MovementType::PurchaseIn,
                    $user,
                    $purchase,
                    "Penerimaan {$purchase->purchase_number}",
                );
            }

            return $this->purchases->update($purchase, ['status' => PurchaseStatus::Completed]);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        return array_map(function (array $item): array {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            return [
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
            ];
        }, $items);
    }
}
