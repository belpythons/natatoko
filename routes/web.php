<?php
/**
 * Created/Modified by: Nata Toko Team
 * Feature: Core & Admin - Konfigurasi routing aplikasi
 */
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Pos;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
 |--------------------------------------------------------------------------
 | Web Routes
 |--------------------------------------------------------------------------
 */

// First-time setup (accessible globally via CheckSetup middleware)
Route::get('/setup', [SetupController::class , 'index'])->name('setup');
Route::post('/setup', [SetupController::class , 'store']);

// Redirect root based on setup status
Route::get('/', function () {
    if (\App\Models\User::count() === 0) {
        return redirect()->route('setup');
    }
    return redirect()->route('login');
});

// General authenticated routes (Admin)
Route::middleware('auth')->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
            return redirect()->route('admin.dashboard');
        }
        )->name('dashboard');

        // Profile routes
        Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
        Route::patch('/profile/pin', [ProfileController::class , 'updatePin'])->name('profile.pin.update');
        Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');
    });

/*
 |--------------------------------------------------------------------------
 | Admin Routes
 |--------------------------------------------------------------------------
 */
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [Admin\DashboardController::class , 'index'])->name('dashboard');

    // Reports
    Route::get('/reports/daily', [Admin\DashboardController::class , 'downloadDailyReport'])->name('reports.daily');
    Route::get('/reports/session/{session}', [Admin\DashboardController::class , 'downloadSessionReport'])->name('reports.session');

    // Partners CRUD
    Route::resource('partners', Admin\PartnerController::class);

    // Box Templates CRUD
    Route::resource('box-templates', Admin\BoxTemplateController::class);


    // Activity Logs
    Route::get('activity-logs', [Admin\ActivityLogController::class , 'index'])->name('activity-logs.index');
});

/*
 |--------------------------------------------------------------------------
 | POS Auth Routes
 |--------------------------------------------------------------------------
 */
Route::post('/pos/authenticate', [Pos\AuthController::class , 'login'])->name('pos.authenticate');

/*
 |--------------------------------------------------------------------------
 | POS Routes
 |--------------------------------------------------------------------------
 */
Route::middleware('pos_auth')->prefix('pos')->name('pos.')->group(function () {
    Route::post('/logout', [Pos\AuthController::class , 'logout'])->name('logout');

    // Shop Session
    Route::get('/open', [Pos\ShopSessionController::class , 'create'])->name('session.create');
    Route::post('/open', [Pos\ShopSessionController::class , 'store'])->name('session.store');
    Route::get('/close', [Pos\ShopSessionController::class , 'showClose'])->name('session.close');
    Route::post('/close', [Pos\ShopSessionController::class , 'close'])->name('session.close.store');
    Route::get('/report/{session}', [Pos\ShopSessionController::class , 'downloadReport'])->name('session.report');

    // Consignment Management
    Route::get('/consignment', [Pos\ConsignmentController::class , 'index'])->name('consignment.index');
    Route::post('/consignment', [Pos\ConsignmentController::class , 'store'])->name('consignment.store');
    Route::patch('/consignment/{consignment}/sold', [Pos\ConsignmentController::class , 'updateSold'])->name('consignment.sold');
    Route::post('/consignment/bulk-update', [Pos\ConsignmentController::class , 'bulkUpdateSold'])->name('consignment.bulk-update');

    // Box Order
    Route::get('/box', [Pos\BoxOrderController::class , 'index'])->name('box.index');
    Route::get('/box/create/{template?}', [Pos\BoxOrderController::class , 'create'])->name('box.create');
    Route::post('/box', [Pos\BoxOrderController::class , 'store'])->name('box.store');
    Route::post('/box/{order}/proof', [Pos\BoxOrderController::class , 'uploadProof'])->name('box.proof');
    Route::patch('/box/{order}/status', [Pos\BoxOrderController::class , 'updateStatus'])->name('box.status');
    Route::get('/box/{order}/receipt', [Pos\BoxOrderController::class , 'downloadReceipt'])->name('box.receipt');

    // Mayar QRIS Payment
    Route::post('/box/{order}/mayar-payment', [Pos\BoxOrderController::class , 'createMayarPayment'])->name('box.mayar-payment');
    Route::get('/box/{order}/payment-status', [Pos\BoxOrderController::class , 'checkPaymentStatus'])->name('box.payment-status');
});

/*
 |--------------------------------------------------------------------------
 | Webhook Routes (No Auth / No CSRF)
 |--------------------------------------------------------------------------
 */
Route::post('/webhook/mayar', [Pos\BoxOrderController::class , 'handleMayarWebhook'])->name('webhook.mayar');

require __DIR__ . '/auth.php';