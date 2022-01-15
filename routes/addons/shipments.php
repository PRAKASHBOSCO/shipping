<?php  

Route::get('admin/shipments/payment/{shipment_id}','ShipmentController@pay')->name('admin.shipments.pay');
Route::get('shipment/tracking','ShipmentController@track')->name('admin.shipments.track');
Route::get('shipment/tracking/{code?}','ShipmentController@tracking')->name('admin.shipments.tracking');
Route::post('shipment/calc/store','ShipmentController@calcStore')->name('admin.shipments.calc.store');
Route::get('shipments/ajaxed-get-states','ShipmentController@ajaxGetStates')->name('admin.shipments.get-states-ajax');
Route::get('shipments/ajaxed-get-areas','ShipmentController@ajaxGetAreas')->name('admin.shipments.get-areas-ajax');
Route::post('shipments/get-estimation-cost','ShipmentController@ajaxGetEstimationCost')->name('admin.shipments.get-estimation-cost');
Route::post('shipments/get-estimation-delivery', 'ShipmentController@ajaxGetEstimationDelivery')->name('admin.shipments.get-estimation-delivery');
Route::get('shipments/action/confirm_amount/{shipment_id}', 'ShipmentController@getAmountModel')->name('admin.shipments.action.confirm_amount');
Route::post('shipments/export-shipments/','ShipmentController@exportShipments')->name('admin.shipments.export');

Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'user_role:admin|staff']], function(){
    
    Route::get('shipments/settings','ShipmentController@settings')->name('admin.shipments.settings');
    Route::post('shipments/settings/store','ShipmentController@storeSettings')->name('admin.shipments.settings.store');

    Route::get('shipments/settings/fees','ShipmentController@feesSettings')->name('admin.shipments.settings.fees');
    Route::get('shipments/settings/fees-fixed','ShipmentController@feesFixedSettings')->name('admin.shipments.settings.fees.fixed');
    Route::get('shipments/settings/fees-gram','ShipmentController@feesGramSettings')->name('admin.shipments.settings.fees.gram');
    Route::get('shipments/settings/fees-state-to-state','ShipmentController@feesStateToStateSettings')->name('admin.shipments.settings.fees.state-to-state');
    Route::get('shipments/settings/fees-country-to-country','ShipmentController@feesCountryToCountrySettings')->name('admin.shipments.settings.fees.country-to-country');

    Route::get('/shipments/covered_cities/{country_id}','ShipmentController@covered_cities')->name('admin.shipments.covered_cities');
    Route::get('/shipments/covered_countries','ShipmentController@covered_countries')->name('admin.shipments.covered_countries');
    Route::post('/shipments/post_covered_cities/{country_id}','ShipmentController@post_covered_cities')->name('admin.shipments.post_covered_cities');
    Route::post('/shipments/post_covered_countries','ShipmentController@post_covered_countries')->name('admin.shipments.post_covered_countries');
    Route::get('/shipments/config/costs','ShipmentController@config_costs')->name('admin.shipments.config.costs');
    Route::get('/shipments/config/costs/ajax','ShipmentController@ajax_costs_repeter')->name('admin.shipments.config.costs.ajax');
    Route::post('/shipments/config/costs','ShipmentController@post_config_costs')->name('admin.shipments.post.config.costs');
    Route::post('/shipments/config/packages/costs','ShipmentController@post_config_package_costs')->name('admin.shipments.post.config.package.costs');

    Route::get('/reasons','ReasonController@index')->name('admin.reasons');

    Route::resource('costs','CostController',[
        'as' => 'admin'
    ]);
    
    Route::resource('areas','AreaController',[
        'as' => 'admin'
    ]);

    Route::resource('packages','PackagesController',[
        'as' => 'admin'
    ]);
    Route::resource('reasons','ReasonController',[
        'as' => 'admin'
    ]);
    Route::resource('deliveryTime','DeliveryTimesController',[
        'as' => 'admin'
    ]);
    Route::resource('deliveryWeightConfig', 'DeliveryWeightConfigsController', [
        'as' => 'admin'
    ]);
    Route::resource('pickupWeightConfig', 'PickupWeightConfigsController', [
        'as' => 'admin'
    ]);
    Route::resource('codConfig', 'CodConfigsController', [
        'as' => 'admin'
    ]);
    Route::resource('dimensionConfig', 'DimensionConfigsController', [
        'as' => 'admin'
    ]);
    Route::get('reasons/delete/{reason}','ReasonController@destroy')->name('admin.reasons.delete-reason');

    Route::get('packages/delete/{package}','PackagesController@destroy')->name('admin.packages.delete-package');

    Route::get('deliveryTime/delete/{deliveryTime}','DeliveryTimesController@destroy')->name('admin.deliveryTimes.delete-deliveryTime');
    
    Route::get('deliveryWeightConfig/delete/{deliveryWeightConfig}', 'DeliveryWeightConfigsController@destroy')->name('admin.deliveryWeightConfigs.delete-deliveryWeightConfig');
    
    Route::get('pickupWeightConfig/delete/{pickupWeightConfig}', 'PickupWeightConfigsController@destroy')->name('admin.pickupWeightConfigs.delete-pickupWeightConfig');
    
    Route::get('codConfig/delete/{codConfig}', 'CodConfigsController@destroy')->name('admin.codConfigs.delete-codConfig');
    
    Route::get('dimensionConfig/delete/{dimensionConfig}', 'DimensionConfigsController@destroy')->name('admin.dimensionConfigs.delete-dimensionConfig');

    Route::get('areas/delete/{area}','AreaController@destroy')->name('admin.areas.delete-area');
    
});


Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'user_role:admin|staff|captain|branch']], function(){
    Route::get('shipments/barcode-scanner','ShipmentController@BarcodeScanner')->name('admin.shipments.barcode.scanner');
    Route::post('shipments/change-status-by-barcode','ShipmentController@ChangeStatusByBarcode')->name('admin.shipments.change.status.by.barcode');
});
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'user_role:customer']], function(){
    Route::get('import', 'ShipmentController@import')->name('admin.shipments.import');
	Route::post('import/parse', 'ShipmentController@parseImport')->name('admin.shipments.import_parse');
    Route::get('shipments/add-shipment-api','ShipmentController@addShipmentByApi')->name('admin.shipments.add.by.api');
});

Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'user_role:admin|staff|customer|branch']], function(){

	//Update Routes
    Route::get('shipments/print/{shipment}/{type}','ShipmentController@print')->name('admin.shipments.print');

    Route::get('shipments/ajaxed-get-addressess','ShipmentController@ajaxGetAddressess')->name('admin.shipments.get-addressess-ajax');
    Route::get('shipments/generate-token','ShipmentController@ajaxGgenerateToken')->name('admin.shipments.generate-token');
    
    Route::post('shipments/action/{to}','ShipmentController@change')->name('admin.shipments.action');
    Route::post('shipments/action/pickup_mission/{type}','ShipmentController@createPickupMission')->name('admin.shipments.action.create.pickup.mission');
    Route::post('shipments/action/supply_mission/{type}','ShipmentController@createSupplyMission')->name('admin.shipments.action.create.supply.mission');
    Route::post('shipments/action/delivery_mission/{type}','ShipmentController@createDeliveryMission')->name('admin.shipments.action.create.delivery.mission');
    Route::post('shipments/action/return_mission/{type}','ShipmentController@createReturnMission')->name('admin.shipments.action.create.return.mission');
    Route::post('shipments/action/transfer_mission/{type}','ShipmentController@createTransferMission')->name('admin.shipments.action.create.transfer.mission');
       
    Route::get('shipments/shipments-report','ShipmentController@shipmentsReport')->name('admin.shipments.report');
    Route::post('shipments/shipments-report/results','ShipmentController@exportShipmentsReport')->name('admin.shipments.post.report');
    Route::post('shipments/print/stickers','ShipmentController@printStickers')->name('admin.shipments.print.stickers');
    
    Route::resource('shipments','ShipmentController',[
        'as' => 'admin',
        'except' => ['show']
    ]);
    
    Route::get('shipments/delete/{shipment}','ShipmentController@destroy')->name('admin.shipments.delete-shipment');
    Route::patch('shipments/update/{shipment}','ShipmentController@update')->name('admin.shipments.update-shipment');

    //Auto Route Creation Based on Statuses Function in Shipment Model
    foreach(\App\Shipment::status_info() as $item)
    {
        $params ='';
        if(isset($item['optional_params']))
        {
            $params = $item['optional_params'];
        }
        Route::get('shipments/'.$item['route_url'].'/{status}'.$params,'ShipmentController@statusIndex')
        ->name($item['route_name']);
    }
});

Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'user_role:admin|staff|customer|branch|captain']], function(){
    Route::get('shipments/{shipment}','ShipmentController@show')->name('admin.shipments.show');
});

Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'user_role:admin|staff|captain|branch']], function(){
    Route::post('shipments/remove-shipment-from-mission','ShipmentController@removeShipmentFromMission')->name('admin.shipments.delete-shipment-from-mission');
});