<?php
/*
|--------------------------------------------------------------------------
| Update Routes
|--------------------------------------------------------------------------
|
| This route is responsible for handling the intallation process
|
|
|
*/
Route::get('/', 'UpdateController@step0');
Route::get('/', 'UpdateController@step1')->name('update.step1');
