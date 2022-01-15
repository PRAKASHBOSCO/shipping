<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PickupWeightConfig;
use App\Http\Helpers\UserRegistrationHelper;
use DB;
use App\User;
use App\Http\Helpers\ApiHelper;

class PickupWeightConfigsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $PickupWeightConfigs = PickupWeightConfig::paginate(15);
        return view('backend.shipments.index-pickupWeightConfigs', compact('PickupWeightConfigs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('backend.shipments.create-pickupWeightConfigs');
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
            $check = PickupWeightConfig::where('from_weight', $_POST['PickupWeightConfig']['from_weight'])->first();
            if ($check != null) {
                flash(translate("This From Weight is created before"))->error();
                return back();
            }
            $model = new PickupWeightConfig();


            $model->fill($_POST['PickupWeightConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }

            DB::commit();
            flash(translate("Pickup Weight Config added successfully"))->success();
            return back();
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function ajaxGetPickupTimes(Request $request) {
        $apihelper = new ApiHelper();
        $user = $apihelper->checkUser($request);

        if ($user) {
            $PickupTimes = PickupTime::get();
            return response()->json($PickupTimes);
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
        $PickupWeightConfig = PickupWeightConfig::where('id', $id)->first();
        if ($PickupWeightConfig != null) {
            return view('backend.shipments.edit-pickupWeightConfigs', compact('PickupWeightConfig'));
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
    public function update(Request $request, $pickupWeightConfig) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        try {
            DB::beginTransaction();
            $check = PickupWeightConfig::where('from_weight', $_POST['PickupWeightConfig']['from_weight'])->whereNotIn('id', [$pickupWeightConfig])->first();
            if ($check != null) {
                flash(translate("This Pickup From Weight is created before"))->error();
                return back();
            }
            $model = PickupWeightConfig::find($pickupWeightConfig);


            $model->fill($_POST['PickupWeightConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }


            DB::commit();
            flash(translate("Pickup Weight Config updated successfully"))->success();
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
    public function destroy($pickupWeightConfig) {

        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = PickupWeightConfig::findOrFail($pickupWeightConfig);

        if ($model->delete()) {
            flash(translate('Pickup Weight Config has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

}
