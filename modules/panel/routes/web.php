<?php

use Illuminate\Support\Facades\Route;

// Export Any Model's Sample Import File

Route::get('export-sample/{model}', [
    Fpaipl\Panel\Http\Exchanges\ModelExchange::class, 'exportSample'
])->name('export-sample.model');

// Import Any Model -> via Sample Import File

Route::get('import-data/{model}', [
    Fpaipl\Panel\Http\Exchanges\ModelExchange::class, 'importData'
])->name('import-data.form');

Route::post('import-data/{model}', [
    Fpaipl\Panel\Http\Exchanges\ModelExchange::class, 'processImportData'
])->name('import-data.process');