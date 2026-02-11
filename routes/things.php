<?php declare(strict_types=1);

use App\Livewire\Things;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('things', Things\Index::class)->name('things.index');
    Route::livewire('things/create', Things\Create::class)->name('things.create');
    Route::livewire('things/{thing}', Things\Show::class)->name('things.show');
    Route::livewire('things/{thing}/edit', Things\Edit::class)->name('things.edit');
});
