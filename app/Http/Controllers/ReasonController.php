<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reason;
use DB;

class ReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reasons = Reason::where("type","remove_shipment_from_mission")->paginate(15);
        return view('backend.shipments.index-reasons', compact('reasons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.shipments.create-reason');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        try{	
			DB::beginTransaction();
            $check = Reason::where('name',$_POST['Reason']['name'])->first();
            if($check != null)
            {
                flash(translate("This Reason is created before"))->error();
                return back();
            }
			$model = new Reason();
			
			
			$model->fill($_POST['Reason']);
			if (!$model->save()){
				throw new \Exception();
			}
			
			DB::commit();
            flash(translate("Reason added successfully"))->success();
            return back();
		}catch(\Exception $e){
			DB::rollback();
			print_r($e->getMessage());
			exit;
			
			flash(translate("Error"))->error();
            return back();
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reason = Reason::where('id', $id)->first();
        if($reason != null){
            return view('backend.shipments.edit-reason',compact('reason'));
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
    public function update(Request $request, $reason)
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        try{	
			DB::beginTransaction();
            $check = Reason::where('name',$_POST['Reason']['name'])->whereNotIn('id',[$reason])->first();
            if($check != null)
            {
                flash(translate("This Reason is created before"))->error();
                return back();
            }
			$model = Reason::find($reason);
			
			
			$model->fill($_POST['Reason']);
		
			if (!$model->save()){
				throw new \Exception();
			}
			
			
			DB::commit();
            flash(translate("Reason updated successfully"))->success();
            return redirect()->route('admin.reasons.index');
		}catch(\Exception $e){
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
    public function destroy($reason)
    {
   
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = Reason::findOrFail($reason);
        
        if($model->delete()){
            flash(translate('Reason has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }
}
