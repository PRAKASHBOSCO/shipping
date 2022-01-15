<?php
use \Milon\Barcode\DNS1D;
$d = new DNS1D();
$origin_branch = App\Branch::where('id',$shipment->branch_id)->get();
if($shipment->to_branch_id !== 0 || $shipment->to_branch_id !== null)
   $destination_branch = App\Branch::where('id',$shipment->to_branch_id)->get();
else 
   $destination_branch = App\Branch::where('area_id',$shipment->area_id)->get();
 
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
    border-left:grey 10px solid;border-right:grey 10px solid;
    text-align: center;
}
.vrt-title td {
  writing-mode: vertical-lr;
  min-width: auto; /* for firefox */
}
</style>
<div class="page border" >
<table cellpadding="0" cellspacing="0" style="border-top:grey 3px solid;border-bottom:grey 1px solid;text-align: center;width:100%;float:left;left:0;">
<tr>
<td >
<table  width="100%" style="border-bottom:#000000 1px solid;text-align: center;"> 
<tr>
<td valign="middle" style="padding:2px; height: auto;"> 
 @if(get_setting('system_logo_white') != null)
                                                <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="180" height="38">
                                            @else
                                                <img src="{{ static_asset('assets/img/print_logo.jpeg') }}" width="180" height="38">
                                            @endif
</td>
<td valign="right" style="background-color:#ccc;">
<b class="destiantion_province_code" >
   
        <span style="font-size:15px; font-weight:bold; padding:5;">
        {{translate('INVOICE')}}
        <br>{{\Carbon\Carbon::parse($shipment->shipping_date)->format('d-m-Y')}}
        </span>
  
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
                    
                    @if($shipment->order_id != null) {{translate('Order ID')}}: {{$shipment->order_id}} / @endif {{$shipment->code}} 
                </span> 
            </td>
    </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;text-align: center;padding-top:2px;">
        
            <tr >
                <td style="text-align:left;padding:5px; border-bottom:grey 2px solid;" >   
                     <span style="font-weight:bold;">{{translate('From')}}:  <br /></span> 
                    <!--{{$shipment->client->name}}-->
                    <!--<br /> {{$shipment->client_phone}}-->
                   {{$shipment->from_address->address}} 
                     <br />
                    <span style="font-weight:bold;">{{translate('To')}}:  <br /></span> 
                    {{$shipment->reciver_address}}
                </td>
                </tr>
                <tr>
                <td style="text-align:left;padding:5px;border-bottom:grey 2px solid;"> 
                    <span style="font-weight:bold;">{{translate('Invoice To')}}: {{$shipment->reciver_phone}}
                    <br />   {{$shipment->reciver_address}}  
                    <br />  {{$shipment->reciver_name}}
                 
                    </span>
                </td>
                </tr>        
        <tr>
<td>
    <table style="margin-top: -39%;    margin-left: 200px;">
        <tr >
                <td >
                     <span >
                      <img src="{{ static_asset('assets/img/qr/print_label_qr.jpeg') }}" width="100" height="100">
                      
                     </span>
                </td>
            </tr>
        </table>
        </td>
        </tr>
        <tr>
        <td>
             <table class="table" style="border-bottom:grey 2px solid;width:100%;padding:0px;text-align:center;">
                                <thead>
                                    <tr>
                                        <th class="pl-0 font-weight-bold text-muted text-uppercase">{{translate('Package Items')}}</th>
                                        <th class="text-middle font-weight-bold text-muted text-uppercase">{{translate('Type')}}</th>
                                        <th class="text-right font-weight-bold text-muted text-uppercase">{{translate('Qty')}}</th>
                                      
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)
                                        <tr class="font-weight-boldest">
                                            <td class="pl-0 border-0 pt-7 d-flex align-items-center">{{$package->description}}</td>
                                            <td class="text-right align-middle pt-7">@if(isset($package->package->name)){{$package->package->name}} @else - @endif</td>
                                            <td class="text-right align-right pt-7">{{$package->qty}}</td>
                                           
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
            
        </td></tr>
         <tr>
        <td>
             <table class="table" style="border-bottom:grey 2px solid;width:100%;padding:0px;text-align:center;">
                                <thead>
                                    <tr>
                                        <th class="pr-0 text-right font-weight-bold text-muted text-uppercase">{{translate('Weight x Length x Width x Height')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(\App\PackageShipment::where('shipment_id',$shipment->id)->get() as $package)
                                        <tr class="font-weight-boldest">
                                            <td style="text-align:cetner;">{{$package->weight." ".translate('KG')." x ".$package->length." ".translate('CM')." x ".$package->width." ".translate('CM')." x ".$package->height." ".translate('CM')}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
        </td></tr>
        
                <tr><td style="font-weight:bold">
                     <span >{{translate('SHIPMENT CODE')}} {{$shipment->code}}</span>
                </td></tr>
                <tr>
            <td>
                 <table class="table" style="border-bottom:grey 2px solid;width:100%;padding:2px;text-align:center;">
                                <thead>
                                    <tr class="font-weight-bolder">
                                        <th >{{translate('PAYMENT TYPE')}}</th>
                                        <th >{{translate('PAYMENT STATUS')}}</th>
                                        <th >{{translate('PAYMENT DATE')}}</th>
                                        <th >{{translate('TOTAL COST')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-weight-bolder">
                                        <td>{{translate($shipment->pay['name'])}} ({{$shipment->getPaymentType()}})</td>
                                        <td>@if($shipment->paid == 1) {{translate('Paid')}} @else {{translate('Pending')}} @endif</td>
                                        <td>@if($shipment->paid == 1) {{$shipment->payment->payment_date ?? ""}} @else - @endif</td>
                                        <td>{{format_price($shipment->tax + $shipment->shipping_cost + $shipment->insurance) }}<br />
                                        <!--<span class="text-muted font-weight-bolder font-size-lg">{{translate('Incl.tax & insurance')}}</span>-->
                                        </td>
                                    </tr>
                                  
                                </tbody>
                            </table>
            </td>
        </tr>
        
</table>    
</div>
<script>
window.onload = function() {
	javascript:window.print();
};
</script>