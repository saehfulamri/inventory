<?php

namespace Tests\Feature\Suppliers;

use App\Enums\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->withRole(Role::Admin)->create();
    }

    private function supplierPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'SUP-FE-001',
            'name' => 'PT Sumber Jaya',
            'phone' => '021-555-0100',
            'email' => 'kontak@sumberjaya.id',
            'address' => 'Jl. Industri No. 1, Jakarta',
            'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_view_supplier_list(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('suppliers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Suppliers/Index')
                ->where('can.viewAnySuppliers', true)
                ->where('suppliers.total', 1)
                ->where('suppliers.data.0.name', $supplier->name)
                ->where('suppliers.data.0.code', $supplier->code)
            );
    }

    public function test_supplier_list_shows_empty_state_when_no_suppliers(): void
    {
        $this->actingAs($this->admin())
            ->get(route('suppliers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Suppliers/Index')
                ->where('suppliers.total', 0)
                ->where('suppliers.data', [])
            );
    }

    public function test_admin_can_open_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('suppliers.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Suppliers/Create')
                ->where('can.viewAnySuppliers', true)
            );
    }

    public function test_admin_can_create_supplier(): void
    {
        $this->actingAs($this->admin())
            ->post(route('suppliers.store'), $this->supplierPayload())
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP-FE-001',
            'name' => 'PT Sumber Jaya',
            'phone' => '021-555-0100',
            'email' => 'kontak@sumberjaya.id',
            'address' => 'Jl. Industri No. 1, Jakarta',
            'is_active' => true,
        ]);
    }

    public function test_create_supplier_requires_name(): void
    {
        $this->actingAs($this->admin())
            ->from(route('suppliers.create'))
            ->post(route('suppliers.store'), [])
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_create_supplier_rejects_duplicate_code(): void
    {
        Supplier::factory()->create(['code' => 'SUP-DUP-1']);

        $this->actingAs($this->admin())
            ->from(route('suppliers.create'))
            ->post(route('suppliers.store'), $this->supplierPayload(['code' => 'SUP-DUP-1']))
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('code');
    }

    public function test_create_supplier_rejects_invalid_email(): void
    {
        $this->actingAs($this->admin())
            ->from(route('suppliers.create'))
            ->post(route('suppliers.store'), $this->supplierPayload(['email' => 'bukan-email']))
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('suppliers.edit', $supplier))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Suppliers/Edit')
                ->where('supplier.name', $supplier->name)
                ->where('supplier.id', $supplier->id)
            );
    }

    public function test_admin_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('suppliers.update', $supplier), [
                'code' => $supplier->code,
                'name' => 'Nama Supplier Baru',
                'phone' => '021-555-9999',
                'email' => $supplier->email,
                'address' => $supplier->address,
                'is_active' => 1,
            ])
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Nama Supplier Baru',
            'phone' => '021-555-9999',
        ]);
    }

    public function test_update_allows_unchanged_code(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('suppliers.update', $supplier), [
                'code' => $supplier->code,
                'name' => $supplier->name,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_can_deactivate_supplier_without_delete(): void
    {
        $supplier = Supplier::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->from(route('suppliers.index'))
            ->post(route('suppliers.deactivate', $supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'is_active' => false]);
        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_admin_can_filter_by_keyword_and_status(): void
    {
        Supplier::factory()->create(['name' => 'PT Alpha', 'code' => 'SUP-A-1', 'is_active' => true]);
        Supplier::factory()->create(['name' => 'CV Beta', 'code' => 'SUP-B-1', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->get(route('suppliers.index', ['keyword' => 'Alpha']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Suppliers/Index')
                ->where('filters.keyword', 'Alpha')
                ->where('suppliers.total', 1)
                ->where('suppliers.data.0.name', 'PT Alpha')
            );

        $this->actingAs($this->admin())
            ->get(route('suppliers.index', ['is_active' => 1]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Suppliers/Index')
                ->where('filters.is_active', 1)
                ->where('suppliers.total', 1)
                ->where('suppliers.data.0.name', 'PT Alpha')
            );
    }
}
