<?php

declare(strict_types=1);

use App\Livewire\Admin\UserManager;
use App\Models\User;
use Livewire\Livewire;

it('mass-assignment is_admin přes User::create neprojde', function () {
    $user = User::create([
        'name' => 'Útočník',
        'email' => 'utocnik@example.cz',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);

    // in-memory: atribut byl zahozen (null), v DB platí default false
    expect($user->is_admin)->not->toBeTrue();
    expect($user->fresh()->is_admin)->toBeFalse();
});

it('mass-assignment is_admin přes update/fill neprojde', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $user->update(['name' => 'Nové jméno', 'is_admin' => true]);

    expect($user->fresh()->is_admin)->toBeFalse();

    $user->fill(['is_admin' => true]);

    expect($user->is_admin)->toBeFalse();
});

it('explicitní přiřazení is_admin funguje', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $user->is_admin = true;
    $user->save();

    expect($user->fresh()->is_admin)->toBeTrue();
});

it('factory umí vytvořit admina (unguarded vytváření)', function () {
    $user = User::factory()->create(['is_admin' => true]);

    expect($user->fresh()->is_admin)->toBeTrue();
});

it('admin UserManager toggluje is_admin explicitně', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    Livewire::actingAs($admin)
        ->test(UserManager::class)
        ->call('toggleAdmin', $user->id);

    expect($user->fresh()->is_admin)->toBeTrue();
});
