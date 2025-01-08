<?php

use Illuminate\Support\Facades\Route;
use Jasotacademy\FileVersionControl\Http\Controllers\FileVersionController;

Route::prefix('file-version-control')->group(function () {
    Route::post('/upload', [FileVersionController::class, 'store']);
    Route::get('/versions/{fileId}', [FileVersionController::class, 'index']);
    Route::post('/file/{fileId}/rollback/{versionId}', [FileVersionController::class, 'rollback']);
});