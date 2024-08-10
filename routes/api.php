<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxRateController;


Route::middleware('api')->group(function (){
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['api','auth:api'])->group(function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::get('me', [AuthController::class, 'me']);
    Route::delete('me', [AuthController::class, 'destroy']);

    Route::ApiResource('customer', CustomerController::class);
    Route::ApiResource('invoice', InvoiceController::class);
    Route::ApiResource('product', ProductController::class);
    Route::ApiResource('supplier', SupplierController::class);
    Route::get('taxrate', [TaxRateController::class,'getTaxRate']);
    Route::get('resettaxrate', [TaxRateController::class,'resetTaxRate']);
    Route::post('taxrate', [TaxRateController::class,'updateTaxRate']);
});
