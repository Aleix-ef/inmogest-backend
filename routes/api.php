<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/properties/{id}/assign-tenant', [PropertyController::class, 'assignTenant'])
        ->middleware('role:manager,owner');
    Route::post('/tenants/{id}/assign-property', [TenantController::class, 'assignProperty'])
        ->middleware('role:manager,owner');
    Route::post('/tenants/{id}/detach-property', [TenantController::class, 'detachProperty'])
        ->middleware('role:manager,owner');
    Route::post('/owners/{id}/assign-property', [OwnerController::class, 'assignProperty'])
        ->middleware('role:manager');
    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])
        ->middleware('role:manager,owner');
    Route::delete('/documents/{id}/file', [DocumentController::class, 'removeFile'])
        ->middleware('role:manager,owner');

    Route::apiResource('properties', PropertyController::class)->middleware('role:manager,owner');
    Route::apiResource('tenants', TenantController::class)->middleware('role:manager,owner');
    Route::apiResource('contracts', ContractController::class)->middleware('role:manager,owner');
    Route::apiResource('owners', OwnerController::class)->middleware('role:manager');
    Route::apiResource('users', UserController::class)->middleware('role:manager');
    Route::apiResource('payments', PaymentController::class)->middleware('role:manager,owner');
    Route::apiResource('documents', DocumentController::class)->middleware('role:manager,owner');
});
