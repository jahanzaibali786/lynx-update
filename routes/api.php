<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ResultApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'ApiController@login');

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('get-projects', [ApiController::class, 'getProjects']);
    Route::post('add-tracker', [ApiController::class, 'addTracker']);
    Route::post('stop-tracker', [ApiController::class, 'stopTracker']);
    Route::post('upload-photos', [ApiController::class, 'uploadImage']);
    //results api's

	Route::get('get-students', [ResultApiController::class, 'getStudents']);
	Route::get('get-student/{id}', [ResultApiController::class, 'getStudent']);
	Route::get('get-classes', [ResultApiController::class, 'getClasses']);
	Route::get('get-branches', [ResultApiController::class, 'getBranches']);
	Route::get('get-branch/{id}', [ResultApiController::class, 'getBranch']);
	Route::get('get-employees-by-branch/{branch_id}', [ResultApiController::class, 'getEmployeesByBranch']);
	Route::get('get-sections', [ResultApiController::class, 'getSections']);
	Route::get('get-class-section', [ResultApiController::class, 'getClassSection']);
});