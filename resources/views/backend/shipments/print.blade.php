<?php
use \Milon\Barcode\DNS1D;
$d = new DNS1D();
$origin_branch = App\Branch::where('id',$shipment->branch_id)->get();
if($shipment->to_branch_id !== 0 || $shipment->to_branch_id !== null)
   $destination_branch = App\Branch::where('id',$shipment->to_branch_id)->get();
else 
   $destination_branch = App\Branch::where('area_id',$shipment->area_id)->get();

   $state_query        = App\State::where('id',$shipment->from_state_id)->get();
   $qty = 0;
   
 ?>
<style type="text/css">
html { background-color: none; } 
body {
    min-height: 80mm;
    max-width: 120mm;
    margin: 0 auto;
}
.page {
width:80mm !important;
height: 120mm !important;
/*padding: 5px;*/
overflow:hidden;

}
.subpage{
padding: 5px;
}
.border{
    border-left:grey 10px solid;border-right:grey 10px solid;
    border-top:#000000 5px solid;border-bottom:#000000 5px solid;
    text-align: center;
}
.vrt-title td {
  writing-mode: vertical-lr;
  min-width: auto; /* for firefox */
}
</style>
<div class="page border">
<table  width="100%" style="border-bottom:#000000 1px solid;text-align: center;"> 
<tr>
<td valign="left">
<b class="destiantion_province_code">
<span style="font-size:28px; font-weight:bold; padding:10px;border-right:#000 1px solid;">
    {{$destination_branch[0]['code']}}
    <!--{{$state_query[0]['name']}}-->
    
</span>
</b>
</td>
<td valign="middle" style="padding:0px;"> 
 @if(get_setting('system_logo_white') != null)
    <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="160" height="40">
            @else
        <img src="{{ static_asset('assets/img/logo.svg') }}" width="160" height="40">
@endif
</td>
<td valign="right" style="background-color:#000;color:#fff;">
<b class="destiantion_province_code" >
    @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)
        <span style="font-size:16px; font-weight:bold;">
        {{translate('COD')}} 
        </span>
    @else
        <span style="font-size:16px; font-weight:bold;"> 
        {{translate('NON COD')}} 
        </span>
    @endif
</b>
</td>
</tr>
</table>   
                                   
<table cellpadding="0" cellspacing="0" style="text-align:center;top:0px;width:100%;">
    <tr>
        <td style="border-bottom:#000 1px solid;">
             @if($shipment->barcode != null)
                @php
                    echo '<img src="data:image/png;base64,' . $d->getBarcodePNG($shipment->code, "C128") . '" alt="barcode"   width="245" height="33"/>';
                @endphp
            @endif
            <span style="font-size:14px; font-weight:bold;"> 
                    @if($shipment->order_id != null) {{translate('Order ID')}}: {{$shipment->order_id}} / @endif {{$shipment->code}} 
                </span> 
            </td>
    </tr>
</table>         
   
<table width="77%" 
style="font-size:12px;border-right:#000 2px solid;overflow:hidden;">
            <tr >
                <td style="padding:2px;text-align: center; border-bottom:#000 .2px solid;" >
                <span style="font-weight:bold;">
                 @if($origin_branch)
                 {{$shipment->branch->name}} <br>
                 <ph>
                    {{!empty($origin_branch[0]['responsible_mobile'])?$origin_branch[0]['responsible_mobile']:""}}
                 </ph>
                      @endif
               </span>
               <br>
               <div style="text-align:left;">
                     @if($shipment->customer_type == "walkin")
                     {{translate('From')}}: {{$shipment->walkin_customer_name}}
                     <br /> {{translate('Ph')}}: {{$shipment->client_phone}}
                     @if(strlen($shipment->walkin_client_address) > 2))
                       <br />{{$shipment->walkin_client_address}}
                       @else <br/>{{translate('address not found')}}
                     @endif
                    @else
                     {{translate('From')}}: {{$shipment->client->name}}
                    <br /> {{translate('Ph')}}: {{$shipment->client_phone}}
                    @if(strlen($shipment->from_address->address) > 2)
                       <br />{{$shipment->from_address->address}}
                       @else <br/>{{translate('address not found')}}
                     @endif
                    @endif
                </div>
                </td>
                </tr>
                 <tr>
                <td style="padding:2px;text-align: center;" >
                <span style="font-weight:bold;">
                 @if($destination_branch)
                 {{$destination_branch[0]['name']}} <br>
                 <ph>
                 {{!empty($destination_branch[0]['responsible_mobile'])?$destination_branch[0]['responsible_mobile']:""}}
                 </ph>
                      @endif
               </span>
              <div style="text-align:left;">
                     {{translate('To')}}: {{$shipment->reciver_name}} <br>
                     {{translate('Ph')}}:{{$shipment->reciver_phone}}
                      @if(strlen($shipment->reciver_address) > 2)
                       <br />{{$shipment->reciver_address}}
                       @else <br/>{{translate('address not found')}}
                     @endif
                    </div>
                </td>
                </tr>
      </table>  
<table style = "margin-top: -57%;height:40%;
    margin-left: 232px;">
    <tr >
            <td style="border-bottom:#000 .2px solid;padding: 2px;">
                    <span >
                      <img src="{{ static_asset('assets/img/qr/print_label_qr.jpeg') }}" width="65" height="65"> 
                     </span>
                     </td>
                      </tr>
                      <tr>
                    <!--  <td style="font-size:30px;text-align:center;padding: 2px;">-->
                    <!--  <span>pathi</span>-->
                    <!--</td>-->
                     <td >
                      <span style="font-size:10px;">@if($destination_branch){{$destination_branch[0]['name']}}@endif <br></span>
                    <!--</td>-->
                    <!-- </tr>   -->
                    <!-- <tr >-->
                    <!--     <td  style="text-align:center;padding:2px;writing-mode: vertical-lr;transform: rotate(182deg);font-size:30px;">   -->
                         <span style="text-align:center;padding:2px;writing-mode: vertical-lr;transform: rotate(182deg);font-size:30px;margin-left:11px;font-weight:bold;">
                             {{!empty($destination_branch[0]['code'])?$destination_branch[0]['code']:""}} 
                            </span>
                        </td>
            </tr>
            </table>    
<table width="100%" style="border-top:#000000 1px solid;font-size:12px;font-weight:bold;margin-top:-3%;">
<tr>
    <td style="border-right: #000000 .2px solid; text-align: left;padding: 2px;">
    <span>{{translate('WGT')}}: {{$shipment->total_weight}} kg</span>
    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)
     <span>
     <br>LWH: {{$package->length + $package->width + $package->height}} cm
    @endforeach
</td>
<td style="border-right: #000000 .2px solid;padding: 2px;">
    <!-- <span style="font-size:12px; padding:2px;">{{translate('COST')}}<br>-->
    <!--         @if($shipment->payment_type == \App\Shipment::POSTPAID )-->
    <!--            {{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}-->
    <!--            @endif-->
    <!--</span>-->
    {{$shipment->getPaymentType()}}
         <br>
             @if($shipment->payment_type == \App\Shipment::POSTPAID )
                <!--{{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}-->
                <!--{{format_price($shipment->amount_to_be_collected) }}-->
                <!--₭{{$shipment->shipping_cost-$shipment->discount_amt}}-->
               {{format_price($shipment->amount_to_be_collected + $shipment->tax + $shipment->shipping_cost + $shipment->insurance + $shipment->pickup_cost + $shipment->shipping_distance_cost + $shipment->door_delivery_cost + $shipment->cod_cost - $shipment->discount_amt) }}
                @else 
                {{format_price($shipment->amount_to_be_collected + $shipment->tax + $shipment->shipping_cost + $shipment->insurance + $shipment->pickup_cost + $shipment->shipping_distance_cost + $shipment->door_delivery_cost + $shipment->cod_cost - $shipment->discount_amt) }}
            @endif
    
</td>
<td style="border-right: #000000 .2px solid; text-align: center;padding: 2px;">
<span style="font-weight:bold;">
      <!--{{$shipment->getPaymentType()}}-->
      <!--<br>-->
    @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)
             {{translate('COD')}} : {{format_price($shipment->shipping_cost - $shipment->discount_amt) }}
    <!--@if($shipment->payment_type == \App\Shipment::POSTPAID )-->
    <!--{{format_price($shipment->amount_to_be_collected + $shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}-->
    <!--{{format_price($shipment->amount_to_be_collected) }}-->
    <!--@else-->
    <!--           {{format_price($shipment->amount_to_be_collected) }}-->
    
    <!--@endif-->
    @else   
    <span>{{translate('NON COD')}} : ₭0 </span> 
    @endif
    <br><span>{{\Carbon\Carbon::parse($shipment->shipping_date)->format('d-m-Y')}}</span>
</span>
</td>
<td style="text-align: center;">  {{translate('Qty')}}  <br>
 @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $key => $package)
    @php
    $qty = $qty + $package->qty;
    echo '<span class="bold">'.$qty.'</span>';
    @endphp
   @endforeach
</td>
</tr>
</table>
<table width="100%" style="border-top:#000 2px solid;border-bottom:#000000 1px solid;text-align: center;">
<tr>    
    <td>
    @if(get_setting('system_logo_white') != null)
        <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="130" height="36">
    @else
        <img src="{{ static_asset('assets/img/logo.svg') }}" width="130" height="36">
    @endif
    </td>
    <td>
          @if($shipment->barcode != null)
                @php
                    echo '<img src="data:image/png;base64,' . $d->getBarcodePNG($shipment->code, "C128") . '" alt="barcode"  width="145" height="30"/>';
                @endphp
            @endif
           <span style="font-size:12px; font-weight:bold;">  
                @if($shipment->order_id != null) {{translate('Order ID')}}: {{$shipment->order_id}}  @endif {{$shipment->code}} 
                </span> 
        </td>    
    </tr>
    </table>

    <table width="100%" style="text-align: left;font-weight:bold;font-size:12px;">
        <tr>
            <td style="border-right:#000 .2px solid;width:25%;padding:2px;">
            <span > 
                     @if($shipment->customer_type == "walkin")
                     {{translate('From')}}: {{$shipment->walkin_customer_name}}
                     @else
                    {{translate('From')}} {{$shipment->client->name}}
                    @endif
                    <br />{{translate('Ph')}}:{{$shipment->client_phone}}
                    <br /> {{translate('To')}} {{$shipment->reciver_name}} <br />{{translate('Ph')}}:{{$shipment->reciver_phone}}
                    
                    </span>
        </td>
        <td style="border-right:#000 .2px solid;width:40%;padding:2px;text-align:center;font-weight:bold;">
             <span> {{\Carbon\Carbon::parse($shipment->shipping_date)->format('d-m-Y')}}<br></span>
                 <!--{{$origin_branch[0]['code']}} <br>-->
                 <!--@if(!empty($destination_branch[0]['code']))-->
                 <!--{{$destination_branch[0]['code']}}-->
                 <!--@endif-->
                 <!--<br>-->
                 <span style="text-align:left;">
                  @if($origin_branch)
                 {{$shipment->branch->name}}@endif
                 <br>
                 @if($destination_branch)
                 {{$destination_branch[0]['name']}}
                 @endif 
                 </sapn>
                 <br>
            <span style="text-align:left;">
             @if ($shipment->amount_to_be_collected && $shipment->amount_to_be_collected  > 0)
             {{translate('COD')}} - {{format_price($shipment->amount_to_be_collected) }}
             <!--@if($shipment->payment_type == \App\Shipment::POSTPAID )-->
                 <!--{{$shipment->getPaymentType()}} =  {{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}-->
             <!--     <br>{{$shipment->getPaymentType()}} : ₭{{$shipment->shipping_cost}}-->
             <!--   @else -->
             <!--     <br>{{$shipment->getPaymentType()}} : ₭{{$shipment->shipping_cost}}-->
             <!--   @endif-->
            @else 
            {{translate('NON COD')}} : ₭0<br>
            {{$shipment->getPaymentType()}} : {{format_price($shipment->amount_to_be_collected + $shipment->tax + $shipment->shipping_cost + $shipment->insurance + $shipment->pickup_cost + $shipment->shipping_distance_cost + $shipment->door_delivery_cost + $shipment->cod_cost - $shipment->discount_amt) }}
             @endif
             
             <!--{{format_price($shipment->shipping_cost - $shipment->discount_amt)}}-->
            </span>
            
        </td>
        <td style="width:30%;padding:2px;font-size:11px;"> 
             <!--   @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)-->
             <!--    <span>{{translate('Weight')}}: {{$package->weight}} {{translate('KG')}}-->
             <!--    <br> LWH: {{$package->length}} * {{ $package->width}}*{{$package->height}}-->
             <!--     @endforeach-->
             <!--    </span>-->
             <!--    <span style="font-size:12px; padding:2px;"> -->
             <!--    <br>-->
             <!--    @if($shipment->payment_type == \App\Shipment::POSTPAID )-->
             <!--   {{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}-->
             <!--   @endif-->
             <!--</span>-->
             <span style="text-align:left;">
                
                 {{translate('WGT')}}: {{$shipment->total_weight}} kg </span>
                <span style="text-align:left;">
                    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $key => $package)
                    @if($key > 0)<br> @endif
                    <br>LWH: {{$package->length + $package->width + $package->height}} cm
                    @endforeach  
              </span>
        </td>
        </tr>
        </table>
</div>
<script>
window.onload = function() {
    javascript:window.print();
};
</script>