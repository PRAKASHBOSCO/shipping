<?php

namespace App\Exports;

use Auth;
use App\Shipment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Http\Controllers\ShipmentController;

class ShipmentsExportExcel implements FromCollection,WithHeadings,WithStyles
{
    public function __construct( object $data)
    {
        $this->data = $data;
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        if(Auth::user()->user_type == 'customer'){
            return ["Code", "Type", "Status", "Origin Branch","Destination Branch", "Shipping Date", "Client Address", "Client Phone", "Reciver Name", "Reciver Phone", "Reciver Address" ,'From Country','To Country','From State','To State','From Area','To Area' , "Payment Type", "Payment Method", "Tax", "Insurance", "Shipping Cost", "Delivery Time", "Total Weight", "Amount To Be Collected", "Order Id" ,'Created At'];
        }elseif(Auth::user()->user_type == 'branch'){
            return ["Code", "Type", "Status", "Client", "Shipping Date", "Client Address", "Client Phone", "Reciver Name", "Reciver Phone", "Reciver Address" ,'From Country','To Country','From State','To State','From Area','To Area' , "Payment Type", "Payment Method", "Tax", "Insurance", "Shipping Cost", "Delivery Time", "Total Weight", "Amount To Be Collected", "Order Id" ,'Created At'];
        }else {        
            return ["Code", "Type", "Status", "Origin Branch","Destination Branch", "Client", "Shipping Date", "Client Address", "Client Phone", "Reciver Name", "Reciver Phone", "Reciver Address" ,'From Country','To Country','From State','To State','From Area','To Area' , "Payment Type", "Payment Method", "Tax", "Insurance", "Shipping Cost", "Delivery Time", "Total Weight", "Amount To Be Collected", "Order Id" ,'Created At'];
        }
    }
    
    public function collection()
    {
        $shipmentController = new ShipmentController();
        $shipments = new Shipment;
        
        $shipments = $shipmentController->search($this->data,$shipments);

        if(Auth::user()->user_type == 'customer'){
            $shipments = $shipments->select('code','type','status_id','branch_id','to_branch_id','shipping_date','client_address','client_phone','reciver_name','reciver_phone','reciver_address','from_country_id','to_country_id','from_state_id','to_state_id','from_area_id','to_area_id','payment_type','payment_method_id','tax','insurance','shipping_cost','delivery_time','total_weight','amount_to_be_collected','order_id','created_at');
        }elseif(Auth::user()->user_type == 'branch'){
            $shipments = $shipments->select('code','type','status_id','client_id','shipping_date','client_address','client_phone','reciver_name','reciver_phone','reciver_address','from_country_id','to_country_id','from_state_id','to_state_id','from_area_id','to_area_id','payment_type','payment_method_id','tax','insurance','shipping_cost','delivery_time','total_weight','amount_to_be_collected','order_id','created_at');
        }else {
            $shipments = $shipments->select('code','type','status_id','branch_id','to_branch_id','client_id','shipping_date','client_address','client_phone','reciver_name','reciver_phone','reciver_address','from_country_id','to_country_id','from_state_id','to_state_id','from_area_id','to_area_id','payment_type','payment_method_id','tax','insurance','shipping_cost','delivery_time','total_weight','amount_to_be_collected','order_id','created_at');
        }
        
        if (isset($this->data->page) && !empty($this->data->page)) {
            
            $skip_number = $this->data->page - 1;
            $skip_number = $skip_number * 20;
            $shipments   = $shipments->with('pay')->orderBy('client_id')->orderBy('id','DESC')->skip($skip_number)->take(20)->get();
        }

        foreach($shipments as $shipment)
        {
            $shipment->status_id = $shipment->getStatus();
            if(Auth::user()->user_type != 'branch')
            {
                $shipment->branch_id    = $shipment->branch->name;
                $shipment->to_branch_id = $shipment->branch->name;
            }
            if(Auth::user()->user_type != 'customer')
            {
                $shipment->client_id = $shipment->client->name;
            }
            $shipment->client_address = $shipment->from_address->address;
            $shipment->created_at     = $shipment->created_at->format('Y-m-d');
            $shipment->from_country_id = $shipment->from_country->name;
            $shipment->to_country_id   = $shipment->to_country->name;
            $shipment->from_state_id   = $shipment->from_state->name;
            $shipment->to_state_id     = $shipment->to_state->name;
            if($shipment->from_area_id != null)
            {
                $shipment->from_area_id    = $shipment->from_area->name;
            }
            if($shipment->to_area_id != null)
            {
                $shipment->to_area_id    = $shipment->to_area->name;
            }
            $shipment->payment_type      = $shipment->getPaymentType();
            $shipment->payment_method_id = $shipment->pay['name'];

        }

        return collect($shipments);
    }
}