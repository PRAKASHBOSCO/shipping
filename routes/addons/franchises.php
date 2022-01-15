<?php 
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
	//Update Routes
    Route::resource('franchises','FranchiseController',[
        'as' => 'admin'
    ]);
    Route::get('franchises/delete/{branch}','FranchiseController@destroy')->name('admin.franchises.delete-branch');

});