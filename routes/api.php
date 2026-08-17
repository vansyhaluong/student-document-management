<?php

use App\Http\Controllers\Api\PublicStudentDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/students/{studentCode}/documents', [PublicStudentDocumentController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('api.students.documents');
