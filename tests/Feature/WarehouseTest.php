<?php

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Region;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warehouse dapat dibuat', function () {
    $user = User::factory()->create();
    $region = Region::factory()->create(['id' => 1]);

    $response = $this
        ->actingAs($user)
        ->post(route('warehouse.store'), [
            'region_id' => $region->id,
            'name'      => 'Gudang A',
            'address'   => 'Jl. Mawar',
            'phone'     => '08123456789',
            'manager'   => 'Budi',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('warehouse.index'));

    $this->assertDatabaseHas('warehouses', [
        'name'    => 'Gudang A',
        'manager' => 'Budi',
    ]);
});

test('warehouse dapat diupdate', function () {
    $user = User::factory()->create();
    $region1 = Region::factory()->create(['id' => 1]);
    $region2 = Region::factory()->create(['id' => 2]);
    $warehouse = Warehouse::factory()->create([
        'region_id' => $region1->id,
        'name'      => 'Gudang Lama',
        'manager'   => 'Andi',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('warehouse.update', $warehouse->id), [
            'region_id' => $region2->id,
            'name'      => 'Gudang Baru',
            'address'   => 'Jl. Melati',
            'phone'     => '08987654321',
            'manager'   => 'Siti',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('warehouse.index'));

    $this->assertDatabaseHas('warehouses', [
        'id'      => $warehouse->id,
        'name'    => 'Gudang Baru',
        'manager' => 'Siti',
        'region_id' => $region2->id,
    ]);
});

test('warehouse dapat dihapus (soft delete)', function () {
    $user = User::factory()->create();
    $region = Region::factory()->create(['id' => 1]);
    $warehouse = Warehouse::factory()->create(['region_id' => $region->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('warehouse.destroy', $warehouse->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('warehouse.index'));

    $this->assertSoftDeleted('warehouses', [
        'id' => $warehouse->id,
    ]);
});

test('warehouse dapat direstore', function () {
    $user = User::factory()->create();
    $region = Region::factory()->create(['id' => 1]);
    $warehouse = Warehouse::factory()->create(['region_id' => $region->id]);
    $warehouse->delete();

    $response = $this
        ->actingAs($user)
        ->post(route('warehouse.restore', $warehouse->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('warehouse.index'));

    $this->assertDatabaseHas('warehouses', [
        'id'         => $warehouse->id,
        'deleted_at' => null,
    ]);
});

test('warehouse dapat dihapus permanen (force delete)', function () {
    $user = User::factory()->create();
    $region = Region::factory()->create(['id' => 1]);
    $warehouse = Warehouse::factory()->create(['region_id' => $region->id]);
    $warehouse->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('warehouse.force-delete', $warehouse->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('warehouse.index'));

    $this->assertDatabaseMissing('warehouses', [
        'id' => $warehouse->id,
    ]);
});

test('validasi gagal jika name kosong saat create', function () {
    $user = User::factory()->create();
    $region = Region::factory()->create(['id' => 1]);

    $response = $this
        ->actingAs($user)
        ->post(route('warehouse.store'), [
            'region_id' => $region->id,
            'name'      => '',
            'address'   => 'Jl. Mawar',
            'phone'     => '08123456789',
            'manager'   => 'Budi',
        ]);

    $response
        ->assertSessionHasErrors('name');
});

test('validasi gagal jika region_id kosong saat create', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('warehouse.store'), [
            'region_id' => '',
            'name'      => 'Gudang A',
            'address'   => 'Jl. Mawar',
            'phone'     => '08123456789',
            'manager'   => 'Budi',
        ]);

    $response
        ->assertSessionHasErrors('region_id');
});