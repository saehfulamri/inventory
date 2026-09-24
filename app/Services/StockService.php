<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class StockService
{
    public function __construct(protected ProductRepositoryInterface $productRepository) {}

    public function increase(
        Product $product,
        float $quantity,
        MovementType $type,
        ?User $user = null,
        ?Model $reference = null,
        ?string $notes = null,
    ): Product {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah penambahan stok harus lebih dari nol.');
        }

        $product = $this->productRepository->findByIdForUpdate($product->getKey()) ?? $product;

        $stockBefore = (float) $product->stock;
        $stockAfter = $stockBefore + $quantity;

        $this->productRepository->update($product, ['stock' => $stockAfter]);

        StockMovement::create([
            'product_id' => $product->getKey(),
            'user_id' => $user?->getKey(),
            'movement_type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
        ]);

        return $product;
    }

    /**
     * Set stok produk ke nilai baru dan catat stock movement (signed difference).
     */
    public function adjust(Product $product, float $newStock, ?User $user = null, ?string $notes = null): StockMovement
    {
        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'new_stock' => 'Stok baru tidak boleh negatif.',
            ]);
        }

        $product = $this->productRepository->findByIdForUpdate($product->getKey()) ?? $product;

        $stockBefore = (float) $product->stock;
        $difference = round($newStock - $stockBefore, 3);

        if ($difference === 0.0) {
            throw ValidationException::withMessages([
                'new_stock' => 'Stok baru sama dengan stok saat ini.',
            ]);
        }

        $this->productRepository->update($product, ['stock' => $newStock]);

        return StockMovement::create([
            'product_id' => $product->getKey(),
            'user_id' => $user?->getKey(),
            'movement_type' => MovementType::Adjustment,
            'quantity' => $difference,
            'stock_before' => $stockBefore,
            'stock_after' => $newStock,
            'notes' => $notes,
        ]);
    }

    public function decrease(
        Product $product,
        float $quantity,
        MovementType $type,
        ?User $user = null,
        ?Model $reference = null,
        ?string $notes = null,
    ): Product {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah pengurangan stok harus lebih dari nol.');
        }

        $product = $this->productRepository->findByIdForUpdate($product->getKey()) ?? $product;

        $stockBefore = (float) $product->stock;
        $stockAfter = $stockBefore - $quantity;

        if ($stockAfter < 0) {
            throw ValidationException::withMessages([
                'stock' => "Stok produk '{$product->name}' tidak mencukupi. Stok tersedia: {$stockBefore}.",
            ]);
        }

        $this->productRepository->update($product, ['stock' => $stockAfter]);

        StockMovement::create([
            'product_id' => $product->getKey(),
            'user_id' => $user?->getKey(),
            'movement_type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
        ]);

        return $product;
    }
}
