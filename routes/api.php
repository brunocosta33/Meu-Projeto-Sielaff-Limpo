<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/backoffice/imageDelete/{id}', [
	'uses' => 'ImagesController@imageDelete',
	'middleware' => ['auth'],
	'as' => 'backoffice.imageDelete'
]);

Route::get('/backoffice/imageStar/{id}/{item}', [
	'uses' => 'ImagesController@imageStar',
	'middleware' => ['auth'],
	'as' => 'backoffice.imageStar'
]);