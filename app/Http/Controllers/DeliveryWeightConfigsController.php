<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DeliveryWeightConfig;
use App\Http\Helpers\UserRegistrationHelper;
use DB;
use App\User;
use App\Http\Helpers\ApiHelper;

class DeliveryWeightConfigsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $DeliveryWeightConfigs = DeliveryWeightConfig::paginate(15);
        return view('backend.shipments.index-deliveryWeightConfigs', compact('DeliveryWeightConfigs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('backend.shipments.create-deliveryWeightConfigs');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try {
            DB::beginTransaction();
            $check = DeliveryWeightConfig::where('from_weight', $_POST['DeliveryWeightConfig']['from_weight'])->first();
            if ($check != null) {
                flash(translate("This From Weight is created before"))->error();
                return back();
            }
            $model = new DeliveryWeightConfig();


            $model->fill($_POST['DeliveryWeightConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }

            DB::commit();
            flash(translate("Delivery Weight Config added successfully"))->success();
            return back();
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function ajaxGetDeliveryTimes(Request $request) {
        $apihelper = new ApiHelper();
        $user = $apihelper->checkUser($request);

        if ($user) {
            $DeliveryTimes = DeliveryTime::get();
            return response()->json($DeliveryTimes);
        } else {
            return response()->json('Not Authorized');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {

        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $DeliveryWeightConfig = DeliveryWeightConfig::where('id', $id)->first();
        if ($DeliveryWeightConfig != null) {
            return view('backend.shipments.edit-deliveryWeightConfigs', compact('DeliveryWeightConfig'));
        }
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $deliveryWeightConfig) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        try {
            DB::beginTransaction();
            $check = DeliveryWeightConfig::where('from_weight', $_POST['DeliveryWeightConfig']['from_weight'])->whereNotIn('id', [$deliveryWeightConfig])->first();
            if ($check != null) {
                flash(translate("This Delivery From Weight is created before"))->error();
                return back();
            }
            $model = DeliveryWeightConfig::find($deliveryWeightConfig);


            $model->fill($_POST['DeliveryWeightConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }


            DB::commit();
            flash(translate("Delivery Weight Config updated successfully"))->success();
            return back();
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($deliveryWeightConfig) {

        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = DeliveryWeightConfig::findOrFail($deliveryWeightConfig);

        if ($model->delete()) {
            flash(translate('Delivery Weight Config has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

}
