<?php

namespace App\Exports;

use Auth;
use App\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use Carbon\Carbon;
use App\Http\Controllers\TransactionController;

class TransactionsExportExcel implements FromCollection,WithHeadings,WithStyles
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
        return ["Owner Type", "Owner Name", "Type", "Value", "Date", "Created By", "Description"];
    }
    
    public function collection()
    {
        $transaction_owner[Transaction::CAPTAIN]['text'] = translate("Captain");
        $transaction_owner[Transaction::CAPTAIN]['key'] = "captain";
        $transaction_owner[Transaction::CAPTAIN]['id'] = "captain_id";
        $transaction_owner[Transaction::CLIENT]['text'] = translate("Client");
        $transaction_owner[Transaction::CLIENT]['key'] = "client";
        $transaction_owner[Transaction::CLIENT]['id'] = "client_id";
        $transaction_owner[Transaction::BRANCH]['text'] = translate("Branch");
        $transaction_owner[Transaction::BRANCH]['key'] = "branch";
        $transaction_owner[Transaction::BRANCH]['id'] = "branch_id";

        $transaction_type[Transaction::MESSION_TYPE] = "mission";
        $transaction_type[Transaction::SHIPMENT_TYPE] = "shipment";
        $transaction_type[Transaction::MANUAL_TYPE] = "manual";

        $transactionsController = new TransactionController();
        $transactions = new Transaction;
        
        $transactions = $transactionsController->search($this->data,$transactions);

        if (isset($this->data->page) && !empty($this->data->page)) {
            $skip_number = $this->data->page - 1;
            $skip_number = $skip_number * 20;
            $transactions = $transactions->orderByDesc('id')->skip($skip_number)->take(20)->get();
        }

        foreach ($transactions as $transaction) {
            $order['transaction_owner']      = $transaction_owner[$transaction->transaction_owner]['text'] ?? "";
            $order['transaction_owner_name'] = $transaction->{$transaction_owner[$transaction->transaction_owner]['key']}->name ?? "";

            if($transaction_type[$transaction->type] == 'mission' && $transaction->mission_id)
            {
                $order['type'] = translate('Mission').'( '. $transaction->mission->code.' )' ?? "";
            }elseif($transaction_type[$transaction->type] == 'shipment' && $transaction->shipment_id)
            {
                $order['type'] = translate('Shipment').'( '.$transaction->shipment->barcode.' )' ?? "";
            }elseif($transaction_type[$transaction->type] == 'manual') {
                $order['type'] = translate('Manual');
            }

            $order['value']  = format_price($transaction->value) ?? "";
            $order['date']   = $transaction->created_at->format("Y-m-d h:i") ?? "";

            $created_by = \App\User::where('id', $transaction->created_by )->first();
            if($created_by)
            {
                $order['created_by']  = $created_by->name ?? "";
            }else {
                if($transaction_type[$transaction->type] == 'mission' && $transaction->mission_id)
                {
                    $order['created_by']  = translate('Mission').'( '. $transaction->mission->code.' )' ?? "";
                }elseif($transaction_type[$transaction->type] == 'shipment' && $transaction->shipment_id)
                {
                    $order['created_by']  = translate('Shipment').'( '.$transaction->shipment->barcode.' )' ?? "";
                }
            }
            
            $order['description'] = $transaction->description ?? "-";
        
            $data[] = $order;
            $order = array();
            
        }

        return collect($data);

    }

}