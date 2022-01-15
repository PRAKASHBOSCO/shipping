<?php
/*
|--------------------------------------------------------------------------
| Update Routes
|--------------------------------------------------------------------------
|
| This route is responsible for handling the update process
|
|
|
*/
Route::get('/', 'UpdateController@step0');
Route::get('/updates/step1', 'UpdateController@step1')->name('update.step1');
Route::get('/updates/step2', 'UpdateController@step2')->name('update.step2');