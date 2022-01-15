<?php
use \Milon\Barcode\DNS1D;
$d = new DNS1D();
$origin_branch = App\Branch::where('id',$shipment->branch_id)->get();
if($shipment->to_branch_id !== 0 || $shipment->to_branch_id !== null)
   $destination_branch = App\Branch::where('id',$shipment->to_branch_id)->get();
else 
   $destination_branch = App\Branch::where('area_id',$shipment->area_id)->get();
   
  // dd($shipment);
 ?>
 <style media="print">
    .no-print, div#kt_header_mobile, div#kt_header, div#kt_footer{
        display: none;
        width:80mm;
        height: 120mm ;
    }
</style>
<style type="text/css">
.page {
width:80mm !important;
height: 120mm !important;
overflow:hidden;
}
.border{
    border-left:#ccc 5px solid;border-right:#ccc 5px solid;
    text-align: center;
}
.vrt-title td {
  writing-mode: vertical-lr;
  min-width: auto; /* for firefox */
}
</style>
<div class="page border" >
<table cellpadding="0" cellspacing="0" style="border-top:grey 3px solid;border-bottom:grey 1px solid;text-align: center;width:100%;float:left;left:0;">
<tr><td >
<table  width="100%" style="border-bottom:#000000 1px solid;text-align: center;">
<tr>    
    <td>    
   
    @if(get_setting('system_logo_white') != null)
        <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="120" height="30">
       @else
        <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="120" height="30">
    @endif
    </td>
    
    <td>
          @if($shipment->barcode != null)
                @php
                    echo '<img src="data:image/png;base64,' . $d->getBarcodePNG($shipment->code, "C128") . '" alt="barcode"  width="170" height="30"/>';
                @endphp
            @endif
           <span style="font-size:12px; font-weight:bold;">  
                @if($shipment->order_id != null) {{translate('Order ID')}}: {{$shipment->order_id}}  @endif {{$shipment->code}} 
                </span> 
        </td>    
    </tr>
    </table>
</td></tr>
<tr><td>
    <table width="100%" style="margin:0px;border-bottom:#000000 5px solid;text-align:center;font-weight:bold;font-size:12px;">
        <tr>
            <td style="border-right:#000000 .2px solid;">
            <span class="bold"> 
                    {{translate('Sender Ph.')}} <br />{{$shipment->client_phone}}
                    <br /> {{translate('Reciver Ph.')}} <br /> {{$shipment->reciver_phone}}
                     <br>
                 {{\Carbon\Carbon::parse($shipment->shipping_date)->format('d-m-Y')}}
                    </span>
        </td>
        <td style="border-right:#000000 .2px solid;">
             <span>
                 {{$shipment->branch->name}} <br>
                 @if(!empty($destination_branch[0]['code']))
                 {{$destination_branch[0]['name']}}
                 @endif
             </span> 
        </td>
        <td style="text-align: center;"> 
        <span style="">
             @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)
             {{translate('COD')}} <br>
                @if($shipment->payment_type == \App\Shipment::POSTPAID )
    {{format_price($shipment->amount_to_be_collected + $shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}
                @else
               {{format_price($shipment->amount_to_be_collected) }}
                @endif
                        </span>
                        @else
                                <span style="">
                                        {{translate('Non COD')}} <br />
                                        @if($shipment->payment_type == \App\Shipment::POSTPAID )
                                       {{translate('S.Cost')}} {{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}
                                        @endif
                                    </span>
                                    @endif
                <br>                    
                <span>
                    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)
                    LWH: {{$package->length}} * {{ $package->width}}*{{$package->height}}
                    @endforeach  / </span>
                <span >{{translate($shipment->pay['name'])}}({{$shipment->getPaymentType()}})</span>
                <span style="text-align:center;margin:0px;">
                               {{translate('Weight')}}</span>: 
                <span style="text-align:center;">
                                {{$shipment->total_weight}} {{translate('Kg')}}
                </span>
        </td>
        </tr>
        </table> </td>
        </tr>
<tr>
<td >
<table  width="100%" style="border-bottom:#000000 1px solid;text-align: center;"> 
<tr>
<td valign="left">
<b class="destiantion_province_code">
<span style="font-size:25px; font-weight:bold; padding:2px;border-right:grey 1px solid;">
    {{$destination_branch[0]['code']}}
</span>
</b>
</td>
<td valign="middle" style="padding:2px; height: auto;"> 
 @if(get_setting('system_logo_white') != null)
                                                <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="180" height="36">
                                            @else
                                                <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="180" height="36">
                                            @endif
</td>
<td valign="right" style="background-color:#ccc;">
<b class="destiantion_province_code" >
    @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)
        <span style="font-size:15px; font-weight:bold; padding:5;">
        {{translate('COD')}} 
        </span>
        <br />
    @else
        <span style="font-size:15px; font-weight:bold; padding:5;"> 
        NON {{translate('COD')}} 
        </span>
    @endif
</b>
</td>
</tr>
</table>   
 </td></tr>                                       
<table cellpadding="0" cellspacing="0" style="text-align:center;top:0px;width:100%;">
    <tr>
        <td style="border-bottom:grey 2px solid; padding:3;">
             @if($shipment->barcode != null)
                @php
                    echo '<img src="data:image/png;base64,' . $d->getBarcodePNG($shipment->code, "C128") . '" alt="barcode"   width="245" height="30"/>';
                @endphp
            @endif
            <span style="font-size:14px; font-weight:bold;">   
            @if($shipment->order_id != null) {{translate('Order ID')}}: {{$shipment->order_id}} / @endif {{$shipment->code}} - {{\Carbon\Carbon::parse($shipment->shipping_date)->format('d-m-Y')}}
                </span> 
            </td>
    </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;text-align: center;padding-top:2px;">
            <tr >
                <td style="padding-bottom:0px;text-align: center;" 
                    >
                 @if($origin_branch)
                 {{$shipment->branch->name}} <br>
                 <ph>
                    PH: {{!empty($origin_branch[0]['responsible_mobile'])?$origin_branch[0]['responsible_mobile']:""}}
                 </ph>
                      @endif
                </td>
            </tr>
            <tr >
                <td style="text-align:left;padding:2px; border-bottom:grey 2px solid;" >   
                     <span style="font-weight:bold;">{{translate('From')}}: </span> 
                    {{$shipment->client->name}}
                    <br /> {{$shipment->client_phone}}
                    <br /> {{$shipment->from_address->address}} 
                </td>
                </tr>
                
                <tr>
                    <td style="padding-bottom:1px;padding-top:1px;text-align: center;" >
                    
                       @if($destination_branch)
                       {{$destination_branch[0]['name']}} <br>
                      <ph>
                       PH: {{!empty($destination_branch[0]['responsible_mobile'])?$destination_branch[0]['responsible_mobile']:""}}
                      </ph>
                      @endif
                    </td>
                </tr>
                <tr>
                <td style="text-align:left;padding:2px;border-bottom:grey 2px solid;"> 
                    <span style="font-weight:bold;">{{translate('To')}}: </span> 
                    {{$shipment->reciver_name}}
                    <br /> {{$shipment->reciver_phone}}
                    <br /> {{$shipment->reciver_address}} 
                </td>
                </tr>        
        <tr>
<td>
   
    <table style="margin-top: -52%;    margin-left: 231px;">
        <tr >
                <td >
                     <span >
                      <img src="{{ static_asset('assets/img/qr/print_label_qr.jpeg') }}" width="60" height="60"> 
                      
                     </span>
                </td>
                       
            </tr>
        </table>
        </td>
        </tr>
</table>    
<table width="100%" style="border-bottom:#000000 2px solid;font-size:12px;font-weight:bold;">
<tr>
    <td style="border-right: #000000 .2px solid; text-align: left;">
    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)
     <span>WT:{{$package->weight}} {{translate('KG')}}
     <br> LwH: {{$package->length}} * {{ $package->width}}*{{$package->height}}
      @endforeach
</td>
<td style="border-right: #000000 .2px solid; text-align: center;">
     <span style="font-size:12px; padding:2px;"> {{translate('ShippingCost')}}<br>
             @if($shipment->payment_type == \App\Shipment::POSTPAID )
                {{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}
                @endif
    </span>
    
</td>
<td style="border-right: #000000 .2px solid; text-align: center;">
  <span style="padding:2px;">
             @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)
             {{translate('COD')}} <br>
                @if($shipment->payment_type == \App\Shipment::POSTPAID )
    {{format_price($shipment->amount_to_be_collected + $shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}
                @else
               {{format_price($shipment->amount_to_be_collected) }}
                @endif
                        </span>
                                @else
                                <span style="padding:2px;">
                                        {{translate('Non COD')}} 
                                    </span>
                                    @endif
</td>
<td style="text-align: center;">  {{translate('Qty')}}: <br>
 @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $key => $package)
    <span class="bold"> {{$package->qty}}</span>
   @endforeach
</td>
</tr>
</table>
    </td></tr>  
     </table>
</div>

<script>
window.onload = function() {
	javascript:window.print();
};
</script>