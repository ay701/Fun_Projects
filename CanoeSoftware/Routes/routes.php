<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/canoe', "CanoeController@index");

Route::group(['prefix' => 'canoe'], function () {
    Route::get('client/{id}', "CanoeController@getClient");
    Route::get('cashflows', "CanoeController@cashflows");
    Route::get('funds-by-type/{type}', "CanoeController@fundsByType");
    Route::get('investments/{clientid}/{fundid}', "CanoeController@getInvestments");
    Route::post('calculate-investment', "CanoeController@calculateInvestment");
    Route::post('add-cashflow', "CanoeController@addCashFlow");
});
































Route::get('/client/{id}', "CanoeController@getClient");
Route::get('/', function() { return \Redirect::to("auth/login"); });

Route::group(['prefix' => 'dashboard'], function () {
	Route::get('/', "DashboardController@index");
	Route::controller('users', "UserController");
    Route::get('users', "UserController@getIndex")->middleware('userredirect:10');
    Route::get('users/edit/{id}', "UserController@getEdit")->middleware('userredirect:10');
	Route::controller('lists', "ListController");
	Route::get('transmissions/csv', "TransmissionController@getCsv");
	Route::post('transmission/csv', "Transmissioncontroller@postCsv");
    Route::get('transmissions/contacts-csv/{id}', "TransmissionController@getContactsCsv")->middleware('userredirect:2');
	Route::controller('transmissions', "TransmissionController");
	Route::controller('contacts', "ContactController");
    Route::get('contacts/advanced-search-csv/{id}', "ContactController@getAdvancedSearchCsv")->middleware('userredirect:2');
    Route::post('contact/export', "ContactController@exportContactData");
    Route::post('contact/randomize', "ContactController@randomizeContactData");
    Route::get('contacts/copy-list-contacts', "ContactController@getCopyListContacts")->middleware('userredirect:10');
    Route::controller('statistics', "StatisticsController");
	Route::controller('sparkpost', "SparkpostController");
	Route::controller('logs', "LogsController");
	Route::controller('settings', "SettingsController");

	Route::group(['prefix' => 'eloqua'], function () {
        Route::get('contact-fields', "EloquaController@getContactFields");
        Route::get('content-section', "EloquaController@getContentSection");
        Route::get('custom-objects', "EloquaController@getCustomObjects");
        Route::get('dynamic-content', "EloquaController@getDynamicContent");
        Route::get('dynamic-content-folders', "EloquaController@getDynamicContentFolders");
        Route::get('shared-content', "EloquaController@getContentSection");
        Route::get('lists', "EloquaController@getLists");
        Route::get('emails', "EloquaController@getEmails");
        Route::get('email-folders', "EloquaController@getEmailFolders");
        Route::get('contacts', "EloquaController@getContacts");
        Route::get('contact/{email}', "EloquaController@getContact");
    });
});

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
