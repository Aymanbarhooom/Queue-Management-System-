<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ManagerDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


// ─── Authenticated ──────────────────────────────────────────────────────────── 
Route::middleware('auth:sanctum')->group(function () {

// auth routes
 Route::post('/logout', [AuthController::class, 'logout']);
 Route::get('/me',      [AuthController::class, 'me']);
 Route::post('/fcm_token', [AuthController::class, 'updateFcmToken']);

 Route::get('/home', [HomeController::class, 'index']);

 // Admin Routes
 Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
 Route::apiResource('users', AdminController::class)->only(['index', 'show']);
 Route::post('users', [AdminController::class, 'addManager']);
 Route::post('deposit', [AdminController::class, 'diposit']);

 // Manager Routes
Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index']);
Route::get('managers/{business}/employees', [ManagerController::class, 'getEmployees']);
Route::post('managers/employees', [ManagerController::class, 'addEmployee']);
Route::put('managers/{business}/employees/{employee}', [ManagerController::class, 'updateEmployee']);
Route::delete('managers/{business}/employees/{employee}', [ManagerController::class, 'deleteEmployee']);


 //categories routes
 Route::apiResource('categories', CategoryController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

 //businesses routes
 Route::get('businesses/top-rated', [BusinessController::class, 'topRated']);
 Route::apiResource('businesses', BusinessController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
 Route::get('businesses/{category}/category', [BusinessController::class, 'getBusinessesOnCategory']);

 //services routes
 Route::apiResource('services', ServiceController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

 //queues routes
 Route::apiResource('queues', QueueController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
Route::put('queues/{queue}/tickets/{ticket}/complete', [QueueController::class, 'complete']);
Route::put('queues/{queue}/update-congestion', [QueueController::class, 'updateQueueCongestion']);

//tickets routes
Route::post('queue/book', [TicketController::class, 'book']);
Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel']);
Route::post('tickets/{ticket}/start-handling', [TicketController::class, 'startHandling']);
Route::post('tickets/{ticket}/complete', [TicketController::class, 'complete']);
Route::post('tickets/{ticket}/no-show', [TicketController::class, 'noShow']);
Route::post('tickets/my-active', [TicketController::class, 'myActiveTickets']);
Route::post('tickets/my-history', [TicketController::class, 'myHistory']);
Route::get('tickets/business/{business}', [TicketController::class, 'getBusinessTickets']);

//admin routes
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
Route::post('/update-profile-image', [AuthController::class, 'updateImage']);
Route::post('/manager/dashboard', [ManagerDashboardController::class, 'index']);
Route::post('/update-profile', [AuthController::class, 'updateProfile']);
Route::get('/services/search', [ServiceController::class, 'serviceSearch']);

});