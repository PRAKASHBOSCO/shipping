<?php

namespace App\Http\Helpers;

use App\ClientShipmentLog;
use App\Mission;
use App\Client;
use App\Shipment;
use App\Transaction;
use App\Http\Helpers\ShipmentPRNG;
use DB;

class MissionStatusManagerHelper
{


    //Mission Status Manager

    public function change_mission_status($missions, $to, $captain_id = null,$params=array())
    {

        $response = array();
        $response['success'] = 1;
        $response['error_msg'] = '';
        try {
            DB::beginTransaction();
            $transaction = new TransactionHelper();
            $INVOICE_PAYMENT = \App\BusinessSetting::where("key","payment_gateway")->where("type","invoice_payment")->first()->id;
            foreach ($missions as $mission_id) {
                $mission = Mission::find($mission_id);
                if($mission->status_id == $to)
                {
                    throw new \Exception("Out of status changer scope");
                }
                if ($mission != null) {

                    $oldStatus = $mission->status_id;

                    if ($to == Mission::APPROVED_STATUS) {

                        if ($captain_id != null) {
                            $mission->captain_id = $captain_id;

                            if(isset($params['due_data']))
                            {
                                $mission->due_date = $params['due_data'];
                            }
                        } else {
                            throw new \Exception("Captain is required in this step");
                        }

                    }

                    if ($to == Mission::RECIVED_STATUS) {
                        foreach ($mission->shipment_mission as $shipment_mission)
                        {
                            $shipment_mission->shipment->otp  = ShipmentPRNG::get();
                            $shipment_mission->shipment->save();
                        }


                        if ($mission->getOriginal('type') == Mission::RETURN_TYPE) {

                            $client   = \App\Client::where('id',$mission->client_id)->first();
                            $gateways[] = 'sms';
                            $data = array(
                                'phone'   =>  $client->responsible_mobile ,
                                'message'   =>  array(
                                    'subject'   =>  'this is your mission otp '. $mission->otp  ,
                                ),
                            );
                            $send_sms_otp = new \App\Notifications\GlobalNotification($data, $gateways);
                            if(session()->has('sms_error'))
                            {
                                flash(translate('Notification sms not sent please check sms verification'))->error();
                                Session::forget('sms_error');
                            }
                        }

                        if ($mission->getOriginal('type') == Mission::DELIVERY_TYPE) {

                            foreach ($mission->shipment_mission as $shipment_mission)
                            {

                                $shipment   = \App\Shipment::where('id',$shipment_mission->shipment->id)->first();
                                $gateways[] = 'sms';
                                $data = array(
                                    'phone'   =>  $shipment->reciver_phone ,
                                    'message'   =>  array(
                                        'subject'   =>  'this is your shipment otp '. $shipment->otp  ,
                                    ),
                                );
                                $send_sms_otp = new \App\Notifications\GlobalNotification($data, $gateways);
                            }
                            if(session()->has('sms_error'))
                            {
                                flash(translate('Notification sms not sent please check sms verification'))->error();
                                Session::forget('sms_error');
                            }

                            //Hook shipment backend in Mission status changed
                            if (\Schema::hasTable('shipment_mission') && class_exists("\App\ShipmentMission") && class_exists("\App\Shipment") && class_exists("\App\Http\Helpers\StatusManagerHelper")) {

                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                    $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::RECIVED_STATUS, $mission->id);
                                }
                            }
                        }
                        
                        if ($mission->getOriginal('type') == Mission::TRANSFER_TYPE || $mission->getOriginal('type') == Mission::PICKUP_TYPE) {

                            //Hook shipment backend in Mission status changed
                            if (\Schema::hasTable('shipment_mission') && class_exists("\App\ShipmentMission") && class_exists("\App\Shipment") && class_exists("\App\Http\Helpers\StatusManagerHelper")) {

                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                    $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::RECIVED_STATUS, $mission->id);
                                }
                            }
                        }

                        if(isset($params['amount']))
                        {

                            $mission->amount = $params['amount'];

                            if ($mission->getOriginal('type') == Mission::PICKUP_TYPE) {

                                if($mission->shipment_mission[0]->shipment->payment_method_id == $INVOICE_PAYMENT)
                                {
                                    $transaction->create_mission_transaction($mission->id,$params['amount'],Transaction::CLIENT,$mission->client_id,Transaction::CREDIT);
                                }else{
                                    $transaction->create_mission_transaction($mission->id,$params['amount'],Transaction::CAPTAIN,$mission->captain_id,Transaction::CREDIT);
                                }
                            }


                        }

                        if($mission->getOriginal('type')  == Mission::SUPPLY_TYPE)
                        {
                            $amount_to_bo_collected = 0 ;
                            foreach ($mission->shipment_mission as $shipment_mission)
                            {
                                $amount_to_bo_collected += $shipment_mission->shipment->amount_to_be_collected;
                            }
                            $transaction->create_mission_transaction($mission->id,$amount_to_bo_collected ,Transaction::CAPTAIN,$mission->captain_id,Transaction::CREDIT);
                        }


                        // comment this after moveing Move confirm amount and signature and otp to be in "Received Missions"


                        // if ($mission->getOriginal('type') == Mission::TRANSFER_TYPE) {
                        //     foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                        //         $shipment = \App\Shipment::find($shipment_id);
                        //         $oldClientStatus = $shipment->client_status;
                        //         $shipment->client_status = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                        //         $log = new ClientShipmentLog();
                        //         $log->from = $oldClientStatus;
                        //         $log->to = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                        //         $log->shipment_id = $shipment->id;
                        //         $log->created_by = \Auth::user()->id;
                        //         $log->save();
                        //     }
                        // }
                        // if ($mission->getOriginal('type') == Mission::DELIVERY_TYPE) {
                        //     if (\Schema::hasTable('shipment_mission') && class_exists("\App\ShipmentMission") && class_exists("\App\Shipment") && class_exists("\App\Http\Helpers\StatusManagerHelper")) {
                        //         foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                        //             $shipment = \App\Shipment::find($shipment_id);
                        //             $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                        //             $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::RECIVED_STATUS);
                        //         }
                        //     }
                        // }

                        // $transaction->create_mission_transaction($mission->id,$mission->amount,Transaction::CAPTAIN,$mission->captain_id,Transaction::DEBIT);
                    }
                    if ($to == Mission::DONE_STATUS) {

                        if ($mission->getOriginal('type') == Mission::PICKUP_TYPE) {
                            if($mission->shipment_mission[0]->shipment->payment_method_id != $INVOICE_PAYMENT)
                            {
                                if(\App\ShipmentSetting::getVal('mission_done_with_fees_received')=='1' || \App\ShipmentSetting::getVal('mission_done_with_fees_received') == null ){
                                    $transaction->create_mission_transaction($mission->id,$mission->amount,Transaction::CAPTAIN,$mission->captain_id,Transaction::DEBIT);
                                }
                            }
                        }

                        if($mission->getOriginal('type')  == Mission::SUPPLY_TYPE)
                        {
                            $amount_to_bo_collected = 0 ;
                            foreach ($mission->shipment_mission as $shipment_mission)
                            {
                                $amount_to_bo_collected += $shipment_mission->shipment->amount_to_be_collected;
                            }
                            $client = $mission->client;
                            if($mission->shipment_mission[0]->shipment->payment_method_id == $INVOICE_PAYMENT)
                            {
                                $transaction->create_mission_transaction($mission->id,$client->supply_cost,Transaction::CLIENT,$mission->client_id,Transaction::CREDIT);
                            }else{
                                $transaction->create_mission_transaction($mission->id,$client->supply_cost,Transaction::CAPTAIN,$mission->captain_id,Transaction::CREDIT);
                            }

                            $transaction->create_mission_transaction($mission->id,$amount_to_bo_collected,Transaction::CAPTAIN,$mission->captain_id,Transaction::DEBIT);
                            $transaction->create_mission_transaction($mission->id,$amount_to_bo_collected,Transaction::CLIENT,$mission->client_id,Transaction::DEBIT);
                        }


                        if ($mission->getOriginal('type') == Mission::TRANSFER_TYPE) {
                            foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                $shipment = \App\Shipment::find($shipment_id);
                                $oldClientStatus = $shipment->client_status;
                                $shipment->client_status = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                                $shipment->status_id = Shipment::APPROVED_STATUS;
                                $shipment->save();
                                $log = new ClientShipmentLog();
                                $log->from = $oldClientStatus;
                                $log->to = Shipment::CLIENT_STATUS_RECEIVED_BRANCH;
                                $log->shipment_id = $shipment->id;
                                $log->created_by = \Auth::user()->id;
                                $log->save();
                            }
                        }
                        if ($mission->getOriginal('type') == Mission::DELIVERY_TYPE) {
                            // $captain_amount = $transaction->calcMissionShipmentsAmount($mission->getOriginal('type'), $mission->id);

                            // $amount_to_bo_collected = 0 ;
                            // foreach ($mission->shipment_mission as $shipment_mission)
                            // {
                            //     $amount_to_bo_collected += $shipment_mission->shipment->amount_to_be_collected;
                            // }

                            $captain_amount = $params['shipment_id']['shipping_cost'] + $params['shipment_id']['tax'] + $params['shipment_id']['insurance'] + $params['shipment_id']['amount_to_be_collected'];

                            $transaction->create_mission_transaction($mission->id,$captain_amount,Transaction::CAPTAIN,$mission->captain_id,Transaction::CREDIT);
                            $transaction->create_mission_transaction($mission->id,$params['shipment_id']['amount_to_be_collected'],Transaction::CLIENT,$params['shipment_id']['client_id'],Transaction::CREDIT);

                            //change shipment status to DELIVERED
                            $action = new StatusManagerHelper();
                            $response = $action->change_shipment_status([$params['shipment_id']['id']], Shipment::DELIVERED_STATUS, $mission->id);

                            // if($shipment_mission = $mission->shipment_mission_by_shipment_id($params['shipment_id']['id'])){
                            //     $shipment_mission->delete();
                            // }

                            // if (\Schema::hasTable('shipment_mission') && class_exists("\App\ShipmentMission") && class_exists("\App\Shipment") && class_exists("\App\Http\Helpers\StatusManagerHelper")) {
                            //     foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                            //         $shipment = \App\Shipment::find($shipment_id);
                            //         $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                            //         $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::RECIVED_STATUS);
                            //     }
                            // }
                        }


                        if (\Schema::hasTable('shipment_mission') && class_exists("\App\ShipmentMission") && class_exists("\App\Shipment") && class_exists("\App\Http\Helpers\StatusManagerHelper")) {
                            if ($mission->getOriginal('type') == Mission::PICKUP_TYPE) {
                                //Hook shipment backend in Mission status changed

                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                    $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::APPROVED_STATUS);
                                }

                            }

                            if ($mission->getOriginal('type') == Mission::DELIVERY_TYPE) {
                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    if($shipment->status_id != \App\Shipment::CAPTAIN_ASSIGNED_STATUS)
                                    {
                                        if($shipment->status_id == \App\Shipment::RETURNED_STATUS){
                                            $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                            $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::RETURNED_STOCK);
                                        }
                                        // else{
                                        //     $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                        //     $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::DELIVERED_STATUS);
                                        // }
                                    }
                                }
                            }

                            if ($mission->getOriginal('type') == Mission::TRANSFER_TYPE) {
                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    $oldClientStatus = $shipment->client_status;
                                    $shipment->prev_branch = $shipment->branch_id;
                                    $shipment->branch_id = $mission->to_branch_id;
                                    $shipment->save();
                                    $shipment->client_status = Shipment::CLIENT_STATUS_TRANSFERED;
                                    $log = new ClientShipmentLog();
                                    $log->from = $oldClientStatus;
                                    $log->to = Shipment::CLIENT_STATUS_TRANSFERED;
                                    $log->shipment_id = $shipment->id;
                                    $log->created_by = \Auth::user()->id;
                                    $log->save();
                                }
                            }

                            if ($mission->getOriginal('type') == Mission::RETURN_TYPE) {
                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    if($shipment->status_id == \App\Shipment::RETURNED_STOCK){
                                        $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                        $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::RETURNED_CLIENT_GIVEN);
                                    }
                                }
                            }

                            if ($mission->getOriginal('type') == Mission::SUPPLY_TYPE) {
                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                    $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::SUPPLIED_STATUS);
                                }
                            }
                        }
                        // if(in_array($mission->getOriginal('type'),[Mission::PICKUP_TYPE,Mission::DELIVERY_TYPE,Mission::RETURN_TYPE,Mission::SUPPLY_TYPE]))
                        // {
                        //     $transaction->create_mission_transaction($mission->id,$mission->amount,Transaction::CAPTAIN,$mission->captain_id,Transaction::CREDIT);
                        //     $transaction->create_mission_transaction($mission->id,$mission->amount,Transaction::CLIENT,$mission->client_id,Transaction::CREDIT);
                        // }

                    }

                    if($mission->getOriginal('type') == Mission::DELIVERY_TYPE && $to == Mission::DONE_STATUS )
                    {
                        $mission->status_id = $mission->status_id;
                    }else{
                        $mission->status_id = $to;
                    }

                    if (!$mission->save()) {
                        throw new \Exception("can't change mission status");
                    }
                    //After change action
                    if ($to == Mission::APPROVED_STATUS) {
                        if ($mission->getOriginal('type') == Mission::PICKUP_TYPE) {

                        }
                        if ($mission->getOriginal('type') == Mission::DELIVERY_TYPE || $mission->getOriginal('type') == Mission::TRANSFER_TYPE) {

                            //Hook shipment backend in Mission status changed
                            if (\Schema::hasTable('shipment_mission') && class_exists("\App\ShipmentMission") && class_exists("\App\Shipment") && class_exists("\App\Http\Helpers\StatusManagerHelper")) {

                                foreach (\App\ShipmentMission::where('mission_id', $mission->id)->pluck('shipment_id') as $shipment_id) {
                                    $shipment = \App\Shipment::find($shipment_id);
                                    $change_status_to_be_approved = new \App\Http\Helpers\StatusManagerHelper();
                                    $change_status_to_be_approved->change_shipment_status([$shipment->id], \App\Shipment::CAPTAIN_ASSIGNED_STATUS, $mission->id);
                                }
                            }
                        }
                    }


                    if ($to == Mission::CLOSED_STATUS || $to == Mission::DONE_STATUS )
                    {
                        if($mission->getOriginal('type') == Mission::DELIVERY_TYPE)
                        {
                            $shipments = \App\Shipment::where('id',$params['shipment_id']['id'])->get();

                            $shipments_mission = \App\Shipment::where('mission_id', $params['shipment_id']['mission_id'])->count();
                            if($shipments_mission == 1)
                            {
                                $mission->status_id = $to;
                                if (!$mission->save()) {
                                    throw new \Exception("can't change mission status");
                                }
                            }

                        }else{
                            $shipments = \App\Shipment::where('mission_id',$mission_id)->get();
                        }

                        foreach ($shipments as $shipment)
                        {
                            $shipment->mission_id = null ;
                            $shipment->save();
                        }


                    }

                } else {
                    throw new \Exception("There is no mission with this Code");
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            //echo $e->getMessage();exit;
            DB::rollback();
            $response['success'] = 0;
            $response['error_msg'] = $e->getMessage();
        }
        return $response;
    }
}
