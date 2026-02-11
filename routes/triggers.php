<?php declare(strict_types=1);

use App\Livewire\Triggers;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('triggers', Triggers\Index::class)->name('triggers.index');
    Route::livewire('triggers/create', Triggers\Create::class)->name('triggers.create');
    Route::livewire('triggers/{trigger}/edit', Triggers\Edit::class)->name('triggers.edit');
});
