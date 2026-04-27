<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\GeneratedReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCreateUserController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\RejectTeacherReasonController;
use App\Http\Controllers\RequestMeetingController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\BDEController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Support\Facades\Route;

$corsMiddleware = [CorsMiddleware::class];
$authMiddleware = ['web', CorsMiddleware::class];

// Public routes (no authentication required)
Route::post('/login', [AuthController::class, 'login'])->middleware($corsMiddleware);
Route::post('/logout', [AuthController::class, 'logout'])->middleware($authMiddleware);

// API v1 routes (protected)
Route::prefix('v1')->middleware($authMiddleware)->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::get('/generated-reports', [GeneratedReportController::class, 'index']);
    Route::post('/generated-reports', [GeneratedReportController::class, 'store']);
    Route::get('/generated-reports/{generatedReport}', [GeneratedReportController::class, 'show']);
});

// Admin routes (protected)
Route::prefix('admin')->middleware($authMiddleware)->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index']);
    Route::get('/reports', [AdminController::class, 'reports']);
    Route::get('/meetings', [AdminController::class, 'meetings']);
    Route::get('/request-meetings', [AdminController::class, 'requestMeetings']);
    Route::post('/create-user', [AdminCreateUserController::class, 'store']);

    // Categories management
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});

// Teacher routes (protected)
Route::prefix('teacher')->middleware($authMiddleware)->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard']);
    Route::get('/reports', [TeacherController::class, 'reports']);
    Route::get('/users', [TeacherController::class, 'users']);
    Route::post('/reports/{report}/resolve', [TeacherController::class, 'resolve']);
    Route::post('/reports/{report}/reject', [TeacherController::class, 'reject']);
});

// Student routes (protected)
Route::prefix('student')->middleware($authMiddleware)->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/my-reports', [StudentController::class, 'myReports']);
    Route::get('/categories', [StudentController::class, 'createReportForm']);
    Route::post('/create-report', [StudentController::class, 'storeReport']);
});

// BDE routes (protected)
Route::prefix('bde')->middleware($authMiddleware)->group(function () {
    Route::get('/dashboard', [BDEController::class, 'dashboard']);
    Route::get('/reports', [BDEController::class, 'reports']);
    Route::post('/approve/{id}', [BDEController::class, 'approve']);
    Route::post('/deny/{id}', [BDEController::class, 'deny']);
});

// Request meetings routes (protected)
Route::prefix('request-meetings')->middleware($authMiddleware)->group(function () {
    Route::get('/', [RequestMeetingController::class, 'index']);
    Route::post('/', [RequestMeetingController::class, 'store']);
    Route::get('/{id}', [RequestMeetingController::class, 'show']);
});

// Meetings routes (protected)
Route::prefix('meetings')->middleware($authMiddleware)->group(function () {
    Route::get('/', [MeetingController::class, 'index']);
    Route::post('/', [MeetingController::class, 'store']);
    Route::get('/{id}', [MeetingController::class, 'show']);
    Route::post('/reject/{id}', [MeetingController::class, 'reject']);
});

// Reject teacher reasons routes (protected)
Route::prefix('reject-teacher-reasons')->middleware($authMiddleware)->group(function () {
    Route::get('/', [RejectTeacherReasonController::class, 'index']);
    Route::post('/', [RejectTeacherReasonController::class, 'store']);
    Route::get('/{id}', [RejectTeacherReasonController::class, 'show']);
    Route::put('/{id}', [RejectTeacherReasonController::class, 'update']);
    Route::delete('/{id}', [RejectTeacherReasonController::class, 'destroy']);
    Route::get('/teacher/{teacherId}', [RejectTeacherReasonController::class, 'byTeacher']);
    Route::get('/generated-report/{generatedReportId}', [RejectTeacherReasonController::class, 'byGeneratedReport']);
});
