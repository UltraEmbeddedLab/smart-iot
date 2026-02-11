<?php declare(strict_types=1);

use App\Livewire\Devices;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('devices', Devices\Index::class)->name('devices.index');
    Route::livewire('devices/create', Devices\Create::class)->name('devices.create');
    Route::livewire('devices/{device}', Devices\Show::class)->name('devices.show');
    Route::livewire('devices/{device}/edit', Devices\Edit::class)->name('devices.edit');
});
