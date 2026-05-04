<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CounterpartyController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\HotelBookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TransportController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VisaController;
use Illuminate\Support\Facades\Route;

// Public endpoints (no auth required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/countries', [CountriesController::class, 'index']);
Route::get('/destinations/list', [DestinationController::class, 'index']); // ?all_list=1

// PDF download — supports ?token= query param for direct browser access
Route::get('/tours/{tour}/pdf', function (\Illuminate\Http\Request $request, \App\Models\Tour $tour) {
    $tokenString = $request->query('token') ?? $request->bearerToken();
    if (!$tokenString) {
        return response()->json(['success' => false, 'message' => 'Tizimga kiring.'], 401);
    }
    $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
    if (!$tokenModel) {
        return response()->json(['success' => false, 'message' => 'Token yaroqsiz.'], 401);
    }
    $service = new \App\Services\TourPdfService();
    $pdf = $service->generateTourDocument($tour->id);
    return $pdf->download("tour-{$tour->tour_code}.pdf");
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'changePassword']);

    Route::get('/dashboard', [DashboardController::class, 'getData']);

    Route::get('/tours/calendar', [TourController::class, 'timeline']);
    Route::post('/tours/{tour}/confirm', [TourController::class, 'confirm']);
    Route::post('/tours/{tour}/duplicate', [TourController::class, 'duplicate']);
    Route::apiResource('tours', TourController::class);

    Route::put('/hotel-bookings/{hotelBooking}/status', [HotelBookingController::class, 'updateStatus']);
    Route::get('/hotel-bookings/statistics', [HotelBookingController::class, 'statistics']);
    Route::apiResource('hotel-bookings', HotelBookingController::class);

    Route::apiResource('hotels', HotelController::class);

    Route::apiResource('transports', TransportController::class);

    Route::put('/visas/{visa}/process', [VisaController::class, 'process']);
    Route::get('/visas/expiring', [VisaController::class, 'expiringVisas']);
    Route::apiResource('visas', VisaController::class);

    Route::get('/counterparties/by-type/{type}', [CounterpartyController::class, 'getByType']);
    Route::apiResource('counterparties', CounterpartyController::class);

    Route::get('/destinations/countries', [DestinationController::class, 'countries']);
    Route::apiResource('destinations', DestinationController::class);

    Route::put('/offers/{offer}/accept', [OfferController::class, 'accept']);
    Route::put('/offers/{offer}/reject', [OfferController::class, 'reject']);
    Route::post('/offers/{offer}/convert', [OfferController::class, 'convertToTour']);
    Route::apiResource('offers', OfferController::class);

    Route::prefix('reports')->group(function () {
        Route::get('/financial', [ReportController::class, 'financialSummary']);
        Route::get('/tours', [ReportController::class, 'tourStatistics']);
        Route::get('/hotels', [ReportController::class, 'hotelOccupancy']);
        Route::get('/visas', [ReportController::class, 'visaReport']);
        Route::get('/counterparties', [ReportController::class, 'counterpartyReport']);
        Route::get('/staff', [ReportController::class, 'staffPerformance']);
        Route::get('/export/{type}', [ReportController::class, 'export']);
    });

    Route::get('/users/roles', [UserController::class, 'roles']);
    Route::put('/users/{user}/role', [UserController::class, 'assignRole']);
    Route::apiResource('users', UserController::class);

    Route::apiResource('branches', BranchController::class);
});
