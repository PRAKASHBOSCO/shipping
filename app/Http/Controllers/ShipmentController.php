<?php

namespace App\Http\Controllers;

use Auth;
use App\Area;
use App\Branch;
use App\Client;
use App\Cost;
use App\User;
use App\Staff;
use App\Payment;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ShipmentActionHelper;
use App\Http\Helpers\StatusManagerHelper;
use App\Http\Helpers\TransactionHelper;
use App\Mission;
use App\Models\Country;
use App\Package;
use App\PackageShipment;
use App\Shipment;
use App\ShipmentMission;
use App\ShipmentSetting;
use App\Http\Helpers\MissionPRNG;
use App\Http\Helpers\ShipmentPRNG;
use Excel;
use App\BusinessSetting;
use App\State;
use App\Transaction;
use App\ShipmentReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;
use function Psy\sh;
use App\Events\CreateMission;
use App\Events\AddShipment;
use App\Events\UpdateShipment;
use App\Events\UpdateMission;
use App\Events\ShipmentAction;
use App\AddressClient;
use App\Http\Helpers\UserRegistrationHelper;
use App\Http\Controllers\ClientController;
use Carbon\Carbon;
use App\Exports\ShipmentsExportExcel;
use App\DeliveryTime;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function search($request, $shipments){

        $auth_user = Auth::user();
        if(isset($auth_user))
        {
            if($auth_user->user_type == 'customer'){
                $shipments = $shipments->where('client_id', Auth::user()->userClient->client_id);
            }elseif($auth_user->user_type == 'branch'){
                $shipments = $shipments->where('branch_id', Auth::user()->userBranch->branch_id);
            } elseif ($auth_user->user_type == 'staff') {
                $staff = Staff::where('user_id', Auth::user()->id)->where('role_id', 3)->first();
                if ($staff) {
                    $shipments = $shipments->where('branch_id', $staff->branch_id);
                }
            }
        }

        if (isset($request) && !empty($request)) {
            if (isset($request->code) && !empty($request->code)) {
                $shipments = $shipments->where('code', $request->code);
            }
            if (isset($request->client_id) && !empty($request->client_id)) {
                $shipments = $shipments->where('client_id', $request->client_id);
            }
            if (isset($request->branch_id) && !empty($request->branch_id)) {
                $shipments = $shipments->where('branch_id', $request->branch_id);
            }
             if (isset($request->to_branch_id) && !empty($request->to_branch_id)) {
                $shipments = $shipments->where('to_branch_id', $request->to_branch_id);
            }
            if (isset($request->type) && !empty($request->type)) {
                $shipments = $shipments->where('type', $request->type);
            }
            if (isset($request->from_country_id) && !empty($request->from_country_id)) {
                $shipments = $shipments->where('from_country_id', $request->from_country_id);
            }
            if (isset($request->to_country_id) && !empty($request->to_country_id)) {
                $shipments = $shipments->where('to_country_id', $request->to_country_id);
            }
            if (isset($request->from_region_id) && !empty($request->from_region_id)) {
                $shipments = $shipments->where('from_state_id', $request->from_region_id);
            }
            if (isset($request->to_region_id) && !empty($request->to_region_id)) {
                $shipments = $shipments->where('to_state_id', $request->to_region_id);
            }
            if (isset($request->from_area_id) && !empty($request->from_area_id)) {
                $shipments = $shipments->where('from_area_id', $request->from_area_id);
            }
            if (isset($request->to_area_id) && !empty($request->to_area_id)) {
                $shipments = $shipments->where('to_area_id', $request->to_area_id);
            }
            if (isset($request->order_id) && !empty($request->order_id)) {
                $shipments = $shipments->where('order_id', $request->order_id);
            }

            if (isset($request->status) && !empty($request->status)) {
                if($request->status == 'all')
                {
                    $shipments = $shipments->where('id','!=', null );
                }else {
                    if(is_array($request->status)){
                        $shipments = $shipments->whereIn('status_id', $request->status);
                    }else{
                        $shipments = $shipments->where('status_id', $request->status);
                    }
                }
            }

            if (isset($request->sort_by) && !empty($request->sort_by)) {
                if($request->sort_by == Shipment::OLDEST)
                {
                    $shipments = $shipments->orderBy('created_at','asc');
                }
            }
        }

        return $shipments;
    }

    public function index(Request $request )
    {
        $shipments = new Shipment();
        $type = null;
        $sort_by = null;

        $shipments = $this->search($request, $shipments);
        if (isset($request)) {
            if (isset($request->status) && !empty($request->status)) {
                $shipments = $shipments->whereIn('status_id', $request->status);
            }
            if (isset($request->client_status) && !empty($request->client_status)) {
                $shipments = $shipments->whereIn('client_status', $request->client_status);
            }
        }

       // $shipments = $shipments->with(['pay','from_address'])->orderBy('client_id')->orderBy('id','DESC')->paginate(20);
        $shipments = $shipments->with(['pay','from_address'])->orderBy('branch_id','DESC')->orderBy('id','DESC')->paginate(20);
        $actions = new ShipmentActionHelper();
        $actions = $actions->get('all');
        $page_name = translate('All Shipments');
        $status = 'all';
        if($request->is('api/*')){
            return  response()->json($shipments);
        }

        return view('backend.shipments.index', compact('shipments', 'page_name', 'type', 'actions', 'status' , 'sort_by'));
    }

    public function statusIndex(Request $request, $status, $type = null , $sort_by = null)
    {
        $shipments = new Shipment();
        if(isset($status) && !empty($status)) {
            $request->request->add(['status' => $status ]);
        }
        $shipments = $this->search($request, $shipments);

        if ($type != null) {
            $shipments = $shipments->where('type', $type);
        }
        $shipments = $shipments->with('pay')->orderBy('branch_id','desc')->orderBy('id','DESC')->paginate(20);

        $actions = new ShipmentActionHelper();
        $actions = $actions->get($status, $type);
        $page_name = Shipment::getStatusByStatusId($status) . " " . Shipment::getType($type);
        return view('backend.shipments.index', compact('shipments', 'actions', 'page_name', 'type', 'status', 'sort_by'));
    }

    public function track()
    {
        if(isset($_GET['code'])){
            $shipment = Shipment::where('code', $_GET['code'])->first();
            if($shipment){
                return redirect()->route('admin.shipments.tracking', $_GET['code']);
            }else{
                flash(translate("Invalid shipment code"))->error();
            }
        }
        return view('backend.shipments.track');
    }

    public function tracking($code)
    {
        $shipment = Shipment::where('code', $code)->first();
        if($shipment){
            return view('backend.shipments.tracking', compact('shipment'));
        }else{
            flash(translate("Invalid shipment code"))->error();
            return redirect()->route('admin.shipments.track');
        }
    }

    public function pay($shipment_id)
    {
        $shipment = Shipment::find($shipment_id);
        if(!$shipment || $shipment->paid == 1){
            flash(translate("Invalid Link"))->error();
            return back();
        }
        // return $shipment;
        return view('backend.shipments.pay',["shipment"=>$shipment]);
    }
    public function printStickers(Request $request)
    {
        $shipments_ids = $request->checked_ids;
        return view('backend.shipments.print-stickers', compact('shipments_ids'));
    }
    public function createPickupMission(Request $request, $type)
    {
        try {

            if($request->is('api/*')){

                $token = $request->header('token');
                if(isset($token))
                {
                    $auth_user = User::where('api_token',$token)->first();
                    if(!$auth_user)
                    {
                        return response()->json('Not Authorized');
                    }

                }else{
                    return response()->json('Not Authorizedd');
                }
            }else{
                $auth_user = Auth::user(); // In case auth user is client . confirm client user id = auth id
            }


            // if($auth_user->user_type == "customer" && $auth_user->userClient->client_id != $request['Mission']['client_id']){
            //     flash(translate("Error"))->error();
            //     return back();
            // }

            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::PICKUP_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }

            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = $code.$model->id;
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;

            if (!$model->save()) {
                throw new \Exception();
            }

            //change shipment status to requested
            $action = new StatusManagerHelper();
            $response = $action->change_shipment_status($request->checked_ids, Shipment::REQUESTED_STATUS, $model->id);

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);

            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::PICKUP_TYPE) == 0)
                {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            event(new ShipmentAction( Shipment::REQUESTED_STATUS,$request->checked_ids));

            event(new CreateMission($model));

            DB::commit();
            if($request->is('api/*')){
                 return $model;
            }else{
                flash(translate("Mission created successfully"))->success();
            	return back();
            }

        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }
    
    public function getAmountModel(Request $request, $shipment_id) {
        $shipment = Shipment::find($shipment_id);
        return view('backend.shipments.ajaxed-confirm-amount', compact(['shipment']));
    }

    public function createDeliveryMission(Request $request, $type)
    {
        try {
            DB::beginTransaction();
            $model = new Mission();
            // $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::DELIVERY_TYPE;
            $model->otp  = MissionPRNG::get();
            // if(ShipmentSetting::getVal('def_shipment_conf_type')=='otp'){
            //     $model->otp = MissionPRNG::get();
            // }
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::DELIVERY_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);

            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                flash(translate("Mission created successfully"))->success();
            	return back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createTransferMission(Request $request, $type)
    {
        try {
            
            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::TRANSFER_TYPE;
            
            if (!$model->save()) {
                throw new \Exception();
            }
            
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            
            foreach ($request->checked_ids as $shipment_id) {
                // if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::TRANSFER_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                // }
            }
        
            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);


            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                flash(translate("Mission created successfully"))->success();
            	return back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createSupplyMission(Request $request, $type)
    {
        try {
            if($request->is('api/*')){

                $token = $request->header('token');
                if(isset($token))
                {
                    $auth_user = User::where('api_token',$token)->first();
                    if(!$auth_user)
                    {
                        return response()->json('Not Authorized');
                    }

                }else{
                    return response()->json('Not Authorizedd');
                }
            }else{
                $auth_user = Auth::user(); // In case auth user is client . confirm client user id = auth id
            }

            // if($auth_user->user_type == "customer" && $auth_user->userClient->client_id != $request['Mission']['client_id']){
            //     flash(translate("Error"))->error();
            //     return back();
            // }

            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->type = Mission::SUPPLY_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::SUPPLY_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);


            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                flash(translate("Mission created successfully"))->success();
            	return back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function createReturnMission(Request $request, $type)
    {
        try {
            DB::beginTransaction();
            $model = new Mission();
            $model->fill($request['Mission']);
            $model->code = -1;
            $model->status_id = Mission::REQUESTED_STATUS;
            $model->otp  = MissionPRNG::get();
            $model->type = Mission::RETURN_TYPE;
            if (!$model->save()) {
                throw new \Exception();
            }
            $code = '';
            for($n = 0; $n < ShipmentSetting::getVal('mission_code_count'); $n++){
                $code .= '0';
            }
            $code   =   substr($code, 0, -strlen($model->id));
            $model->code = ShipmentSetting::getVal('mission_prefix').$code.$model->id;
            if (!$model->save()) {
                throw new \Exception();
            }
            foreach ($request->checked_ids as $shipment_id) {
                if ($model->id != null && ShipmentMission::check_if_shipment_is_assigned_to_mission($shipment_id, Mission::RETURN_TYPE) == 0) {
                    $shipment = Shipment::find($shipment_id);
                    $shipment_mission = new ShipmentMission();
                    $shipment_mission->shipment_id = $shipment->id;
                    $shipment_mission->mission_id = $model->id;
                    if ($shipment_mission->save()) {
                        $shipment->mission_id = $model->id;
                        $shipment->save();
                    }
                }
            }

            //Calaculate Amount
            $helper = new TransactionHelper();
            $helper->calculate_mission_amount($model->id);

            event(new CreateMission($model));
            DB::commit();

            if($request->is('api/*')){
                 return $model;
            }else{
                flash(translate("Mission created successfully"))->success();
            	return back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function removeShipmentFromMission(Request $request , $fromApi = false)
    {
        $shipment_id = $request->shipment_id;
        $mission_id = $request->mission_id;
        try {
            DB::beginTransaction();

            $mission = Mission::find($mission_id);
            $shipment = Shipment::find($shipment_id);
            if($mission && $shipment && in_array($mission->status_id , [Mission::APPROVED_STATUS,Mission::REQUESTED_STATUS,Mission::RECIVED_STATUS])){
                //change shipment status to requested
                // return $mission->shipment_mission_by_shipment_id($shipment_id);
                $action = new StatusManagerHelper();
                if($mission->type == Mission::getType(Mission::PICKUP_TYPE)){
                    $response = $action->change_shipment_status([$shipment_id], Shipment::SAVED_STATUS, $mission_id);
                }elseif(in_array($mission->type , [Mission::getType(Mission::DELIVERY_TYPE) ,Mission::getType(Mission::RETURN_TYPE) ]) && $mission->status_id == Mission::RECIVED_STATUS){
                    $response = $action->change_shipment_status([$shipment_id], Shipment::RETURNED_STATUS, $mission_id);
                }elseif(in_array($mission->type , [Mission::getType(Mission::DELIVERY_TYPE) ,Mission::getType(Mission::RETURN_TYPE) ]) && in_array($mission->status_id , [Mission::APPROVED_STATUS,Mission::REQUESTED_STATUS]) ){
                    $response = $action->change_shipment_status([$shipment_id], Shipment::RETURNED_STOCK, $mission_id);
                }

                if($shipment_mission = $mission->shipment_mission_by_shipment_id($shipment_id)){
                    $shipment_mission->delete();
                }
                $shipment_reason = new ShipmentReason();
                $shipment_reason->reason_id = $request->reason;
                $shipment_reason->shipment_id = $request->shipment_id;
                $shipment_reason->type = "Delete From Mission";
                $shipment_reason->save();
                //Calaculate Amount
                $helper = new TransactionHelper();
                $helper->calculate_mission_amount($mission_id);

                event(new UpdateMission( $mission_id));
                event(new ShipmentAction( Shipment::SAVED_STATUS,[$shipment]));
                DB::commit();
                if($fromApi)
                {
                    return 'success';
                }
                flash(translate("Shipment removed from mission successfully"))->success();
                return back();
            }else{
                flash(translate("Invalid Shipment"))->error();
                return back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }


    public function change(Request $request, $to)
    {

        if (isset($request->checked_ids)) {
            $action = new StatusManagerHelper();
            if ($to == Shipment::DELIVERED_STATUS) {
                $shipment = Shipment::where('id', $request->checked_ids[0])->get()->first();
                if (ShipmentSetting::getVal('def_shipment_conf_type') == 'seg') {
                    if (isset($request->signaturePadImg)) {
                        $params['seg_img'] = $request->signaturePadImg;
                    } else {
                        if ($request->signaturePadImg == null || $request->signaturePadImg == " ") {
                            flash(translate("Please Confirm The Signature"))->error();
                            return back();
                        }
                    }
                } elseif (ShipmentSetting::getVal('def_shipment_conf_type') == 'otp') {
                    if (isset($request->otp_confirm)) {
                        if ($shipment->otp != $request->otp_confirm) {
                            flash(translate("Please enter correct OTP"))->error();
                            return back();
                        }
                    } else {
                        if ($request->otp_confirm == null || $request->otp_confirm == " ") {
                            if ($fromApi) {
                                return response()->json(['message' => "Please enter OTP of mission", 'status' => false]);
                            }
                            flash(translate("Please enter OTP of mission"))->error();
                            return back();
                        }
                    }
                }
                $to_branch = Branch::where('id', $shipment->to_branch_id)->first();
                if ($shipment->amount_to_be_collected > 0) {
                    $transaction = new TransactionHelper();
                    $owner = Transaction::FRANCHISE;
                    if ($to_branch->type == 1) {
                        $owner = Transaction::BRANCH;
                    }
                    $transaction->create_shipment_transaction($shipment->id, $shipment->amount_to_be_collected, $owner, $shipment->to_branch_id, Transaction::CREDIT, Transaction::SHIPMENT_TYPE, 'COD collection');
                }
                if ($shipment->payment_method_id != 16707 && $shipment->payment_type == Shipment::POSTPAID) {
                    $bookAmt = $shipment->tax + $shipment->shipping_cost + $shipment->shipping_distance_cost + $shipment->pickup_cost + $shipment->cod_cost + $shipment->door_delivery_cost - $shipment->discount_amt;
                    $transaction = new TransactionHelper();
                    $owner = Transaction::FRANCHISE;
                    if ($to_branch->type == 1) {
                        $owner = Transaction::BRANCH;
                    }
                    $transaction->create_shipment_transaction($shipment->id, $bookAmt, $owner, $shipment->to_branch_id, Transaction::CREDIT, Transaction::SHIPMENT_TYPE, 'Postpaid shipment');
                    $shipment->paid = 1;
                    $shipment->save();
                }
                if ($to_branch->type == 2) {
                    $delCom = (($shipment->tax + $shipment->shipping_cost + $shipment->shipping_distance_cost + $shipment->pickup_cost + $shipment->cod_cost + $shipment->door_delivery_cost - $shipment->discount_amt) * $to_branch->delivery_commission) / 100;
                    $delDesc = $to_branch->delivery_commission . '% delivery commission for ' . ($shipment->tax + $shipment->shipping_cost + $shipment->shipping_distance_cost + $shipment->pickup_cost + $shipment->cod_cost + $shipment->door_delivery_cost - $shipment->discount_amt);
                    $transaction = new TransactionHelper();
                    $transaction->create_shipment_transaction($shipment->id, $delCom, Transaction::FRANCHISE, $shipment->to_branch_id, Transaction::DEBIT, Transaction::COMMISSION_TYPE, $delDesc);
                }
            }
            $response = $action->change_shipment_status($request->checked_ids, $to);
            if ($response['success']) {
                event(new ShipmentAction($to,$request->checked_ids));
                flash(translate("Status Changed Successfully!"))->success();
                return back();
            }
        } else {
            flash(translate("Please select shipments"))->error();
            return back();
        }
    }

    public function ajaxGetAddressess()
    {
        $client_id = $_GET['client_id'];
        $addressess = AddressClient::where('client_id', $client_id)->get();
        return response()->json($addressess);
    }
    public function ajaxGgenerateToken()
    {
        $userRegistrationHelper = new UserRegistrationHelper(Auth::user()->id);
        $token = $userRegistrationHelper->setApiTokenGenerator();

        return response()->json($token);
    }
    public function ajaxGetStates()
    {
        $country_id = $_GET['country_id'];
        $states = State::where('country_id', $country_id)->where('covered',1)->get();
        return response()->json($states);
    }
    public function ajaxGetAreas()
    {
        $state_id = $_GET['state_id'];
        $areas = Area::where('state_id', $state_id)->get();
        return response()->json($areas);
    }
    public function ajaxGetNotifications(Request $request)
    {
        if($request->is('api/*')){
            $token = $request->header('token');
            if(isset($token))
            {
                $user = User::where('api_token',$token)->first();

                if(!$user)
                {
                    return response()->json('Not Authorized');
                }
                $notification = $user->notifications()->select('data','created_at')->where('notifiable_type','App\User')->where('notifiable_id', $user->id)->get();

                return response()->json($notification);
            }else{
                return response()->json('Not Authorizedd');
            }
        }
    }

    public function ajaxGetShipmentByBarcode(Request $request)
    {
        if($request->is('api/*')){
            $token = $request->header('token');
            if(isset($token))
            {
                $user = User::where('api_token',$token)->first();

                if(!$user)
                {
                    return response()->json('Not Authorized');
                }
                $barcode  = $_GET['barcode'];
                $shipment = Shipment::where('client_id', $user->userClient->client_id)->where('barcode' , $barcode)->first();
                return response()->json($shipment);
            }else{
                return response()->json('Not Authorizedd');
            }
        }
    }

    public function ajaxGetEstimationCost(Request $request)
    {
        $request->validate([
            'total_weight' => 'required|integer|min:0',
        ]);
        $costs = $this->applyDetailShipmentCost($request, $request->package_ids);
        $formated_cost["tax"] = format_price($costs["tax"]);
        $formated_cost["shipping_cost"] = format_price($costs["shipping_cost"]);
        $formated_cost["shipping_distance_cost"] = format_price($costs["shipping_distance_cost"]);
        $formated_cost["pickup_cost"] = format_price($costs["pickup_cost"]);
        $formated_cost["cod_cost"] = format_price($costs["cod_cost"]);
        $formated_cost["door_delivery_cost"] = format_price($costs["door_delivery_cost"]);
        $formated_cost["discount"] = format_price($costs["discount_amt"]);
        $formated_cost["total_cost"] = format_price($costs["shipping_cost"] + $costs["tax"] + $costs["shipping_distance_cost"] + $costs["pickup_cost"] + $costs["cod_cost"] + $costs["door_delivery_cost"] - $costs["discount_amt"]);

        return $formated_cost;
    }
    
    public function ajaxGetEstimationDelivery(Request $request) {
        $request->validate([
            'from_state_id' => 'required',
            'to_state_id' => 'required',
        ]);


        $from_country_id = $request['from_country_id'];
        $to_country_id = $request['to_country_id'];

        if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
            $from_state_id = $request['from_state_id'];
            $to_state_id = $request['to_state_id'];
        }
        if (isset($request['from_area_id']) && isset($request['to_area_id'])) {
            $from_area_id = $request['from_area_id'];
            $to_area_id = $request['to_area_id'];
        }

        // Shipping Cost = Default + kg + Covered Custom  + Package extra
        $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);

        if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
            $covered_cost = $covered_cost->where('from_state_id', $from_state_id)->where('to_state_id', $to_state_id);
        } else {
            $covered_cost = $covered_cost->where('from_state_id', 0)->where('to_state_id', 0);
        }

        $covered_cost = $covered_cost->first();

        if ($covered_cost != null) {
            $date = date('Y-m-d', strtotime("+$covered_cost->delivery_time days"));
        } else {
            $date = date('Y-m-d');
        }
        return $date;
    }

    public function feesSettings()
    {
        return view('backend.shipments.fees-type-settings');
    }
    public function feesFixedSettings()
    {
        return view('backend.shipments.fees-fixed-settings');
    }
    public function feesGramSettings()
    {
        return view('backend.shipments.fees-by-gram-price-settings');
    }
    public function feesStateToStateSettings()
    {
        $costs = Cost::paginate(20);
        return view('backend.shipments.fees-state-to-state-settings')->with('costs', $costs);
    }
    public function feesCountryToCountrySettings()
    {
        $costs = Cost::paginate(20);
        return view('backend.shipments.fees-country-to-country-settings')->with('costs', $costs);
    }

    public function settings()
    {

        return view('backend.shipments.settings');
    }

    public function storeSettings()
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }

        foreach ($_POST['Setting'] as $key => $value) {
            if (ShipmentSetting::where('key',$key)->count() == 0) {
                $set = new ShipmentSetting();
                $set->key = $key;
                $set->value = $value;
                $set->save();
            } else {
                $set = ShipmentSetting::where('key', $key)->first();
                $set->value = $value;
                $set->save();
            }
        }

        flash(translate("Settings Changed Successfully!"))->success();
        if (isset($_POST['Setting']['fees_type'])) {
            if ($_POST['Setting']['fees_type'] == 1) {
                return redirect()->route('admin.shipments.settings.fees.fixed');
            } elseif ($_POST['Setting']['fees_type'] == 2) {
                return redirect()->route('admin.shipments.settings.fees.state-to-state');
            } elseif ($_POST['Setting']['fees_type'] == 4) {
                return redirect()->route('admin.shipments.settings.fees.country-to-country');
            } elseif ($_POST['Setting']['fees_type'] == 5) {
                return redirect()->route('admin.shipments.settings.fees.gram');
            }
        } else {
            return back();
        }
    }

    public function getConfirmationTypeMission(Request $request)
    {
        if($request->is('api/*')){
            $token = $request->header('token');
            if(isset($token))
            {
                $user = User::where('api_token',$token)->first();

                if(!$user)
                {
                    return response()->json('Not Authorized');
                }
                $confirmType = ShipmentSetting::where('key', 'def_shipment_conf_type')->first();
                return response()->json($confirmType);
            }else{
                return response()->json('Not Authorizedd');
            }
        }
    }

    public function createMissionAPI(Request $request)
    {
        if($request->is('api/*')){
            $token = $request->header('token');
            if(isset($token))
            {
                $user = User::where('api_token',$token)->first();

                if(!$user)
                {
                    return response()->json('Not Authorized');
                }
                switch($request->type){
                    case Mission::PICKUP_TYPE:
                        $mission = $this->createPickupMission($request,$request->type);
                        break;
                    case Mission::DELIVERY_TYPE:
                        $mission = $this->createDeliveryMission($request,$request->type);
                        break;
                    case Mission::TRANSFER_TYPE:
                        $mission = $this->createTransferMission($request,$request->type);
                        break;
                    case Mission::SUPPLY_TYPE:
                        $mission = $this->createSupplyMission($request,$request->type);
                        break;
                    case Mission::RETURN_TYPE:
                        $mission = $this->createReturnMission($request,$request->type);
                        break;
                }
                return response()->json($mission);
            }else{
                return response()->json('Not Authorizedd');
            }
        }
    }

    public function applyShipmentCost($request,$packages)
    {
        $client_costs = Client::where('id', $request['client_id'] )->first();

        $from_country_id = $request['from_country_id'];
        $to_country_id = $request['to_country_id'];

        if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
            $from_state_id = $request['from_state_id'];
            $to_state_id = $request['to_state_id'];
        }
        if (isset($request['from_area_id']) && isset($request['to_area_id'])) {
            $from_area_id = $request['from_area_id'];
            $to_area_id = $request['to_area_id'];
        }

        $total_weight = 0 ;
        $package_extras = 0;
        foreach ($packages as $pack) {
            $total_weight += isset($pack['weight']) ? $pack['weight'] : 1;
            $extra = Package::find($pack['package_id'])->cost;
            $package_extras += $extra;
        }

        //$weight =  $request['total_weight'];
        $weight = isset($request['total_weight']) ? $request['total_weight'] : $total_weight;

        $array = ['return_cost' => 0, 'shipping_cost' => 0, 'tax' => 0, 'insurance' => 0];

        // Shipping Cost = Default + kg + Covered Custom  + Package extra
        $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);

        if (isset($request['from_state_id']) && isset($request['to_state_id'])) {
            $covered_cost = $covered_cost->where('from_state_id', $from_state_id)->where('to_state_id', $to_state_id);
        } else {
            $covered_cost = $covered_cost->where('from_state_id', 0)->where('to_state_id', 0);
        }

        $covered_cost = $covered_cost->first();

        $def_return_cost_gram = $client_costs && $client_costs->def_return_cost_gram   ? $client_costs->def_return_cost_gram   : ShipmentSetting::getCost('def_return_cost_gram');
        $def_return_cost = $client_costs && $client_costs->def_return_cost ? $client_costs->def_return_cost : ShipmentSetting::getCost('def_return_cost');

        $def_shipping_cost_gram = $client_costs && $client_costs->def_shipping_cost_gram ? $client_costs->def_shipping_cost_gram : ShipmentSetting::getCost('def_shipping_cost_gram');
        $def_shipping_cost = $client_costs && $client_costs->def_shipping_cost ? $client_costs->def_shipping_cost : ShipmentSetting::getCost('def_shipping_cost');

        $def_return_mile_cost_gram = $client_costs && $client_costs->def_return_mile_cost_gram ? $client_costs->def_return_mile_cost_gram : ShipmentSetting::getCost('def_return_mile_cost_gram');
        $def_return_mile_cost  = $client_costs && $client_costs->def_return_mile_cost ? $client_costs->def_return_mile_cost : ShipmentSetting::getCost('def_return_mile_cost');

        $def_mile_cost_gram = $client_costs && $client_costs->def_mile_cost_gram ? $client_costs->def_mile_cost_gram : ShipmentSetting::getCost('def_mile_cost_gram');
        $def_mile_cost = $client_costs && $client_costs->def_mile_cost ? $client_costs->def_mile_cost : ShipmentSetting::getCost('def_mile_cost');

        $def_insurance_gram = $client_costs && $client_costs->def_insurance_gram ? $client_costs->def_insurance_gram : ShipmentSetting::getCost('def_insurance_gram');
        $def_insurance = $client_costs && $client_costs->def_insurance ? $client_costs->def_insurance : ShipmentSetting::getCost('def_insurance');


        $def_tax_gram = $client_costs && $client_costs->def_tax_gram ? $client_costs->def_tax_gram : ShipmentSetting::getCost('def_tax_gram');
        $def_tax = $client_costs && $client_costs->def_tax ? $client_costs->def_tax : ShipmentSetting::getCost('def_tax');




        if ($covered_cost != null) {

            // $package_extras = 0;
            // foreach ($packages as $pack) {
            //     $extra = Package::find($pack['package_id'])->cost;
            //     $package_extras += $extra;
            // }

            if($weight > 1){
                if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = (float) $covered_cost->return_cost + (float) ( $def_return_cost_gram * ($weight -1));
                    $shipping_cost_first_one = (float) $covered_cost->shipping_cost + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_shipping_cost_gram * ($weight -1));
                } else if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = (float) $covered_cost->return_mile_cost + (float) ( $def_return_mile_cost_gram * ($weight -1));
                    $shipping_cost_first_one = (float) $covered_cost->mile_cost + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_mile_cost_gram * ($weight -1));
                }
                $insurance = (float) $covered_cost->insurance + (float) ( $def_insurance_gram * ($weight -1));

                $tax_for_first_one = (($covered_cost->tax * $shipping_cost_first_one) / 100 );

                $tax_for_exrea = (( $def_tax_gram * $shipping_cost_for_extra) / 100 );

                $shipping_cost = $shipping_cost_first_one + $shipping_cost_for_extra;
                $tax = $tax_for_first_one + $tax_for_exrea;

            }else{
                if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = (float) $covered_cost->return_cost;
                    $shipping_cost = (float) $covered_cost->shipping_cost + $package_extras;
                } else if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = (float) $covered_cost->return_mile_cost;
                    $shipping_cost = (float) $covered_cost->mile_cost + $package_extras;
                }
                $insurance = (float) $covered_cost->insurance;
                $tax = (($covered_cost->tax * $shipping_cost) / 100 );
            }

            $array['tax'] = $tax;
            $array['insurance'] = $insurance;
            $array['return_cost'] = $return_cost;
            $array['shipping_cost'] = $shipping_cost;
        } else {

            // $package_extras = 0;

            // foreach ($packages as $pack) {
            //     $extra = Package::find($pack['package_id'])->cost;
            //     $package_extras += $extra;
            // }

            if($weight > 1){
                if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = $def_return_cost + (float) ( $def_return_cost_gram * ($weight -1));
                    $shipping_cost_first_one = $def_shipping_cost + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_shipping_cost_gram * ($weight -1));

                } else if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = $def_return_mile_cost + (float) ( $def_return_mile_cost_gram * ($weight -1));
                    $shipping_cost_first_one = $def_mile_cost + $package_extras;
                    $shipping_cost_for_extra = (float) ( $def_mile_cost_gram * ($weight -1));
                }

                $insurance = $def_insurance + (float) ( $def_insurance_gram * ($weight -1));
                $tax_for_first_one = (( $def_tax * $shipping_cost_first_one) / 100 );
                $tax_for_exrea = ((ShipmentSetting::getCost('def_tax_gram') * $shipping_cost_for_extra) / 100 );

                $shipping_cost = $shipping_cost_first_one + $shipping_cost_for_extra;
                $tax = $tax_for_first_one + $tax_for_exrea;

            }else{
                if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='2')
                {
                    $return_cost = $def_return_cost;
                    $shipping_cost = $def_shipping_cost + $package_extras;
                } else if(\App\ShipmentSetting::getVal('is_def_mile_or_fees')=='1')
                {
                    $return_cost = $def_return_mile_cost;
                    $shipping_cost = $def_mile_cost + $package_extras;
                }
                $insurance = $def_insurance;
                $tax = (( $def_tax * $shipping_cost) / 100 );
            }

            $array['tax'] = $tax;
            $array['insurance'] = $insurance;
            $array['return_cost'] = $return_cost;
            $array['shipping_cost'] = $shipping_cost;

        }
        return $array;
    }
    
    //Written by Mushaqdeen
    public function applyDetailShipmentCost($request, $packages) {
        if (isset($request['from_branch_id'])) {
            $from_branch = Branch::where('id', $request['from_branch_id'])->first();
        } else {
            $from_branch = Branch::where('id', $request['branch_id'])->first();
        }
        $to_branch = Branch::where('id', $request['to_branch_id'])->first();

        $from_country_id = $from_branch->country_id;
        $to_country_id = $to_branch->country_id;

        if (isset($from_branch->state_id) && isset($to_branch->state_id)) {
            $from_state_id = $from_branch->state_id;
            $to_state_id = $to_branch->state_id;
        }

        $weight_cost = 0;
        $distance_cost = 0;
        $weight = 0;

        $container_weight = 0;
        $container_height = 0;
        $container_width = 0;
        $container_length = 0;

        $covered_cost = Cost::where('from_country_id', $from_country_id)->where('to_country_id', $to_country_id);
        if (isset($from_branch->state_id) && isset($to_branch->state_id)) {
            $covered_cost = $covered_cost->where('from_state_id', $from_state_id)->where('to_state_id', $to_state_id);
        } else {
            $covered_cost = $covered_cost->where('from_state_id', 0)->where('to_state_id', 0);
        }
        $covered_cost = $covered_cost->first();

        $distance_price = 0;
        if ($covered_cost != null) {
            if (\App\ShipmentSetting::getVal('is_def_mile_or_fees') == '2') {
                $distance_price = (float) $covered_cost->shipping_cost;
            }
        }

        foreach ($packages as $pack) {
            $weight = $weight + $pack['weight'];
            $package = Package::find($pack['package_id']);
            if ($package->id == 4) {
                $container_weight = $container_weight + $pack['weight'];
                $container_height = $container_height + $pack['height'];
                $container_width = $container_width + $pack['width'];
                $container_length = $container_length + $pack['length'];
            } else {
                if ($from_state_id == $to_state_id) {
                    $weight_cost = $weight_cost + ($package->cost * $pack['qty']);
                } else {
                    $weight_cost = $weight_cost + ($package->cost_state * $pack['qty']);
                }
            }
        }

        if ($container_weight > 0) {
            $weight_config = \App\DeliveryWeightConfig::getCost($container_weight);
            $dimension_config = \App\DimensionConfig::getCost($container_height + $container_width + $container_length);
            $weight_price = 0;
            $dimension_price = 0;
            if ($weight_config != null) {
                if ($from_state_id == $to_state_id) {
                    $weight_price = ($weight_config->price * $container_weight);
                } else {
                    $weight_price = ($weight_config->price_state * $container_weight);
                }
            }
            if ($dimension_config != null) {
                if ($from_state_id == $to_state_id) {
                    $dimension_price = $dimension_config->price_district;
                } else {
                    $dimension_price = $dimension_config->price_state;
                }
            }
            if ($dimension_price > $weight_price) {
                $weight_cost = $weight_cost + $dimension_price;
            } else {
                $weight_cost = $weight_cost + $weight_price;
                $distance_cost = $distance_cost + $distance_price;
            }
        }

        $array = ['tax' => 0, 'shipping_cost' => $weight_cost, 'shipping_distance_cost' => $distance_cost, 'pickup_cost' => 0, 'cod_cost' => 0, 'door_delivery_cost' => 0, 'discount_amt' => 0];

        if ($request['type'] == 1) {
            $pickup_percent = (float) \App\PickupWeightConfig::getCost($weight);
            $array['pickup_cost'] = ((($array['shipping_distance_cost'] + $array['shipping_cost']) / 100)) * $pickup_percent;
        }
        if ((isset($request['type_delivery']) && $request['type_delivery'] == 2) || ((isset($request['delivery_method_id']) && $request['delivery_method_id'] == 2))) {
            $delivery_percent = (float) \App\PickupWeightConfig::getCost($weight);
            $array['door_delivery_cost'] = ((($array['shipping_distance_cost'] + $array['shipping_cost']) / 100)) * $delivery_percent;
        }
        if ($request['parcel_type'] == 'cod' && ((isset($request['cod_amt']) && $request['cod_amt'] > 0) || (isset($request['amount_to_be_collected']) && $request['amount_to_be_collected'] > 0))) {
            $cod_amt = isset($request['cod_amt']) ? $request['cod_amt'] : $request['amount_to_be_collected'];
            $array['cod_cost'] = (float) \App\CodConfig::getCost($cod_amt);
        }
        if ($request['discount'] > 0) {
            $array['discount_amt'] = (($array['shipping_distance_cost'] + $array['shipping_cost'] + $array['pickup_cost'] + $array['door_delivery_cost'] + $array['cod_cost']) / 100) * $request['discount'];
        }
        if ($request['taxable'] == 1) {
            $array['tax'] = ((($array['shipping_distance_cost'] + $array['shipping_cost'] + $array['pickup_cost'] + $array['door_delivery_cost'] + $array['cod_cost'] - $array['discount'])) / 100) * $covered_cost->tax;
        }
        return $array;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $branchs = Branch::where('is_archived', 0)->get();
        $clients = Client::where('is_archived', 0)->get();
        return view('backend.shipments.create', compact('branchs', 'clients'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
                $model = $this->storeShipment($request);
            DB::commit();
            flash(translate("Shipment added successfully"))->success();
            return redirect()->route('admin.shipments.show', $model->id);
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    public function storeAPI(Request $request)
    {
        try {
            DB::beginTransaction();
                $message = $this->storeShipment($request , $request->header('auth-token'));
            DB::commit();
            return response()->json(['message' => $message ] );
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function addShipmentByApi()
    {
        $branchs = Branch::where('is_archived', 0)->get();
        return view('backend.shipments.create_by_api', compact('branchs'));
    }

    private function storeShipment($request , $token = null)
    {
        $from_branch = Branch::where('id', $request->Shipment['branch_id'])->first();
        $to_branch = Branch::where('id', $request->Shipment['to_branch_id'])->first();
        
        $model = new Shipment();


        $model->fill($request->Shipment);
        if (isset($from_branch->country_id) && isset($to_branch->country_id)) {
            $model->from_country_id = $from_branch->country_id;
            $model->to_country_id = $to_branch->country_id;
        }
        
        if (isset($from_branch->state_id) && isset($to_branch->state_id)) {
            $model->from_state_id = $from_branch->state_id;
            $model->to_state_id = $to_branch->state_id;
        }

        if (isset($from_branch->area_id) && isset($to_branch->area_id)) {
            $model->from_area_id = $from_branch->area_id;
            $model->to_area_id = $to_branch->area_id;
        }
        $model->code = -1;
        $model->from_branch_id = $request->Shipment['branch_id'];
        $model->status_id = Shipment::SAVED_STATUS;
        $date = date_create();
        $today = date("Y-m-d");

        if (!$model->save()) {
            return response()->json(['message' => new \Exception()] );
        }


        if(ShipmentSetting::getVal('def_shipment_code_type')=='random'){
            $barcode = ShipmentPRNG::get();
        }else{
            $code = '';
            //for ($n = 0; $n < ShipmentSetting::getVal('shipment_code_count'); $n++) {
            $todayCount = Shipment::where('from_branch_id', $request->Shipment['branch_id'])->where('shipping_date', $request->Shipment['shipping_date'])->count();
            for ($n = 0; $n < 3; $n++) {
                $code .= '0';
            }
            $code = substr($code, 0, -strlen($todayCount));
            $barcode = $from_branch->code . date('Ymd', strtotime($request->Shipment['shipping_date'])) . $code . $todayCount . $to_branch->code;
        }
        $model->barcode = $barcode;
        $model->code = ShipmentSetting::getVal('shipment_prefix') . $barcode;

        if((Auth::user()->user_type ?? "") == 'customer'){
            $model->client_id = Auth::user()->userClient->client_id;
        }

        if(isset($token)){
            $user = User::where('api_token', $token)->first();
            if(isset($user))
            {

                // Validation
                if(!isset($request->Shipment['type']) || !isset($request->Shipment['branch_id']) || !isset($request->Shipment['shipping_date']) || !isset($request->Shipment['client_address']) || !isset($request->Shipment['reciver_name']) || !isset($request->Shipment['reciver_phone']) || !isset($request->Shipment['reciver_address']) || !isset($request->Shipment['from_country_id']) || !isset($request->Shipment['to_country_id']) || !isset($request->Shipment['from_state_id']) || !isset($request->Shipment['to_state_id']) || !isset($request->Shipment['from_area_id']) || !isset($request->Shipment['to_area_id']) || !isset($request->Shipment['payment_method_id']) || !isset($request->Shipment['payment_type']) || !isset($request->Package))
                {
                    $message = 'Please make sure to add all required fields';
                    return $message;
                }else {
                    if($request->Shipment['type'] != '1' && $request->Shipment['type'] != '2' ){
                        return 'Type invalid';
                    }

                    if(!Branch::find($request->Shipment['branch_id'])){
                        return 'Branch invalid';
                    }

                    if(!AddressClient::find($request->Shipment['client_address'])){
                        return 'Client Address invalid';
                    }

                    if(!Country::find($request->Shipment['from_country_id']) || !Country::find($request->Shipment['to_country_id']) ){
                        return 'Country invalid';
                    }

                    if(!State::find($request->Shipment['from_state_id']) || !State::find($request->Shipment['to_state_id'])){
                        return 'State invalid';
                    }

                    if(!Area::find($request->Shipment['from_area_id']) || !Area::find($request->Shipment['to_area_id'])){
                        return 'Area invalid';
                    }

                    if(isset($request->Shipment['payment_method_id']) )
                    {
                        $BusinessSetting = BusinessSetting::where('id',$request->Shipment['payment_method_id'])->where('key', 'payment_gateway')->first();
                        if(!$BusinessSetting){
                            return 'Payment Method Id invalid';
                        }
                    }

                    if($request->Shipment['payment_type'] != Shipment::POSTPAID && $request->Shipment['payment_type'] != Shipment::PREPAID){
                        return 'Payment Type Id invalid';
                    }

                    if(isset($request->Shipment['delivery_time'])){
                        $delivery_time = DeliveryTime::where('name', $request->Shipment['delivery_time'] )->first();
                        if(!$delivery_time){
                            return 'Delivery Time invalid';
                        }
                    }

                }

                if($user->user_type == 'customer')
                {
                    $model->client_id = $user->userClient->client_id;
                }else{
                    $model->client_id = $user->id;
                }

                if(!isset($request->Shipment['client_phone'])){
                    $model->client_phone = $user->phone;
                }

                if(!isset($request->Shipment['amount_to_be_collected'])){
                    $model->amount_to_be_collected = 0;
                }

            }else{
                return response()->json('invalid or Expired Api Key');
            }
        }

        if (!$model->save()) {
            return response()->json(['message' => new \Exception()] );
        }

        $costs = $this->applyDetailShipmentCost($request->Shipment,$request->Package);

        $model->fill($costs);
        
        if ($request->Shipment['type'] == 2) {
            $action = new StatusManagerHelper();
            $action->change_shipment_status(array($model->id), Shipment::APPROVED_STATUS);
        }
        
        if ($request->Shipment['customer_type'] == 'walkin') {
            $model->client_id = 68;
            $model->client_address = 85;
        }
        
        if ($request->Shipment['payment_method_id'] != 16707 && $request->Shipment['payment_type'] == Shipment::PREPAID) {
            $model->paid = 1;
        }
        
        if ($request->Shipment['client_id'] != null) {
            $client = Client::where('id', $request->Shipment['client_id'])->first();
            $model->client_phone = $client->responsible_mobile;
        }
        
        if (!$model->save()) {
            return response()->json(['message' => new \Exception()] );
        }
        
        if ($request->Shipment['client_id'] != null) {
            if ($request->Shipment['payment_method_id'] == 16707) {
                $creditAmt = ($costs['tax'] + $costs['shipping_cost'] + $costs['shipping_distance_cost'] + $costs['pickup_cost'] + $costs['cod_cost'] + $costs['door_delivery_cost'] - $costs['discount_amt']);
                $creditDesc = 'Credit payment';
                $transaction = new TransactionHelper();
                $transaction->create_shipment_transaction($model->id, $creditAmt, Transaction::CLIENT, $request->Shipment['client_id'], Transaction::DEBIT, Transaction::CREDIT_TYPE, $creditDesc);
            }
        }

        if ($request->Shipment['payment_method_id'] != 16707 && $request->Shipment['payment_type'] == Shipment::PREPAID) {
            $bookAmt = $costs['tax'] + $costs['shipping_cost'] + $costs['shipping_distance_cost'] + $costs['pickup_cost'] + $costs['cod_cost'] + $costs['door_delivery_cost'] - $costs['discount_amt'];
            $transaction = new TransactionHelper();
            $owner = Transaction::FRANCHISE;
            if ($from_branch->type == 1) {
                $owner = Transaction::BRANCH;
            }
            $transaction->create_shipment_transaction($model->id, $bookAmt, $owner, $request->Shipment['branch_id'], Transaction::CREDIT, Transaction::SHIPMENT_TYPE, 'Prepaid shipment');
        }

        if ($from_branch->type == 2) {
            $bookCom = (($costs['tax'] + $costs['shipping_cost'] + $costs['shipping_distance_cost'] + $costs['pickup_cost'] + $costs['cod_cost'] + $costs['door_delivery_cost'] - $costs['discount_amt']) * $from_branch->booking_commission) / 100;
            $bookDesc = $from_branch->booking_commission . '% booking commission for ' . ($costs['tax'] + $costs['shipping_cost'] + $costs['shipping_distance_cost'] + $costs['pickup_cost'] + $costs['cod_cost'] + $costs['door_delivery_cost'] - $costs['discount_amt']);
            $transaction = new TransactionHelper();
            $transaction->create_shipment_transaction($model->id, $bookCom, Transaction::FRANCHISE, $request->Shipment['branch_id'], Transaction::DEBIT, Transaction::COMMISSION_TYPE, $bookDesc);
        }

        $counter = 0;
        if (isset($request->Package)) {
            if (!empty($request->Package)) {

                if (isset($request->Package[$counter]['package_id'])) {

                    if(isset($token))
                    {
                        $total_weight = 0;
                    }

                    foreach ($request->Package as $package) {
                        if(isset($token))
                        {
                            if(!Package::find($package['package_id'])){
                                return 'Package invalid';
                            }

                            if(!isset($package['qty'])){
                                $package['qty'] = 1;
                            }

                            if(!isset($package['weight'])){
                                $package['weight'] = 1;
                            }
                            if(!isset($package['length'])){
                                $package['length'] = 1;
                            }
                            if(!isset($package->width)){
                                $package['width'] = 1;
                            }
                            if(!isset($package['height'])){
                                $package['height'] = 1;
                            }

                            $total_weight = $total_weight + $package['weight'];
                        }
                        $package_shipment = new PackageShipment();
                        $package_shipment->fill($package);
                        $package_shipment->shipment_id = $model->id;
                        if (!$package_shipment->save()) {
                            throw new \Exception();
                        }
                    }

                    if(isset($token))
                    {
                        $model->total_weight = $total_weight;
                        if (!$model->save()) {
                            return response()->json(['message' => new \Exception()] );
                        }
                    }
                }
            }
        }

        event(new AddShipment($model));

        if(isset($token))
        {
            $message = 'Shipment added successfully';
            return $message;
        }else {
            return $model;
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
        $shipment = Shipment::find($id);
        return view('backend.shipments.show', compact('shipment'));
    }

    public function print($shipment, $type = 'invoice')
    {
        $shipment = Shipment::find($shipment);
        if($type == 'label'){
            return view('backend.shipments.print_label', compact('shipment'));
        }else{
            return view('backend.shipments.print', compact('shipment'));
        }
    }

    public function shipmentsReport(Request $request)
    {
        $shipments = new Shipment();
        $type = null;
        if (isset($_GET)) {
            if (isset($_GET['code']) && !empty($_GET['code'])) {

                $shipments = $shipments->where('code', $_GET['code']);
            }
            if (isset($_GET['client_id']) && !empty($_GET['client_id'])) {

                $shipments = $shipments->where('client_id', $_GET['client_id']);
            }
            if (isset($_GET['branch_id']) && !empty($_GET['branch_id'])) {
                $shipments = $shipments->where('branch_id', $_GET['branch_id']);
            }
            if (isset($_GET['type']) && !empty($_GET['type'])) {
                $shipments = $shipments->where('type', $_GET['type']);
            }
        }
        if(Auth::user()->user_type == 'customer'){
            $shipments = $shipments->where('client_id', Auth::user()->userClient->client_id);
        }elseif(Auth::user()->user_type == 'branch'){
            $shipments = $shipments->where('branch_id', Auth::user()->userBranch->branch_id);
        }
        $shipments = $shipments->orderBy('id','DESC')->paginate(20);
        $actions = new ShipmentActionHelper();
        $actions = $actions->get('all');
        $page_name = translate('All Shipments');
        $status = 'all';
        return view('backend.shipments.shipments-report', compact('shipments', 'page_name', 'type', 'actions', 'status'));
    }
    public function exportShipmentsReport(Request $request)
    {

        $object = new \App\Services\ShipmentsExport;
		$object->branch_id = $_POST['branch_id'];
		$object->client_id = $_POST['client_id'];
		$object->type = $_POST['type'];
		$object->status = $_POST['status'];
        if(isset($_POST['excel'])){
		$fileName='Shipments_'.date("Y-m-d").'.xlsx';
		return Excel::download($object, $fileName);
        }else
        {
            $shipments = new Shipment();
            $type = null;
            if (isset($_POST)) {

                if (isset($_POST['status']) && !empty($_POST['status'])) {
                    $shipments = $shipments->where('status_id', $_POST['status']);
                }
                if (isset($_POST['client_id']) && !empty($_POST['client_id'])) {

                    $shipments = $shipments->where('client_id', $_POST['client_id']);
                }
                if (isset($_POST['branch_id']) && !empty($_POST['branch_id'])) {
                    $shipments = $shipments->where('branch_id', $_POST['branch_id']);
                }
                //start author pathi
                if (isset($_POST['to_branch_id']) && !empty($_POST['to_branch_id'])) {
                    $shipments = $shipments->where('to_branch_id', $_POST['to_branch_id']);
                }
                 //end author pathi
                if (isset($_POST['type']) && !empty($_POST['type'])) {
                    $shipments = $shipments->where('type', $_POST['type']);
                }
                if(isset($_POST['from_date']) && isset($_POST['to_date']) )
                {
                    if(!empty($_POST['from_date']))
                    {
                        $shipments = $shipments->whereBetween('created_at',[$_POST['from_date'],$_POST['to_date']]);
                    }
                }
            }
            if(Auth::user()->user_type == 'customer'){
                $shipments = $shipments->where('client_id', Auth::user()->userClient->client_id);
            }elseif(Auth::user()->user_type == 'branch'){
                $shipments = $shipments->where('branch_id', Auth::user()->userBranch->branch_id);
            }
            $shipments = $shipments->orderBy('id','DESC')->paginate(20);
            $actions = new ShipmentActionHelper();
            $actions = $actions->get('all');
            $page_name = translate('Shipments Report Results');
            $status = 'all';
            return view('backend.shipments.shipments-report', compact('shipments', 'page_name', 'type', 'actions', 'status'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $branchs = Branch::where('is_archived', 0)->get();
        $clients = Client::where('is_archived', 0)->get();
        $shipment = Shipment::find($id);
        $deliveryTimes = DeliveryTime::all();
        return view('backend.shipments.edit', compact('branchs', 'clients', 'shipment', 'deliveryTimes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $shipment)
    {
        try {
            DB::beginTransaction();
            $model = Shipment::find($shipment);


            $model->fill($_POST['Shipment']);


            if (!$model->save()) {
                throw new \Exception();
            }
            foreach (\App\PackageShipment::where('shipment_id', $model->id)->get() as $pack) {
                $pack->delete();
            }
            $counter = 0;
            if (isset($_POST['Package'])) {

                if (!empty($_POST['Package'])) {

                    if (isset($_POST['Package'][$counter]['package_id'])) {

                        foreach ($_POST['Package'] as $package) {
                            $package_shipment = new PackageShipment();
                            $package_shipment->fill($package);
                            $package_shipment->shipment_id = $model->id;
                            if (!$package_shipment->save()) {
                                throw new \Exception();
                            }
                        }
                    }
                }
            }

            event(new UpdateShipment($model));
            DB::commit();
            flash(translate("Shipment added successfully"))->success();
            return redirect()->route('admin.shipments.show', $model->id);
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }


    public function covered_countries()
    {
        $countries  = Country::all();
        return  view('backend.shipments.covered_countries', compact('countries'));
    }
    public function covered_cities($country_id)
    {
        $cities  = State::where('country_id', $country_id)->get();
        $country = Country::find($country_id);
        return  view('backend.shipments.covered_cities', compact('cities', 'country'));
    }
    public function config_costs()
    {
        $from_country = $_GET['from_country'];
        $to_country = $_GET['to_country'];
        if($from_country && $to_country){
            $from = Country::find($from_country);
            $to = Country::find($to_country);
            $from_cities = State::where('country_id', $from->id)->where('covered', 1)->get();
            $to_cities = State::where('country_id', $to->id)->where('covered', 1)->get();
            return view('backend.shipments.costs-repeter', compact('from', 'to', 'from_cities', 'to_cities'));
        }else{
            flash(translate("(From Country) and (To Country) are required"))->error();
            return back();
        }
    }
    public function ajax_costs_repeter() {
        $from_country = $_GET['from_country'];
        $to_country = $_GET['to_country'];
        $costBlocks = array();
        $from = Country::find($from_country);
        $to = Country::find($to_country);
        $from_cities = State::where('country_id', $from->id)->where('covered', 1)->get();
        $to_cities = State::where('country_id', $to->id)->where('covered', 1)->get();
        $counter = 0;
        foreach ($from_cities as $city) {
            foreach ($to_cities as $to_city) {
                $from_costs = \App\Cost::where('from_country_id', $from->id)->where('to_country_id', $to->id)->where('from_state_id', $city->id)->where('to_state_id', $to_city->id)->first();
                if ($from_costs != null) {
                    array_push($costBlocks, ['from_country' => $from->name, 'from_country_id' => $from->id, 'to_country' => $to->name, 'to_country_id' => $to->id, 'from_state' => $city->name, 'from_state_id' => $city->id, 'to_state' => $to_city->name, 'to_state_id' => $to_city->id, 'shipping_cost' => $from_costs->shipping_cost, 'mile_cost' => $from_costs->mile_cost, 'tax' => $from_costs->tax, 'delivery_time' => $from_costs->delivery_time, 'return_cost' => $from_costs->return_cost, 'return_mile_cost' => $from_costs->return_mile_cost, 'insurance' => $from_costs->insurance]);
                } else {
                    array_push($costBlocks, ['from_country' => $from->name, 'from_country_id' => $from->id, 'to_country' => $to->name, 'to_country_id' => $to->id, 'from_state' => $city->name, 'from_state_id' => $city->id, 'to_state' => $to_city->name, 'to_state_id' => $to_city->id, 'shipping_cost' => 0, 'tax' => 0, 'delivery_time' => 3, 'return_cost' => 0, 'insurance' => 0]);
                }
            }
        }
        return response()->json($costBlocks);
    }

    public function post_config_costs(Request $request) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        // $costs_removal = Cost::where('from_country_id', $_GET['from_country'])->where('to_country_id', $_GET['to_country'])->get();
        // foreach ($costs_removal as $cost) {
        //     $cost->delete();
        // }
        $counter = 0;
        $from_country = $request->from_country_h[$counter];
        $to_country = $request->to_country_h[$counter];
        $from_state = $request->from_state[$counter];
        $to_state = $request->to_state[$counter];

        $tax = $request->tax[$counter];
        $insurance = $request->insurance[$counter];
        $delivery_time = $request->delivery_time[$counter];
        $newCost = Cost::where('from_country_id', $from_country)->where('to_country_id', $to_country)->first();
        if (!isset($newCost)) {
            $newCost = new Cost();
            $newCost->from_country_id = $from_country;
            $newCost->to_country_id = $to_country;
        }

        if (\App\ShipmentSetting::getVal('is_def_mile_or_fees') == '2') {
            $shipping_cost = $request->shipping_cost[$counter];
            $return_cost = $request->return_cost[$counter];

            $newCost->shipping_cost = $shipping_cost;
            $newCost->return_cost = $return_cost;
        } elseif (\App\ShipmentSetting::getVal('is_def_mile_or_fees') == '1') {
            $mile_cost = $request->mile_cost[$counter];
            $return_mile_cost = $request->return_mile_cost[$counter];

            $newCost->mile_cost = $mile_cost;
            $newCost->return_mile_cost = $return_mile_cost;
        }
        $newCost->tax = $tax;
        $newCost->insurance = $insurance;
        $newCost->delivery_time = $delivery_time;
        $newCost->save();
        $counter = 1;
        foreach ($request->from_country_h as $cost_data) {
            if ($counter < (count($request->from_country_h))) {
                $from_country = $request->from_country_h[$counter];
                $to_country = $request->to_country_h[$counter];

                $from_state = $request->from_state[$counter - 1];
                $to_state = $request->to_state[$counter - 1];


                $tax = $request->tax[$counter];
                $insurance = $request->insurance[$counter];
                $delivery_time = $request->delivery_time[$counter];

                $newCost = Cost::where('from_country_id', $from_country)->where('to_country_id', $to_country)->where('from_state_id', $from_state)->where('to_state_id', $to_state)->first();
                if (!isset($newCost)) {
                    $newCost = new Cost();
                    $newCost->from_country_id = $from_country;
                    $newCost->to_country_id = $to_country;
                    $newCost->from_state_id = $from_state;
                    $newCost->to_state_id = $to_state;
                }

                if (\App\ShipmentSetting::getVal('is_def_mile_or_fees') == '2') {
                    $shipping_cost = $request->shipping_cost[$counter];
                    $return_cost = $request->return_cost[$counter];

                    $newCost->shipping_cost = $shipping_cost;
                    $newCost->return_cost = $return_cost;
                } elseif (\App\ShipmentSetting::getVal('is_def_mile_or_fees') == '1') {
                    $mile_cost = $request->mile_cost[$counter];
                    $return_mile_cost = $request->return_mile_cost[$counter];
                    $newCost->mile_cost = $mile_cost;
                    $newCost->return_mile_cost = $return_mile_cost;
                }
                $newCost->tax = $tax;
                $newCost->insurance = $insurance;
                $newCost->delivery_time = $delivery_time;
                $newCost->save();
                $counter++;
            }
        }
        flash(translate("Costs updated successfully"))->success();
        return redirect()->back();
    }
    public function post_config_package_costs(Request $request)
    {
        $counter = 0;
        foreach ($request->package_id as $package) {
            $pack = Package::find($request->package_id[$counter]);
            $pack->cost = $request->package_extra[$counter];
            $pack->cost_state = $request->package_extra_state[$counter];
            $pack->save();
            $counter++;
        }
        flash(translate("Package Extra Fees updated successfully"))->success();
        return redirect()->back();
    }
    public function post_covered_countries()
    {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $countries = Country::all();
        foreach ($countries as $count) {
            $count->covered = 0;
            $count->save();
        }
        if(isset($_POST['covered_countries'])){
            foreach ($_POST['covered_countries'] as $country_id) {
                $c = Country::find($country_id);
                $c->covered = 1;
                $c->save();
            }
        }
        flash(translate("Covered Countries added successfully"))->success();
        return back();
    }

    public function post_covered_cities($country_id)
    {

        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $countries = State::where('country_id', $country_id)->get();

        foreach ($countries as $count) {
            $count->covered = 0;
            $count->save();
        }
        if(isset($_POST['covered_cities'])){
            foreach ($_POST['covered_cities'] as $state_id) {
                $c = State::find($state_id);
                $c->covered = 1;
                $c->save();
            }
        }
        flash(translate("Covered Cities updated successfully"))->success();
        return back();
    }

    public function import(Request $request)
    {
        $shipment = new Shipment;
        $columns = $shipment->getTableColumns();
        return view('backend.shipments.import',['columns'=>$columns]);
    }

    public function parseImport(Request $request)
    {
        $request->validate([
            'shipments_file' => 'required|mimes:csv,txt',
            "columns"        => "required|array|min:16",
        ]);

        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }

        $path = $request->file('shipments_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        if(count($data[0]) != count($request->columns)){
            flash(translate('This file you are trying to import is not the file that you should upload'))->error();
            return back();
        }

        if(!in_array('type',$request->columns) || !in_array('client_phone',$request->columns) || !in_array('client_address',$request->columns) || !in_array('branch_id',$request->columns) || !in_array('shipping_date',$request->columns) || !in_array('reciver_name',$request->columns) || !in_array('reciver_phone',$request->columns) || !in_array('reciver_address',$request->columns) || !in_array('from_country_id',$request->columns) || !in_array('to_country_id',$request->columns) || !in_array('from_state_id',$request->columns) || !in_array('to_state_id',$request->columns) || !in_array('to_area_id',$request->columns) || !in_array('from_area_id',$request->columns) || !in_array('payment_type',$request->columns) || !in_array('payment_method_id',$request->columns) || !in_array('package_id',$request->columns) ){
            flash(translate('Make Sure All Required Parameters In CSV'))->error();
            return back();
        }

        try {
            unset($data[0]);

            $auth_user = Auth::user();
            foreach ($data as $row) {
                for ($i=0; $i < count($row); $i++) {
                    // Validation
                    if($request->columns[$i] == 'type'){
                        if($row[$i] != '1' && $row[$i] != '2' ){
                            flash(translate('Type invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'branch_id'){
                        if(!Branch::find($row[$i])){
                            flash(translate('Branch invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'client_address'){
                        if(!AddressClient::find($row[$i])){
                            flash(translate('Client Address invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'from_country_id' || $request->columns[$i] == 'to_country_id'){
                        if(!Country::find($row[$i])){
                            flash(translate('Country invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'from_state_id' || $request->columns[$i] == 'to_state_id' ){
                        if(!State::find($row[$i])){
                            flash(translate('State invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'from_area_id' || $request->columns[$i] == 'to_area_id'){
                        if(!Area::find($row[$i])){
                            flash(translate('Area invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'payment_method_id'){
                        if(!BusinessSetting::find($row[$i])){
                            flash(translate('Payment Method Id invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'payment_type'){
                        if($row[$i] != Shipment::POSTPAID && $row[$i] != Shipment::PREPAID){
                            flash(translate('Payment Type Id invalid'))->error();
                            return back();
                        }
                    }

                    if($request->columns[$i] == 'package_id'){
                        if(!Package::find($row[$i])){
                            flash(translate('Package invalid'))->error();
                            return back();
                        }
                    }
                    // End Validation

                    if($request->columns[$i] != 'package_id' && $request->columns[$i] != 'description' && $request->columns[$i] != 'height' && $request->columns[$i] != 'width' && $request->columns[$i] != 'length' && $request->columns[$i] != 'weight' && $request->columns[$i] != 'qty' )
                    {

                        if($request->columns[$i] == 'amount_to_be_collected'){

                            if($row[$i] == "" || $row[$i] == " " || !is_numeric($row[$i]))
                            {
                                $new_shipment[$request->columns[$i]] = 0;
                            }else{
                                $new_shipment[$request->columns[$i]] = $row[$i];
                            }
                        }elseif($request->columns[$i] == 'client_phone'){
                            if($row[$i] == "" || $row[$i] == " ")
                            {
                                $new_shipment[$request->columns[$i]] = $auth_user->userClient->responsible_mobile;
                            }else{
                                $new_shipment[$request->columns[$i]] = $row[$i];
                            }
                        }
                        else {
                            $new_shipment[$request->columns[$i]] = $row[$i];
                        }

                    }else{
                        if($request->columns[$i] == 'package_id')
                        {
                            $new_package[$request->columns[$i]] = intval($row[$i]);
                        }else{
                            if($request->columns[$i] != 'description')
                            {
                                if($row[$i] == "" || $row[$i] == " " || !is_numeric($row[$i]))
                                {
                                    $new_package[$request->columns[$i]] = 1;

                                    if($request->columns[$i] == 'weight'){
                                        $new_shipment['total_weight'] = 1;
                                    }
                                }else{
                                    $new_package[$request->columns[$i]] = $row[$i];
                                    if($request->columns[$i] == 'weight'){
                                        $new_shipment['total_weight'] = $row[$i];
                                    }
                                }
                            }else {
                                $new_package[$request->columns[$i]] = $row[$i];
                            }
                        }

                    }

                }
                $request['Shipment'] = $new_shipment;

                $packages[0] = $new_package;
                $request['Package'] = $packages;

                $this->storeShipment($request , $auth_user->api_token);
            }

            flash(translate("File imported successfully"))->success();
            return back();
        } catch (\Throwable $th) {
            dd($th);
            flash(translate('This file you are trying to import is not the file that you should upload'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function shipmentCalc()
    {
        $branchs = Branch::where('is_archived', 0)->get();
        return view('backend.shipments.shipment_calc' , compact('branchs') );
    }
    public function calcStore(Request $request)
    {
        $ClientController = new ClientController();
        $shipment = $request->Shipment;
        if($request->if_have_account == '1')
        {
            $client = Client::where('email', $request->client_email)->first();
            
            $userLogin = User::where('email', $request->client_email)->first();
            Auth::loginUsingId($userLogin->id);
        }elseif($request->if_have_account == '0'){
            // Add New Client
            $request->request->add(['name' => $request->client_name ]);
            $request->request->add(['password' => $request->client_password ]);
            $request->request->add(['password_confirmation' => $request->client_password ]);
            $request->request->add(['email' => $request->client_email ]);
            $request->request->add(['condotion_agreement' => '1' ]);
            $request->request->add(['responsible_mobile' => $request->Shipment['client_phone'] ]);
            $request->request->add(['branch_id' => $request->Shipment['branch_id'] ]);

            $client = $ClientController->save($request , $calc = true);
        }
        if($client)
        {
            $shipment['client_id']    = $client->id;
            $shipment['client_phone'] = $client->responsible_mobile;

            // Add New Client Address
            $request->client_id =  $client->id;
            $request->address   =  $request->client_address;
            $request->country   =  $request->Shipment['from_country_id'];
            $request->state     =  $request->Shipment['from_state_id'];
            if(isset($request->area))
            {
                $request->area      =  $request->Shipment['from_area_id'];
            }
            $new_address        = $ClientController->addNewAddress($request , $calc = true);

            if($new_address)
            {
                $shipment['client_address'] = $new_address->id;
            }

        }
        $request->Shipment = $shipment;
        $model = $this->storeShipment($request);
        flash(translate("Shipment added successfully"))->success();
        return redirect()->route('admin.shipments.show', $model->id);
    }

    public function BarcodeScanner()
    {
        return view('backend.shipments.barcode_scanner');
    }
    public function ChangeStatusByBarcode(Request $request)
    {

        $shipment = Shipment::where('code',$request->barcode)->first();
        if($shipment)
        {
            $request->request->add(['checked_ids' => [$shipment->id] ]);
            $staff_permission = json_decode(Auth::user()->staff->role->permissions ?? "[]");
            if(Auth::user()->user_type == 'captain'){

                if ($shipment->status_id == Shipment::REQUESTED_STATUS || $shipment->status_id == Shipment::CAPTAIN_ASSIGNED_STATUS) {
                    $to = Shipment::RECIVED_STATUS;
                    $action = new StatusManagerHelper();
                    $response = $action->change_shipment_status($request->checked_ids, $to, $shipment->mission_id);
                    if ($response['success']) {
                        event(new ShipmentAction($to,$request->checked_ids));
                        flash(translate("Status Changed Successfully!"))->success();
                        return back();
                    }

                }else{
                    flash(translate("Can't Change This Shipment ".$request->barcode))->error();
                    return back();
                }

            } elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'branch' || in_array('1109', $staff_permission)) {

                if( $shipment->status_id == Shipment::RECIVED_STATUS)
                {
                    $to = Shipment::APPROVED_STATUS;
                    $action = new StatusManagerHelper();
                    $response = $action->change_shipment_status($request->checked_ids, $to);
                    if ($response['success']) {
                        event(new ShipmentAction($to,$request->checked_ids));
                        flash(translate("Status Changed Successfully!"))->success();
                        return back();
                    }
                }elseif($shipment->status_id == Shipment::RETURNED_STATUS)
                {
                    $to = Shipment::RETURNED_STOCK;
                    $action = new StatusManagerHelper();
                    $response = $action->change_shipment_status($request->checked_ids, $to);
                    if ($response['success']) {
                        event(new ShipmentAction($to,$request->checked_ids));
                        flash(translate("Status Changed Successfully!"))->success();
                        return back();
                    }

                }else
                {
                    flash(translate("Can't Change This Shipment ".$request->barcode))->error();
                    return back();
                }
            }
        }else{
            flash(translate('No Shipment With This Barcode '.$request->barcode))->error();
            return back();
        }
    }

    public function exportShipments(Request $request)
    {
        $excelName = '';
        $time_zone = BusinessSetting::where('type', 'timezone')->first();
        if($time_zone->value == null){
            $excelName = 'export_shipments_'.Carbon::now()->toDateString().'.xlsx';
        }else {
            $excelName = 'export_shipments_'.Carbon::now($time_zone->value)->toDateString().'.xlsx';
        }

        return Excel::download( new ShipmentsExportExcel($request) , $excelName );
    }


}
