<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleItemRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        protected SaleRepositoryInterface $sales,
        protected SaleItemRepositoryInterface $items,
        protected StockService $stockService,
        protected ProductRepositoryInterface $products,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->sales->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Sale
    {
        return $this->sales->findById($id);
    }

    public function complete(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            $date = Carbon::parse($data['sale_date']);
            $lines = $this->normalizeLines($data['items']);

            $subtotal = array_sum(array_column($lines, 'subtotal'));
            $paymentMethod = PaymentMethod::from($data['payment_method']);
            $grandTotal = round($subtotal, 2);

            $paidAmount = $paymentMethod === PaymentMethod::Cash
                ? (float) $data['paid_amount']
                : $grandTotal;

            if ($paidAmount < $grandTotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Jumlah pembayaran kurang dari total transaksi.',
                ]);
            }

            $sale = $this->sales->create([
                'user_id' => $user->getKey(),
                'sale_number' => $this->sales->nextSaleNumber($date),
                'sale_date' => $date->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => round($paidAmount - $grandTotal, 2),
                'payment_method' => $paymentMethod,
                'status' => SaleStatus::Completed,
            ]);

            foreach ($lines as $line) {
                $this->items->create([
                    'sale_id' => $sale->getKey(),
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => 0,
                    'subtotal' => $line['subtotal'],
                ]);
            }

            foreach ($lines as $line) {
                $this->stockService->decrease(
                    $line['product'],
                    $line['quantity'],
                    MovementType::SaleOut,
                    $user,
                    $sale,
                    "Penjualan {$sale->sale_number}",
                );
            }

            return $this->sales->findById($sale->getKey()) ?? $sale;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeLines(array $items): array
    {
        return array_map(function (array $item): array {
            $product = $this->products->findByIdForUpdate((int) $item['product_id']);

            if (! $product instanceof Product) {
                throw ValidationException::withMessages([
                    'items' => 'Produk tidak ditemukan.',
                ]);
            }

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => "Produk '{$product->name}' tidak aktif.",
                ]);
            }

            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $product->selling_price;

            return [
                'product' => $product,
                'product_id' => $product->getKey(),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => round($quantity * $unitPrice, 2),
            ];
        }, $items);
    }
}
