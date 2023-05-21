<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GithubAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/prs', [GithubAPIController::class, 'getPRs']);
Route::get('/prs-with-review', [GithubAPIController::class, 'getPRsWithReview']);
Route::get('/prs-with-success', [GithubAPIController::class, 'getPRsWithSuccess']);
