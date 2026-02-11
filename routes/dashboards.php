<?php declare(strict_types=1);

use App\Livewire\Dashboards;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboards', Dashboards\Index::class)->name('dashboards.index');
    Route::livewire('dashboards/create', Dashboards\Create::class)->name('dashboards.create');
    Route::livewire('dashboards/{dashboard}', Dashboards\Show::class)->name('dashboards.show');
    Route::livewire('dashboards/{dashboard}/edit', Dashboards\Edit::class)->name('dashboards.edit');
});
