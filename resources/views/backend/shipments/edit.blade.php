@extends('backend.layouts.app')

@section('style')
    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
        }

        /* Firefox */
        input[type=number] {
        -moz-appearance: textfield;
        }
    </style>
@endsection

@section('content')
@php
    $auth_user = Auth::user();
    $user_type = Auth::user()->user_type;
    $checked_google_map = \App\BusinessSetting::where('type', 'google_map')->first();
    $staff_permission = json_decode(Auth::user()->staff->role->permissions ?? "[]");
    $countries = \App\Country::where('covered',1)->get();

    $is_def_mile_or_fees = \App\ShipmentSetting::getVal('is_def_mile_or_fees');
    // is_def_mile_or_fees if result 1 for mile if result 2 for fees

    if($user_type == 'customer')
    {
        $user_client = Auth::user()->userClient->client_id;
    }

    $addressess = \App\AddressClient::where('client_id', $shipment->client_id)->get();
@endphp
<style>
    label {
        font-weight: bold !important;
    }
</style>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Shipment Info')}}</h5>
        </div>
        <form class="form-horizontal" action="{{ route('admin.shipments.update-shipment',['shipment'=>$shipment->id]) }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            {{ method_field('PATCH') }}
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label class="col-2 col-form-label">{{translate('Shipment Type')}}</label>
                            <div class="col-9 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio-success btn btn-default">
                                        <input type="radio" name="Shipment[type]" @if($shipment->type == \App\Shipment::getType(\App\Shipment::PICKUP)) checked @endif value="{{\App\Shipment::PICKUP}}" />
                                        <span></span>
                                        {{translate("Pickup (For door to door delivery)")}}
                                    </label>
                                    <label class="radio radio-success btn btn-default">
                                        <input type="radio" name="Shipment[type]" @if($shipment->type == \App\Shipment::getType(\App\Shipment::DROPOFF)) checked @endif value="{{\App\Shipment::DROPOFF}}" />
                                        <span></span>
                                        {{translate("Drop off (For delivery package from branch directly)")}}
                                    </label>
                                </div>

                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('From Branch')}}:</label>
                                    <select class="form-control kt-select2 select-branch" name="Shipment[branch_id]">
                                        <option></option>
                                        @foreach($branchs as $branch)
                                            <option @if($shipment->branch_id == $branch->id) selected @endif value="{{$branch->id}}">{{$branch->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('To Branch')}}:</label>
                                    <select class="form-control kt-select2 select-branch" name="Shipment[to_branch_id]">
                                        <option></option>
                                        @foreach($branchs as $branch)
                                            <option @if($shipment->to_branch_id == $branch->id) selected @endif value="{{$branch->id}}">{{$branch->name}}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{translate('Delivery Method')}}:</label>
                                    <select class="form-control kt-select2 payment-method" id="delivery_method_id" name="Shipment[delivery_method_id]">
                                            <option @if($shipment->delivery_method_id == 1) selected @endif value="1" selected>{{translate('Branch Pickup')}}</option>
                                            <option @if($shipment->delivery_method_id == 2) selected @endif value="2">{{translate('Door Delivery')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if(\App\ShipmentSetting::getVal('is_date_required') == '1' || \App\ShipmentSetting::getVal('is_date_required') == null)
                                <div class="form-group">
                                    <label>{{translate('Shipping Date')}}:</label>
                                    <div class="input-group date">
                                        <input type="text" placeholder="{{translate('Shipping Date')}}" value="{{$shipment->shipping_date}}" name="Shipment[shipping_date]" autocomplete="off" class="form-control" id="kt_datepicker_3" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="la la-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{translate('Customer Type')}}:</label>
                                    <select class="form-control kt-select2 payment-method" onchange="selectIsTriggered()" id="customer_type" name="Shipment[customer_type]">
                                        <option @if($shipment->customer_type == 'walkin') selected @endif value="walkin">{{translate('Walkin Customer')}}</option>
                                        <option @if($shipment->customer_type == 'corporate') selected @endif value="corporate" selected >{{translate('Corporate Customer')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{translate('Parcel Type')}}:</label>
                                    <select class="form-control kt-select2 payment-method" id="parcel_type" onchange="decideCODInput();" name="Shipment[parcel_type]">
                                        <option @if($shipment->parcel_type == 'cod') selected @endif value="cod" >{{translate('COD')}}</option>
                                        <option @if($shipment->parcel_type == 'noncod') selected @endif value="noncod" selected>{{translate('Non COD')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group client-select" id="corporate_customer_container">
                                    <label>{{translate('Client/Sender')}}:</label>
                                    <select class="form-control kt-select2 select-client" id="client-id" name="Shipment[client_id]">
                                        <option></option>
                                        @foreach($clients as $client)
                                        <option @if($shipment->client_id == $client->id) selected @endif value="{{$client->id}}" data-phone="{{$client->responsible_mobile}}">{{$client->responsible_mobile}} ({{$client->name}})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group client-select" id="walkin_customer_container" style="display: none;">
                                    <label>{{translate('Client/Sender')}}:</label>
                                    <input type="text" placeholder="{{translate('Client/Sender')}}" value="{{$shipment->walkin_customer_name}}" class="form-control" name="Shipment[walkin_customer_name]"  />
                                </div>
                            </div>
                            @if($auth_user->user_type == "customer")
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{translate('Client Phone')}}:</label>
                                            @php
                                                $client_phone = \App\Client::select('responsible_mobile')->where('id', $auth_user->userClient->client_id)->first();
                                            @endphp
                                            <input placeholder="{{translate('Client Phone')}}" name="Shipment[client_phone]" id="client_phone" value="{{$shipment->responsible_mobile}}" class="form-control" />
                                    </div>
                                </div>
                            @else
                                <input type="hidden" placeholder="{{translate('Client Phone')}}" value="{{$shipment->client_phone}}" name="Shipment[client_phone]" id="client_phone" class="form-control" />
                            @endif
                            <div class="col-md-4" id="walkin_customer_phone_container" style="display: none;">
                                <div class="form-group">
                                    <label>{{translate('Client Phone')}}:</label>
                                    <input placeholder="{{translate('Client Phone')}}" value="{{$shipment->client_phone}}" name="Shipment[client_phone]" class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{translate('Taxable')}}?</label>
                                    <select class="form-control select-country" name="Shipment[taxable]" id="taxable">
                                        <option @if($shipment->taxable == 0) selected @endif value="0">No</option>
                                        <option @if($shipment->taxable == 1) selected @endif value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{translate('Client Address')}}:</label>
                                    <div id="corporate_customer_address">
                                        <select id="client-addressess" name="Shipment[client_address]" class="form-control select-address">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                    <input placeholder="{{translate('Client Address')}}" value="{{$shipment->walkin_client_address}}" name="Shipment[walkin_client_address]" id="walkin_client_address" class="form-control" style="display: none;" />
                                </div>
                            </div>
                            <div class="p-3 mb-4 col-md-12" id="show_address_div" style="border: 1px solid #e4e6ef; display:none">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{translate('Country')}}:</label>
                                                <select id="change-country-client-address" name="country_id" class="form-control select-country">
                                                    <option value=""></option>
                                                    @foreach($countries as $country)
                                                    <option value="{{$country->id}}">{{$country->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{translate('Region')}}:</label>
                                                <select id="change-state-client-address" name="state_id" class="form-control select-state">
                                                    <option value=""></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>{{translate('Area')}}:</label>
                                        <select name="area_id" style="display: block !important;" class="form-control select-area">
                                            <option value=""></option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{translate('Address')}}:</label>
                                        <input type="text" placeholder="{{translate('Address')}}" name="client_address" class="form-control" required/>
                                    </div>
                                    @if($checked_google_map->value == 1 )
                                        <div class="location-client">
                                            <label>{{translate('Location')}}:</label>
                                            <input type="text" class="form-control address-client " placeholder="{{translate('Location')}}" name="client_street_address_map"  rel="client" value="" />
                                            <input type="hidden" class="form-control lat" data-client="lat" name="client_lat" />
                                            <input type="hidden" class="form-control lng" data-client="lng" name="client_lng" />
                                            <input type="hidden" class="form-control url" data-client="url" name="client_url" />

                                            <div class="mt-2 col-sm-12 map_canvas map-client" style="width:100%;height:300px;"></div>
                                            <span class="form-text text-muted">{{'Change the pin to select the right location'}}</span>
                                        </div>
                                    @endif
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-primary" onclick="AddNewClientAddress()">{{translate('Save')}}</button>
                                        <button type="button" class="btn btn-secondary" onclick="closeAddressDiv()">{{translate('Close')}}</button>
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('Receiver Name')}}:</label>
                                    <input type="text" placeholder="{{translate('Receiver Name')}}" value="{{$shipment->reciver_name}}" name="Shipment[reciver_name]" class="form-control" />

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('Receiver Phone')}}:</label>
                                    <input type="text" placeholder="{{translate('Receiver Phone')}}" value="{{$shipment->reciver_phone}}" name="Shipment[reciver_phone]" class="form-control" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{translate('Receiver Address')}}:</label>
                                    <input type="text" placeholder="{{translate('Receiver Address')}}" value="{{$shipment->reciver_address}}" name="Shipment[reciver_address]" class="form-control" />
                                </div>
                            </div>

                            @if($checked_google_map->value == 1 )
                                <div class="col-md-12">
                                    <div class="location-receiver">
                                        <label>{{translate('Receiver Location')}}:</label>
                                        <input type="text" class="form-control address-receiver" value="{{$shipment->reciver_street_address_map}}" placeholder="{{translate('Receiver Location')}}" name="Shipment[reciver_street_address_map]"  rel="receiver" value="" />
                                        <input type="hidden" class="form-control lat" data-receiver="lat" value="{{$shipment->reciver_lat}}" name="Shipment[reciver_lat]" />
                                        <input type="hidden" class="form-control lng" data-receiver="lng" value="{{$shipment->reciver_lng}}" name="Shipment[reciver_lng]" />
                                        <input type="hidden" class="form-control url" data-receiver="url" value="{{$shipment->reciver_url}}" name="Shipment[reciver_url]" />

                                        <div class="mt-2 col-sm-12 map_canvas map-receiver" style="width:100%;height:300px;"></div>
                                        <span class="form-text text-muted">{{'Change the pin to select the right location'}}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('Payment Type')}}:</label>
                                    <select class="form-control kt-select2 payment-type" id="payment_type" name="Shipment[payment_type]">
                                        <option @if($shipment->payment_type=='1' ) selected @endif value="1">{{translate('Postpaid')}}</option>
                                        <option @if($shipment->payment_type=='2' ) selected @endif value="2">{{translate('Prepaid')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{translate('Payment Method')}}:</label>
                                    <select class="form-control kt-select2 payment-method" id="payment_method_id" name="Shipment[payment_method_id]">
                                        @forelse (\App\BusinessSetting::where("key","payment_gateway")->where("value","1")->get() as $gateway)
                                            <option value="{{$gateway->id}}" @if($shipment->payment_method_id == $gateway->id) selected @endif>{{$gateway->name}}</option>
                                        @empty
                                            <option value="11">{{translate('Cash')}}</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{translate('Order ID')}}:</label>
                                    <input type="text" placeholder="{{translate('Order ID')}}" name="Shipment[order_id]" class="form-control" value="{{$shipment->order_id}}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{translate('Attachments')}}:</label>

                                    <div class="input-group " data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="Shipment[attachments_before_shipping]" class="selected-files" value="{{$shipment->attachments_before_shipping}}" max="3">
                                    </div>
                                    <div class="file-preview">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{translate('Discount(%)')}}:</label>
                                    <input type="text" placeholder="{{translate('Discount')}}" name="Shipment[discount]" value="{{$shipment->discount}}" class="form-control" />
                                </div>
                            </div>
                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{translate('Attachments After Shipping')}}:</label>

                                    <div class="input-group " data-toggle="aizuploader" data-type="image" data-multiple="true">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                        </div>
                                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                        <input type="hidden" name="Shipment[attachments_after_shipping]" class="selected-files" value="{{$shipment->attachments_after_shipping}}" max="3">
                                    </div>
                                    <div class="file-preview">
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                        <hr>

                        <div id="kt_repeater_1">
                            <div class="row" id="">
                                <h2 class="text-left">{{translate('Package Info')}}:</h2>
                                <div data-repeater-list="Package" class="col-lg-12">
                                    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $pack)
                                    <div data-repeater-item class="row align-items-center" style="margin-top: 15px;padding-bottom: 15px;padding-top: 15px;border-top:1px solid #ccc;border-bottom:1px solid #ccc;">

                                        <div class="col-md-3">
                                            <label>{{translate('Package Type')}}:</label>
                                            <select class="form-control kt-select2 package-type-select" name="package_id" >
                                                <option></option>
                                                @foreach(\App\Package::all() as $package)
                                                <option @if($pack->package_id == $package->id) selected @endif value="{{$package->id}}">{{$package->name}}</option>
                                                @endforeach
                                            </select>
                                            <div class="mb-2 d-md-none"></div>
                                        </div>

                                        <div class="col-md-3">
                                            <label>{{translate('description')}}:</label>
                                            <input type="text" placeholder="{{translate('description')}}" value="{{$pack->description}}" class="form-control" name="description">
                                            <div class="mb-2 d-md-none"></div>
                                        </div>

                                        <div class="col-md-3">
                                            <label>{{translate('Quantity')}}:</label>
                                            <input type="text" name="qty" class="form-control kt_touchspin_qty" value="{{$pack->qty}}" />
                                            <div class="mb-2 d-md-none"></div>
                                        </div>

                                        <div class="col-md-3">
                                            <label>{{translate('Weight')}}:</label>
                                            <input type="text" name="weight" class="form-control weight-listener kt_touchspin_weight" value="{{$pack->weight}}" onchange="calcTotalWeight()" />
                                            <div class="mb-2 d-md-none"></div>
                                        </div>


                                        <div class="col-md-12" style="margin-top: 10px;">
                                            <label>{{translate('Dimensions [Length x Width x Height] (cm):')}}:</label>
                                        </div>
                                        <div class="col-md-2">

                                            <input class="dimensions_r" type="text" class="form-control" placeholder="{{translate('Length')}}" value="{{$pack->length}}"  name="length"/>

                                        </div>
                                        <div class="col-md-2">

                                            <input class="dimensions_r" type="text" class="form-control" placeholder="{{translate('Width')}}" value="{{$pack->width}}" name="width" />

                                        </div>
                                        <div class="col-md-2">

                                            <input class="dimensions_r" type="text" class="form-control " placeholder="{{translate('Height')}}" value="{{$pack->height}}" name="height" />

                                        </div>


                                        <div class="row">
                                            <div class="col-md-12">

                                                <div>
                                                    <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">
                                                        <i class="la la-trash-o"></i>{{translate('Delete')}}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="">
                                    <label class="text-right col-form-label">{{translate('Add')}}</label>
                                    <div>
                                        <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                            <i class="la la-plus"></i>{{translate('Add')}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row" id="cod_container" style="display: none;">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{translate('Amount to be Collected')}}:</label>
                                        <input id="kt_touchspin_3" placeholder="{{translate('Amount to be Collected')}}" type="text" min="0" class="form-control" value="0" name="Shipment[amount_to_be_collected]" value="{{$shipment->amount_to_be_collected}}" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{translate('Delivery Time')}}:</label>
                                        <div class="input-group date">
                                            <input type="text" placeholder="{{translate('Delivery Time')}}" value="{{$shipment->delivery_time}}" name="Shipment[delivery_time]" autocomplete="off" class="form-control" id="delivery_time" />
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <i class="la la-calendar"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{translate('Total Weight')}}:</label>
                                        <input id="kt_touchspin_4" placeholder="{{translate('Total Weight')}}" value="{{$shipment->total_weight}}" type="text" min="1" class="form-control total-weight" value="1" name="Shipment[total_weight]" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    {!! hookView('shipment_addon',$currentView) !!}

                    <div class="mb-0 text-right form-group">
                        <button type="button" class="btn btn-sm btn-primary" onclick="get_estimation_cost()">{{translate('Save')}}</button>

                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-sm btn-primary d-none" data-toggle="modal" data-target="#exampleModalCenter" id="modal_open">
                            {{translate('Save')}}
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">{{translate('Estimation Cost')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="modal_close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="text-left modal-body">
                                        <div class="row">
                                            @if($is_def_mile_or_fees=='2')
                                                <div class="col-6">{{translate('Shipping Weight Cost')}} :</div>
                                                <div class="col-6" id="shipping_cost"></div>
                                            @elseif($is_def_mile_or_fees=='1')
                                                <div class="col-6">{{translate('Mile Weight Cost')}} :</div>
                                                <div class="col-6" id="mile_cost"></div>
                                            @endif
                                        </div>
                                        <div class="row">
                                            @if($is_def_mile_or_fees=='2')
                                                <div class="col-6">{{translate('Shipping Distance Cost')}} :</div>
                                                <div class="col-6" id="shipping_distance_cost"></div>
                                            @elseif($is_def_mile_or_fees=='1')
                                                <div class="col-6">{{translate('Mile Distance Cost')}} :</div>
                                                <div class="col-6" id="mile_distance_cost"></div>
                                            @endif
                                        </div>
                                        <div class="row" id="pickup_cost_container">
                                            <div class="col-6">{{translate('Pickup Cost')}} :</div>
                                            <div class="col-6" id="pickup_cost"></div>
                                        </div>
                                        <div class="row" id="taxable_container">
                                            <div class="col-6">{{translate('Tax & Duty')}} :</div>
                                            <div class="col-6" id="tax_duty"></div>
                                        </div>
                                        <div class="row" id="cod_fees_container">
                                            <div class="col-6">{{translate('COD Fees')}} :</div>
                                            <div class="col-6" id="cod_fees"></div>
                                        </div>
                                        <div class="row" id="door_delivery_fees_container">
                                            <div class="col-6">{{translate('Door Delivery Fees')}} :</div>
                                            <div class="col-6" id="door_delivery_fees"></div>
                                        </div>
                                        <div class="row" id="discount_fees_container">
                                            <div class="col-6">{{translate('Discount')}} :</div>
                                            <div class="col-6" id="discount_fees"></div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">{{translate('TOTAL COST')}} :</div>
                                            <div class="col-6" id="total_cost"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
                                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>
    @endsection

@section('script')
<script src="{{ static_asset('assets/dashboard/js/geocomplete/jquery.geocomplete.js') }}"></script>
<script src="//maps.googleapis.com/maps/api/js?libraries=places&key={{$checked_google_map->key}}"></script>

<script type="text/javascript">

    let selected_state_from_id = {{$shipment->from_state_id}};
    let selected_state_to_id   = {{$shipment->to_state_id}};
    let selected_area_from_id  = {{$shipment->from_area_id}};
    let selected_area_to_id    = {{$shipment->to_area_id}};

    $('#change-country').change(function() {
        var id = $(this).val();
        $.get("{{route('admin.shipments.get-states-ajax')}}?country_id=" + id, function(data) {
            $('select[name ="Shipment[from_state_id]"]').empty();
            $('select[name ="Shipment[from_state_id]"]').append('<option value=""></option>');
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                if( element['id'] == selected_state_from_id ){
                    $('select[name ="Shipment[from_state_id]"]').append('<option selected value="' + element['id'] + '">' + element['name'] + '</option>');
                }else{
                    $('select[name ="Shipment[from_state_id]"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }
            }


        });
    });
    $('#change-country-to').change(function() {
        var id = $(this).val();

        $.get("{{route('admin.shipments.get-states-ajax')}}?country_id=" + id, function(data) {
            $('select[name ="Shipment[to_state_id]"]').empty();
            $('select[name ="Shipment[to_state_id]"]').append('<option value=""></option>');
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                if( element['id'] == selected_state_to_id ){
                    $('select[name ="Shipment[to_state_id]"]').append('<option selected value="' + element['id'] + '">' + element['name'] + '</option>');
                }else{
                    $('select[name ="Shipment[to_state_id]"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }
            }


        });
    });
    $('#change-state-from').change(function() {
        if ($(this).val()){
            var id = $(this).val();
        }else{
            var id = selected_state_from_id;
        }

        $.get("{{route('admin.shipments.get-areas-ajax')}}?state_id=" + id, function(data) {
            $('select[name ="Shipment[from_area_id]"]').empty();
            $('select[name ="Shipment[from_area_id]"]').append('<option value=""></option>');
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                if( element['id'] == selected_area_from_id ){
                    $('select[name ="Shipment[from_area_id]"]').append('<option selected value="' + element['id'] + '">' + element['name'] + '</option>');
                }else{
                    $('select[name ="Shipment[from_area_id]"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }
            }


        });
    });
    $('#change-state-to').change(function() {
        if ($(this).val()){
            var id = $(this).val();
        }else{
            var id = selected_state_to_id;
        }
        $.get("{{route('admin.shipments.get-areas-ajax')}}?state_id=" + id, function(data) {
            $('select[name ="Shipment[to_area_id]"]').empty();
            $('select[name ="Shipment[to_area_id]"]').append('<option value=""></option>');
            for (let index = 0; index < data.length; index++) {
                const element = data[index];
                if( element['id'] == selected_area_to_id ){
                    $('select[name ="Shipment[to_area_id]"]').append('<option selected value="' + element['id'] + '">' + element['name'] + '</option>');
                }else{
                    $('select[name ="Shipment[to_area_id]"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }
            }


        });
    });

    function calcTotalWeight() {
        var elements = $('.weight-listener');
        var sumWeight = 0;
        elements.map(function() {
            sumWeight += parseInt($(this).val());
        }).get();
        $('.total-weight').val(sumWeight);
    }

    $('.address-receiver').each(function(){
        var address = $(this);

        var lat = '{{$shipment->reciver_lat}}';
        lat = parseFloat(lat);
        var lng = '{{$shipment->reciver_lng}}';
        lng = parseFloat(lng);

        address.geocomplete({
            map: ".map_canvas.map-receiver",
            mapOptions: {
                zoom: 18,
                center: { lat: lat, lng: lng },
            },
            markerOptions: {
                draggable: true
            },
            details: ".location-receiver",
            detailsAttribute: 'data-receiver',
            autoselect: true,
            restoreValueAfterBlur: true,
        });
        address.bind("geocode:dragged", function(event, latLng){
            $("input[data-receiver=lat]").val(latLng.lat());
            $("input[data-receiver=lng]").val(latLng.lng());
        });
    });


    $('.select-client').select2({
        placeholder: "Select Client",
    });
    $('.delivery-time').select2({
        placeholder: "Delivery Time",
    });
    $('.select-branch').select2({
        placeholder: "Select Branch",
    })
    $('.payment-method').select2({
        placeholder: "Payment Method",
    });

    $('.payment-type').select2({
        placeholder: "Payment Type",
    });
    function get_estimation_cost() {
        var total_weight = document.getElementById('kt_touchspin_4').value;
        var select_packages = document.getElementsByClassName('package-type-select');

        var from_country_id = document.getElementsByName("Shipment[from_country_id]")[0].value;
        var to_country_id = document.getElementsByName("Shipment[to_country_id]")[0].value;
        var from_state_id = document.getElementsByName("Shipment[from_state_id]")[0].value;
        var to_state_id = document.getElementsByName("Shipment[to_state_id]")[0].value;
        var from_area_id = document.getElementsByName("Shipment[from_area_id]")[0].value;
        var to_area_id = document.getElementsByName("Shipment[to_area_id]")[0].value;
        @if($user_type == 'customer')
            var client_id = {{$user_client}};
        @else
            var client_id = document.getElementById("client-id").value;
        @endif

        var package_ids = [];
        for (let index = 0; index < select_packages.length; index++) {
            if(select_packages[index].value){
                package_ids[index] = new Object();
                package_ids[index]["package_id"] = select_packages[index].value;
            }else{
                AIZ.plugins.notify('danger', '{{ translate('Please select package type') }} ' + (index+1));
                return 0;
            }
        }
        var request_data = { _token : '{{ csrf_token() }}',
                                package_ids : package_ids,
                                total_weight : total_weight,
                                from_country_id : from_country_id,
                                to_country_id : to_country_id,
                                from_state_id : from_state_id,
                                to_state_id : to_state_id,
                                from_area_id : from_area_id,
                                to_area_id : to_area_id,
                                client_id : client_id,
                            };
        $.post('{{ route('admin.shipments.get-estimation-cost') }}', request_data, function(response){

            if({{$is_def_mile_or_fees}} =='2')
            {
                document.getElementById("shipping_cost").innerHTML = response.shipping_cost;
                document.getElementById("return_cost").innerHTML = response.return_cost;
            }else if({{$is_def_mile_or_fees}} =='1')
            {
                document.getElementById("mile_cost").innerHTML = response.shipping_cost;
                document.getElementById("return_mile_cost").innerHTML = response.return_cost;
            }

            document.getElementsByName("Shipment[tax]")[0].value = Number(response.tax.replace(/[^0-9.-]+/g,""));
            document.getElementsByName("Shipment[shipping_cost]")[0].value = Number(response.shipping_cost.replace(/[^0-9.-]+/g,""));
            
            
            console.log(response);
            document.getElementsByName("Shipment[return_cost]")[0].value = Number(response.return_cost.replace(/[^0-9.-]+/g,""));
            document.getElementsByName("Shipment[insurance]")[0].value = Number(response.insurance.replace(/[^0-9.-]+/g,""));

            document.getElementById("tax_duty").innerHTML = response.tax;
            document.getElementById("insurance").innerHTML = response.insurance;
            document.getElementById("total_cost").innerHTML = response.total_cost;
            document.getElementById('modal_open').click();
            console.log(response);
        });
    }

    $('.select-client').change(function(){
        var client_phone = $(this).find(':selected').data('phone');
        document.getElementById("client_phone").value = client_phone;
    })
    $(document).ready(function() {

        $('.select-country').select2({
            placeholder: "Select country",
            language: {
              noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.shipments.covered_countries')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('Countries')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
              },
            },
            escapeMarkup: function(markup) {
              return markup;
            },
        });

        $('.select-state').select2({
            placeholder: "Select state",
            language: {
              noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.shipments.covered_countries')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('States')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
              },
            },
            escapeMarkup: function(markup) {
              return markup;
            },
        });

        $('.select-address').select2({
            placeholder: "Select Client First",
        })

        $('.select-area').select2({
            placeholder: "Select Area",
            language: {
              noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.areas.create')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('Areas')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
              },
            },
            escapeMarkup: function(markup) {
              return markup;
            },
        });

        $('.select-country').trigger('change');
        $('.select-state').trigger('change');

        var inputs = document.getElementsByTagName('input');

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].type.toLowerCase() == 'number') {
                inputs[i].onkeydown = function(e) {
                    if (!((e.keyCode > 95 && e.keyCode < 106) ||
                            (e.keyCode > 47 && e.keyCode < 58) ||
                            e.keyCode == 8)) {
                        return false;
                    }
                }
            }
        }
        $('#kt_datepicker_3').datepicker({
            orientation: "bottom auto",
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayBtn: true,
            todayHighlight: true,
			startDate: new Date(),
        });

        $('#kt_timepicker_3').timepicker({
            icons:
            {
                up: 'fa fa-angle-up',
                down: 'fa fa-angle-down'
            },
            setTime: new Date(),
            minuteStep: 1,
            showSeconds: true,
            showMeridian: true,
            pickerPosition: 'bottom-auto',
        });

        $('#kt_repeater_1').repeater({
            initEmpty: false,

            show: function() {
                $(this).slideDown();

                $('.package-type-select').select2({
                    placeholder: "Package Type",
                    language: {
                    noResults: function() {
                        @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                            return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.packages.create')}}?redirect=admin.shipments.create"
                            class="btn btn-primary" >Manage {{translate('Packages')}}</a>
                            </li>`;
                        @else
                            return ``;
                        @endif
                    },
                    },
                    escapeMarkup: function(markup) {
                    return markup;
                    },
                });
                $('.dimensions_r').TouchSpin({
                    buttondown_class: 'btn btn-secondary',
                    buttonup_class: 'btn btn-secondary',

                    min: 1,
                    max: 1000000000,
                    stepinterval: 50,
                    maxboostedstep: 10000000,
                    initval: 1,
                });
                $('.kt_touchspin_weight').TouchSpin({
                    buttondown_class: 'btn btn-secondary',
                    buttonup_class: 'btn btn-secondary',

                    min: 1,
                    max: 1000000000,
                    stepinterval: 50,
                    maxboostedstep: 10000000,
                    initval: 1,
                    prefix: 'Kg'
                });
                $('.kt_touchspin_qty').TouchSpin({
                    buttondown_class: 'btn btn-secondary',
                    buttonup_class: 'btn btn-secondary',

                    min: 1,
                    max: 1000000000,
                    stepinterval: 50,
                    maxboostedstep: 10000000,
                    initval: 1,
                });

                calcTotalWeight();
            },

            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            }
        });

        $('#kt_touchspin_2, #kt_touchspin_2_2').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: '%'
        });
        $('#kt_touchspin_3').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: '{{currency_symbol()}}'
        });
        $('#kt_touchspin_3_3').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: '{{currency_symbol()}}'
        });
        $('#kt_touchspin_4').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: 'Kg'
        });

        $('.kt_touchspin_weight').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: 'Kg'
        });
        $('.dimensions_r').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
        });
        $('.kt_touchspin_qty').TouchSpin({
            buttondown_class: 'btn btn-secondary',
            buttonup_class: 'btn btn-secondary',

            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
        });
        $('.package-type-select').select2({
            placeholder: "Package Type",
            language: {
            noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.packages.create')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('Packages')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
            },
            },
            escapeMarkup: function(markup) {
            return markup;
            },
        });

        FormValidation.formValidation(
            document.getElementById('kt_form_1'), {
                fields: {
                    "Shipment[type]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[shipping_date]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[branch_id]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[client_id]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[client_address]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[client_phone]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[payment_type]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[payment_method_id]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[tax]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[insurance]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[shipping_cost]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[delivery_time]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[delivery_time]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[total_weight]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[reciver_name]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[reciver_phone]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Shipment[reciver_address]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },

                },


                plugins: {
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    trigger: new FormValidation.plugins.Trigger(),
                    // Bootstrap Framework Integration
                    bootstrap: new FormValidation.plugins.Bootstrap(),
                    // Validate fields when clicking the Submit button
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    // Submit the form when all fields are valid
                    defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    icon: new FormValidation.plugins.Icon({
                        valid: 'fa fa-check',
                        invalid: 'fa fa-times',
                        validating: 'fa fa-refresh',
                    }),
                }
            }
        );
    });
</script>
@endsection
