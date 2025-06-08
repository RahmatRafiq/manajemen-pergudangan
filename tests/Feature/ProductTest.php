<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('product dapat dibuat', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('product.store'), [
            'sku'         => 'PRD-0001',
            'name'        => 'Produk A',
            'category_id' => $category->id,
            'description' => 'Deskripsi produk',
            'unit'        => 'pcs',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('product.index'));

    $this->assertDatabaseHas('products', [
        'name'        => 'Produk A',
        'category_id' => $category->id,
        'unit'        => 'pcs',
    ]);
});

test('product dapat diupdate', function () {
    $user     = User::factory()->create();
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $product  = Product::factory()->create([
        'category_id' => $category1->id,
        'name'        => 'Produk Lama',
        'unit'        => 'pcs',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('product.update', $product->id), [
            'sku'         => $product->sku,
            'name'        => 'Produk Baru',
            'category_id' => $category2->id,
            'description' => 'Deskripsi baru',
            'unit'        => 'box',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('product.index'));

    $this->assertDatabaseHas('products', [
        'id'          => $product->id,
        'name'        => 'Produk Baru',
        'category_id' => $category2->id,
        'unit'        => 'box',
    ]);
});

test('product dapat dihapus (soft delete)', function () {
    $user    = User::factory()->create();
    $product = Product::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('product.destroy', $product->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('product.index'));

    $this->assertSoftDeleted('products', [
        'id' => $product->id,
    ]);
});

test('product dapat direstore', function () {
    $user    = User::factory()->create();
    $product = Product::factory()->create();
    $product->delete();

    $response = $this
        ->actingAs($user)
        ->post(route('product.restore', $product->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('product.index'));

    $this->assertDatabaseHas('products', [
        'id'         => $product->id,
        'deleted_at' => null,
    ]);
});

test('product dapat dihapus permanen (force delete)', function () {
    $user    = User::factory()->create();
    $product = Product::factory()->create();
    $product->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('product.force-delete', $product->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('product.index'));

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

test('validasi gagal jika name kosong saat create', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('product.store'), [
            'sku'         => 'PRD-0002',
            'name'        => '',
            'category_id' => $category->id,
            'description' => 'Deskripsi produk',
            'unit'        => 'pcs',
        ]);

    $response
        ->assertSessionHasErrors('name');
});

test('validasi gagal jika category_id kosong saat create', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('product.store'), [
            'sku'         => 'PRD-0003',
            'name'        => 'Produk A',
            'category_id' => '',
            'description' => 'Deskripsi produk',
            'unit'        => 'pcs',
        ]);

    $response
        ->assertSessionHasErrors('category_id');
});