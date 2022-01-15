<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CodConfig;
use App\Http\Helpers\UserRegistrationHelper;
use DB;
use App\User;
use App\Http\Helpers\ApiHelper;

class CodConfigsController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $CodConfigs = CodConfig::paginate(15);
        return view('backend.shipments.index-codConfigs', compact('CodConfigs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('backend.shipments.create-codConfigs');
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
            $check = CodConfig::where('from_amount', $_POST['CodConfig']['from_amount'])->first();
            if ($check != null) {
                flash(translate("This From Amount is created before"))->error();
                return back();
            }
            $model = new CodConfig();


            $model->fill($_POST['CodConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }

            DB::commit();
            flash(translate("COD Config added successfully"))->success();
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
        $CodConfig = CodConfig::where('id', $id)->first();
        if ($CodConfig != null) {
            return view('backend.shipments.edit-codConfigs', compact('CodConfig'));
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
    public function update(Request $request, $codConfig) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        try {
            DB::beginTransaction();
            $check = CodConfig::where('from_amount', $_POST['CodConfig']['from_amount'])->whereNotIn('id', [$codConfig])->first();
            if ($check != null) {
                flash(translate("This COD From Amount is created before"))->error();
                return back();
            }
            $model = CodConfig::find($codConfig);


            $model->fill($_POST['CodConfig']);

            if (!$model->save()) {
                throw new \Exception();
            }


            DB::commit();
            flash(translate("COD Config updated successfully"))->success();
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
    public function destroy($codConfig) {

        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = CodConfig::findOrFail($codConfig);

        if ($model->delete()) {
            flash(translate('COD Config has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

}
