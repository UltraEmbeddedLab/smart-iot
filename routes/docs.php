<?php declare(strict_types=1);

use App\Http\Controllers\Docs\RawController as DocsRawController;
use App\Livewire\Docs\Show as DocsShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('docs/{slug?}/raw', DocsRawController::class)
        ->where('slug', '[A-Za-z0-9\-\_\/]+')
        ->name('docs.raw');

    Route::livewire('docs/{slug?}', DocsShow::class)
        ->where('slug', '[A-Za-z0-9\-\_\/]+')
        ->name('docs.show');
});
