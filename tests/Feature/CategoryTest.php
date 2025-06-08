<?php

use App\Models\Category;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('category dapat dibuat', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('category.store'), [
            'name'        => 'Kategori A',
            'type'        => 'finished_goods',
            'description' => 'Deskripsi kategori A',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('category.index'));

    $this->assertDatabaseHas('categories', [
        'name'        => 'Kategori A',
        'type'        => 'finished_goods',
        'description' => 'Deskripsi kategori A',
    ]);
});

test('category dapat diupdate', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create([
        'name'        => 'Kategori Lama',
        'type'        => 'raw_material',
        'description' => 'Deskripsi lama',
    ]);

    $response = $this
        ->actingAs($user)
        ->put(route('category.update', $category->id), [
            'name'        => 'Kategori Baru',
            'type'        => 'consumable',
            'description' => 'Deskripsi baru',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('category.index'));

    $this->assertDatabaseHas('categories', [
        'id'          => $category->id,
        'name'        => 'Kategori Baru',
        'type'        => 'consumable',
        'description' => 'Deskripsi baru',
    ]);
});

test('category dapat dihapus (soft delete)', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('category.destroy', $category->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('category.index'));

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});

test('category dapat direstore', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create();
    $category->delete();

    $response = $this
        ->actingAs($user)
        ->post(route('category.restore', $category->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('category.index'));

    $this->assertDatabaseHas('categories', [
        'id'         => $category->id,
        'deleted_at' => null,
    ]);
});

test('category dapat dihapus permanen (force delete)', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->create();
    $category->delete();

    $response = $this
        ->actingAs($user)
        ->delete(route('category.force-delete', $category->id));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('category.index'));

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('validasi gagal jika name kosong saat create', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('category.store'), [
            'name'        => '',
            'type'        => 'finished_goods',
            'description' => 'Deskripsi',
        ]);

    $response
        ->assertSessionHasErrors('name');
});

test('validasi gagal jika type kosong saat create', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('category.store'), [
            'name'        => 'Kategori',
            'type'        => '',
            'description' => 'Deskripsi',
        ]);

    $response
        ->assertSessionHasErrors('type');
});
