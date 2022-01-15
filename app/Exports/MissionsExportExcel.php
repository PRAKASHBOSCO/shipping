<?php

namespace App\Exports;

use Auth;
use App\Mission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use Carbon\Carbon;
use App\Http\Controllers\MissionsController;
use App\Http\Helpers\TransactionHelper;

class MissionsExportExcel implements FromCollection,WithHeadings,WithStyles
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
        return ["Code", "Status", "Driver" ,"Type", "Amount", "	Due Date"];
    }
    
    public function collection()
    {

        $missionController = new MissionsController();
        $missions = new Mission;
    
        $missions = $missionController->search($this->data,$missions);
        
        if (isset($this->data->page) && !empty($this->data->page)) {
            $skip_number = $this->data->page - 1;
            $skip_number = $skip_number * 20;
            $missions = $missions->orderBy('id','DESC')->skip($skip_number)->take(20)->get();
        }        

        $helper = new TransactionHelper();
        foreach ($missions as $mission) {
            $order['code']   = $mission->code ?? "";
            $order['status'] = $mission->getStatus() ?? "";
            $order['driver'] = $mission->captain->name ?? translate('No Driver');
            $order['type']   = $mission->type ?? "";

            $mission_amount  = $helper->calcMissionShipmentsAmount($mission->getOriginal('type'),$mission->id);
            $order['amount'] = format_price($mission_amount) ?? "";
            $order['date']   = $mission->due_date ?? "-";
        
            $data[] = $order;
            $order = array();
            
        }

        return collect($data);

    }

}