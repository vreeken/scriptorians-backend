<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::prefix('api')->group(function () {
	Route::post('auth/login', 								'AuthController@postLogin');
	Route::post('auth/register', 							'AuthController@postRegister');
	Route::get('auth/logout',								'AuthController@getLogout');

	Route::post('comments/get', 						'CommentController@getComments');


	// auth.both is custom middleware that checks for either jwt OR session based login. I don't think we'll be using jwt, though
	Route::group(['middleware' => 'auth.both'], function () {
		Route::get('account-data',						'UserController@getAccountData');

		Route::post('comments/new',						'CommentController@postComment');
		Route::post('comments/vote',					'CommentController@vote');
	});
});


Route::get('{any}', 'MainController@getSpaPage')->where('any', '.*');

//Route::get('', 'MainController@getSpaPage');
//Route::get('/scriptures/{volume?}/{book?}/{chapter?}', 'MainController@getSpaPage');
/*
Route::get('', function() {
	return view('spa_main');
});

Route::get('/scriptures/{volume?}/{book?}/{chapter?}', function() {
	return view('spa_main');
});
*/
