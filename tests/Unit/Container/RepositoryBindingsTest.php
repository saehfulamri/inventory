<?php

namespace Tests\Unit\Container;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PurchaseItemRepositoryInterface;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use App\Repositories\Contracts\SaleItemRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Eloquent\EloquenTCategoryRepository;
use App\Repositories\Eloquent\EloquenTProductRepository;
use App\Repositories\Eloquent\EloquentPurchaseItemRepository;
use App\Repositories\Eloquent\EloquentPurchaseRepository;
use App\Repositories\Eloquent\EloquentSaleItemRepository;
use App\Repositories\Eloquent\EloquentSaleRepository;
use App\Repositories\Eloquent\EloquentStockAdjustmentRepository;
use App\Repositories\Eloquent\EloquentStockMovementRepository;
use App\Repositories\Eloquent\EloquenTSupplierRepository;
use App\Repositories\Eloquent\EloquenTUnitRepository;
use App\Services\CategoryService;
use App\Services\DashboardService;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\SaleService;
use App\Services\StockAdjustmentService;
use App\Services\StockMovementService;
use App\Services\StockService;
use App\Services\SupplierService;
use App\Services\UnitService;
use ReflectionProperty;
use Tests\TestCase;

class RepositoryBindingsTest extends TestCase
{
    public function test_repository_interfaces_resolve_to_eloquent_implementations(): void
    {
        $this->assertInstanceOf(EloquenTCategoryRepository::class, app(CategoryRepositoryInterface::class));
        $this->assertInstanceOf(EloquenTUnitRepository::class, app(UnitRepositoryInterface::class));
        $this->assertInstanceOf(EloquenTSupplierRepository::class, app(SupplierRepositoryInterface::class));
        $this->assertInstanceOf(EloquenTProductRepository::class, app(ProductRepositoryInterface::class));
    }

    public function test_services_resolve_and_inject_repository_interfaces(): void
    {
        $categoryService = app(CategoryService::class);
        $unitService = app(UnitService::class);
        $supplierService = app(SupplierService::class);
        $productService = app(ProductService::class);

        $this->assertInstanceOf(CategoryService::class, $categoryService);
        $this->assertInstanceOf(UnitService::class, $unitService);
        $this->assertInstanceOf(SupplierService::class, $supplierService);
        $this->assertInstanceOf(ProductService::class, $productService);

        $this->assertInstanceOf(CategoryRepositoryInterface::class, $this->repositoryOf($categoryService));
        $this->assertInstanceOf(UnitRepositoryInterface::class, $this->repositoryOf($unitService));
        $this->assertInstanceOf(SupplierRepositoryInterface::class, $this->repositoryOf($supplierService));
        $this->assertInstanceOf(ProductRepositoryInterface::class, $this->repositoryOf($productService));
    }

    public function test_purchase_repositories_and_service_resolve(): void
    {
        $this->assertInstanceOf(EloquentPurchaseRepository::class, app(PurchaseRepositoryInterface::class));
        $this->assertInstanceOf(EloquentPurchaseItemRepository::class, app(PurchaseItemRepositoryInterface::class));

        $purchaseService = app(PurchaseService::class);

        $this->assertInstanceOf(PurchaseService::class, $purchaseService);
        $this->assertInstanceOf(
            PurchaseRepositoryInterface::class,
            (new ReflectionProperty($purchaseService, 'purchases'))->getValue($purchaseService),
        );
        $this->assertInstanceOf(
            PurchaseItemRepositoryInterface::class,
            (new ReflectionProperty($purchaseService, 'items'))->getValue($purchaseService),
        );
        $this->assertInstanceOf(
            StockService::class,
            (new ReflectionProperty($purchaseService, 'stockService'))->getValue($purchaseService),
        );
    }

    private function repositoryOf(object $service): mixed
    {
        return (new ReflectionProperty($service, 'repository'))->getValue($service);
    }

    public function test_stock_repositories_and_services_resolve(): void
    {
        $this->assertInstanceOf(EloquentStockMovementRepository::class, app(StockMovementRepositoryInterface::class));
        $this->assertInstanceOf(EloquentStockAdjustmentRepository::class, app(StockAdjustmentRepositoryInterface::class));

        $movementService = app(StockMovementService::class);
        $this->assertInstanceOf(StockMovementService::class, $movementService);
        $this->assertInstanceOf(StockMovementRepositoryInterface::class, $this->repositoryOf($movementService));

        $adjustmentService = app(StockAdjustmentService::class);
        $this->assertInstanceOf(StockAdjustmentService::class, $adjustmentService);
        $this->assertInstanceOf(
            StockAdjustmentRepositoryInterface::class,
            (new ReflectionProperty($adjustmentService, 'adjustments'))->getValue($adjustmentService),
        );
        $this->assertInstanceOf(
            StockService::class,
            (new ReflectionProperty($adjustmentService, 'stockService'))->getValue($adjustmentService),
        );
    }

    public function test_sale_repositories_and_service_resolve(): void
    {
        $this->assertInstanceOf(EloquentSaleRepository::class, app(SaleRepositoryInterface::class));
        $this->assertInstanceOf(EloquentSaleItemRepository::class, app(SaleItemRepositoryInterface::class));

        $saleService = app(SaleService::class);
        $this->assertInstanceOf(SaleService::class, $saleService);
        $this->assertInstanceOf(
            SaleRepositoryInterface::class,
            (new ReflectionProperty($saleService, 'sales'))->getValue($saleService),
        );
        $this->assertInstanceOf(
            SaleItemRepositoryInterface::class,
            (new ReflectionProperty($saleService, 'items'))->getValue($saleService),
        );
        $this->assertInstanceOf(
            StockService::class,
            (new ReflectionProperty($saleService, 'stockService'))->getValue($saleService),
        );
        $this->assertInstanceOf(
            ProductRepositoryInterface::class,
            (new ReflectionProperty($saleService, 'products'))->getValue($saleService),
        );
    }

    public function test_dashboard_service_resolves(): void
    {
        $dashboardService = app(DashboardService::class);
        $this->assertInstanceOf(DashboardService::class, $dashboardService);
        $this->assertInstanceOf(
            SaleRepositoryInterface::class,
            (new ReflectionProperty($dashboardService, 'sales'))->getValue($dashboardService),
        );
        $this->assertInstanceOf(
            ProductRepositoryInterface::class,
            (new ReflectionProperty($dashboardService, 'products'))->getValue($dashboardService),
        );
    }
}
