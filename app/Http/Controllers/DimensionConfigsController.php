<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DimensionConfig;
use App\Http\Helpers\UserRegistrationHelper;
use DB;
use App\User;
use App\Http\Helpers\ApiHelper;

class DimensionConfigsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $DimensionConfigs = DimensionConfig::paginate(15);
        return view('backend.shipments.index-dimensionConfigs', compact('DimensionConfigs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('backend.shipments.create-dimensionConfigs');
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
            $check = DimensionConfig::where('from_weight', $_POST['DimensionConfig']['from_weight'])->first();
            if ($check != null) {
                flash(translate("This From Weight is created before"))->error();
                return back();
            }
            $model = new DimensionConfig();


            $model->fill($_POST['DimensionConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }

            DB::commit();
            flash(translate("Dimension Config added successfully"))->success();
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
        $DimensionConfig = DimensionConfig::where('id', $id)->first();
        if ($DimensionConfig != null) {
            return view('backend.shipments.edit-dimensionConfigs', compact('DimensionConfig'));
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
    public function update(Request $request, $dimensionConfig) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        try {
            DB::beginTransaction();
            $check = DimensionConfig::where('from_weight', $_POST['DimensionConfig']['from_weight'])->whereNotIn('id', [$dimensionConfig])->first();
            if ($check != null) {
                flash(translate("This Dimension From Weight is created before"))->error();
                return back();
            }
            $model = DimensionConfig::find($dimensionConfig);


            $model->fill($_POST['DimensionConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }


            DB::commit();
            flash(translate("Dimension Config updated successfully"))->success();
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
    public function destroy($dimensionConfig) {

        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = DimensionConfig::findOrFail($dimensionConfig);

        if ($model->delete()) {
            flash(translate('Dimension Config has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

}
