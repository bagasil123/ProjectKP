<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK (Tidak Perlu Login/Token) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password/validate', [ForgotPasswordController::class, 'validateStep']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/clock-in', [AbsensiController::class, 'clockIn']);


// --- RUTE TERPROTEKSI (Wajib Login & Mengirim Token) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    Route::get('/products-by-kelompok/{kelompok_id}', function($kelompok_id) {
        $products = App\Models\Inventory\Dtproduk::where('kelompok_id', $kelompok_id)
            ->select('id', 'kode_produk', 'nama_produk', 'harga_beli')
            ->get();

        return response()->json($products);
    });
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint Absensi
    Route::post('/absensi/clock-in', [AbsensiController::class, 'clockIn']);
    Route::post('/absensi/clock-out', [AbsensiController::class, 'clockOut']);
    Route::get('/absensi/status-hari-ini', [AbsensiController::class, 'getTodayStatus']);
    Route::get('/absensi/history', [AbsensiController::class, 'getHistory']);
    Route::post('/leave/submit', [LeaveRequestController::class, 'store']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::get('/attendance-overview', [AbsensiController::class, 'getAttendanceOverview']);

});
