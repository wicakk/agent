<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'check.subscription', 'branch.scope'])->group(function () {

    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Stock
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/',                    [StockController::class, 'index'])->name('index');
        Route::get('/create',             [StockController::class, 'create'])->name('create')->middleware('role:owner,admin');
        Route::post('/',                   [StockController::class, 'store'])->name('store')->middleware('role:owner,admin');
        Route::get('/{product}/edit',     [StockController::class, 'edit'])->name('edit')->middleware('role:owner,admin');
        Route::put('/{product}',          [StockController::class, 'update'])->name('update')->middleware('role:owner,admin');
        Route::post('/{product}/movement',[StockController::class, 'movement'])->name('movement');
        Route::get('/{product}/history',  [StockController::class, 'history'])->name('history');
        Route::delete('/{product}',       [StockController::class, 'destroy'])->name('destroy')->middleware('role:owner,admin');
    });

    // Distributions
    Route::prefix('distribution')->name('distribution.')->group(function () {
        Route::get('/',                               [DistributionController::class, 'index'])->name('index');
        Route::get('/create',                         [DistributionController::class, 'create'])->name('create')->middleware('role:owner,admin');
        Route::post('/',                              [DistributionController::class, 'store'])->name('store')->middleware('role:owner,admin');
        Route::get('/{distribution}',                [DistributionController::class, 'show'])->name('show');
        Route::patch('/{distribution}/status',       [DistributionController::class, 'updateStatus'])->name('update-status');
        Route::post('/{distribution}/depart',        [DistributionController::class, 'depart'])->name('depart');        // Berangkat + GPS
        Route::post('/{distribution}/deliver',       [DistributionController::class, 'deliver'])->name('deliver');      // Terkirim + foto
        Route::post('/{distribution}/gps-log',       [DistributionController::class, 'logGps'])->name('gps-log');       // Kirim GPS point
        Route::get('/{distribution}/track-data',     [DistributionController::class, 'trackData'])->name('track-data'); // Live GPS JSON
        Route::delete('/{distribution}',             [DistributionController::class, 'destroy'])->name('destroy')->middleware('role:owner,admin');
    });

    // Stores
    Route::resource('stores', StoreController::class);

    // Users
    Route::prefix('users')->name('users.')->middleware('role:owner,admin')->group(function () {
        Route::get('/',                [UserController::class, 'index'])->name('index');
        Route::get('/create',         [UserController::class, 'create'])->name('create');
        Route::post('/',               [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit',    [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',         [UserController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle',[UserController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{user}',      [UserController::class, 'destroy'])->name('destroy');
    });

    // Branches
    Route::prefix('branches')->name('branches.')->middleware('role:owner')->group(function () {
        Route::get('/',               [BranchController::class, 'index'])->name('index');
        Route::get('/create',        [BranchController::class, 'create'])->name('create');
        Route::post('/',              [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}',      [BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}',      [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}',   [BranchController::class, 'destroy'])->name('destroy');
        Route::post('/assign-user',  [BranchController::class, 'assignUser'])->name('assign-user');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',      [ReportController::class, 'index'])->name('index');
        Route::get('/pdf',   [ReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/excel', [ReportController::class, 'exportExcel'])->name('export-excel');
    });

    // Tracking GPS
    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::get('/',         [TrackingController::class, 'index'])->name('index');
        Route::post('/log-gps', [TrackingController::class, 'logGps'])->name('log-gps');
    });

    // Billing
    Route::prefix('billing')->name('billing.')->middleware('role:owner')->group(function () {
        Route::get('/',        [BillingController::class, 'index'])->name('index');
        Route::post('/upgrade',[BillingController::class, 'upgrade'])->name('upgrade');
    });
});
