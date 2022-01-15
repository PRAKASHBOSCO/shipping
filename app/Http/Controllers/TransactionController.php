<?php

namespace App\Http\Controllers;

use App\Captain;
use App\Branch;
use App\Client;
use App\Staff;
use App\BusinessSetting;
use App\Http\Controllers\Controller;
use App\Http\Helpers\TransactionHelper;
use App\Transaction;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Exports\TransactionsExportExcel;
use Carbon\Carbon;
use Excel;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(['auth', 'user_role:admin|staff|customer|branch|captain'])->only('index');
        $this->middleware(['auth', 'user_role:admin|staff|branch'])->only('create');
        $this->middleware(['auth', 'user_role:admin|staff|branch'])->only('store');
    }

    public function search($request, $transactions){

        $auth_user = Auth::user();
        if(isset($auth_user))
        {
            if(Auth::user()->user_type == 'customer'){
                $transactions = $transactions->where('client_id', Auth::user()->userClient->client_id);
            }elseif(Auth::user()->user_type == 'branch'){
                $transactions = $transactions->where('branch_id', Auth::user()->userBranch->branch_id)->orWhere('branch_owner_id', Auth::user()->userBranch->branch_id);
            }elseif(Auth::user()->user_type == 'captain'){
                $transactions = $transactions->where('captain_id', Auth::user()->userCaptain->captain_id);
            }
        }

        if (isset($request)) {
            if (isset($request->code) && !empty($request->code)) {
                $transactions = $transactions->where('code', $request->code );
            }
            if (isset($request->owner_type) && !empty($request->owner_type)) {
                $transactions = $transactions->where('transaction_owner', $request->owner_type);
            }
            if (isset($request->captain) && !empty($request->captain)) {
                $transactions = $transactions->where('captain_id', $request->captain );
            }
            if (isset($request->client) && !empty($request->client)) {
                $transactions = $transactions->where('client_id', $request->client );
            }
            if (isset($request->branch) && !empty($request->branch)) {
                $transactions = $transactions->where('branch_id', $request->branch );
            }
            if (isset($request->date) && !empty($request->date)) {
                $transactions = $transactions->whereDate('created_at', '=', date($request->date));
            }
            
        }

        return $transactions;
    }

    public function index(Request $request)
    {
        
        $branchs = Branch::where('is_archived', 0)->get();
        $clients = Client::where('is_archived', 0)->get();
        $captains = Captain::where('is_archived', 0)->get();

        $types[Transaction::CAPTAIN] = ["name"=> translate("Captain"),"key"=> "captain"];
        $types[Transaction::CLIENT] = ["name"=> translate("Client"),"key"=> "client"];
        $types[Transaction::BRANCH] = ["name"=> translate("Branch"),"key"=> "branch"];
        $types[Transaction::FRANCHISE] = ["name"=> translate("Franchise"),"key"=> "branch"];

        $transactions = new Transaction;
        
        $transactions = $this->search($request, $transactions);
        $transactions = $transactions->with('client','branch','captain','mission','shipment')->orderByDesc('id')->paginate(20);

        $transaction_owner[Transaction::CAPTAIN]['text'] = translate("Captain");
        $transaction_owner[Transaction::CAPTAIN]['key'] = "captain";
        $transaction_owner[Transaction::CAPTAIN]['id'] = "captain_id";
        $transaction_owner[Transaction::CLIENT]['text'] = translate("Client");
        $transaction_owner[Transaction::CLIENT]['key'] = "client";
        $transaction_owner[Transaction::CLIENT]['id'] = "client_id";
        $transaction_owner[Transaction::BRANCH]['text'] = translate("Branch");
        $transaction_owner[Transaction::BRANCH]['key'] = "branch";
        $transaction_owner[Transaction::BRANCH]['id'] = "branch_id";
        $transaction_owner[Transaction::FRANCHISE]['text'] = translate("Franchise");
        $transaction_owner[Transaction::FRANCHISE]['key'] = "branch";
        $transaction_owner[Transaction::FRANCHISE]['id'] = "branch_id";

        $transaction_type[Transaction::MESSION_TYPE] = "mission";
        $transaction_type[Transaction::SHIPMENT_TYPE] = "shipment";
        $transaction_type[Transaction::MANUAL_TYPE] = "manual";
        $transaction_type[Transaction::COMMISSION_TYPE] = "commission";
        $transaction_type[Transaction::CREDIT_TYPE] = "credit";

        $page_name = translate('All Transactions');
        // return $transactions;
        return view('backend.transactions.index', compact('transactions', 'page_name', 'transaction_owner','transaction_type','branchs', 'clients','captains','types'));
    }

    public function getClientTransaction($client_id)
    {
        $transactions = Transaction::where('client_id',$client_id)->orderBy('created_at','desc')->get();
        $client = Client::find($client_id);
        // $transactions_by_month = Transaction::select([
        //     DB::raw("DATE_FORMAT(created_at, '%m') month"),
        //     DB::raw("SUM(value) sum_value")
        // ])->whereRaw("DATE_FORMAT(created_at, '%y') = DATE_FORMAT(NOW(), '%y')")->where('client_id',$client_id)->groupBy('month')->get();
        $chart_categories = array();
        $chart_values = array();
        // foreach($transactions_by_month as $trans)
        // {
        //     array_push($chart_categories,$trans->month);
        //     array_push($chart_values,$trans->sum_value);
        // }
        return view('backend.transactions.show-client-transactions')
        ->with('transactions',$transactions)
        ->with('client',$client)
        ->with('chart_categories',$chart_categories)
        ->with('chart_values',$chart_values);
    }

    public function getCaptainTransaction($captain_id)
    {
        $transactions = Transaction::where('captain_id',$captain_id)->orderBy('created_at','desc')->get();
        $captain = Captain::find($captain_id);

        $chart_categories = array();
        $chart_values = array();

        return view('backend.transactions.show-captain-transactions')
        ->with('transactions',$transactions)
        ->with('captain',$captain)
        ->with('chart_categories',$chart_categories)
        ->with('chart_values',$chart_values);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->user_type == 'branch' || Auth::user()->user_type == 'staff'){
            if(Auth::user()->user_type == 'staff'){
                $staff = staff::where('user_id', Auth::user()->id)->first();
                $branch_id = $staff->branch_id;
            }else {
                $branch_id = Auth::user()->userBranch->branch_id;
            }
            $clients = Client::where('is_archived', 0)->where('branch_id', $branch_id)->get();
            $captains = Captain::where('is_archived', 0)->where('branch_id',$branch_id)->get();
        }else {
            $clients = Client::where('is_archived', 0)->get();
            $captains = Captain::where('is_archived', 0)->get();

            $types[Transaction::BRANCH] = ["name"=> translate("Branch"),"key"=> "branch"];
        }
        $branchs = Branch::where('is_archived', 0)->where('type', 1)->get();
        $franchises = Branch::where('is_archived', 0)->where('type', 2)->get();

        $types[Transaction::FRANCHISE] = ["name" => translate("Franchise"), "key" => "franchise"];
        $types[Transaction::CAPTAIN] = ["name"=> translate("Captain"),"key"=> "captain"];
        $types[Transaction::CLIENT] = ["name"=> translate("Client"),"key"=> "client"];

        return view('backend.transactions.create', compact('branchs', 'franchises', 'clients', 'captains', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|integer|min:1|max:4',
            'branch' => 'nullable|exists:branchs,id',
            'client' => 'nullable|exists:clients,id',
            'captain' => 'nullable|exists:captains,id',
            'amount' => 'required|integer|min:1,max:999999999',
            'wallet_type' => 'required|min:1,max:7',
            'description' => 'nullable|max:5000',
        ]);
        if(!$request->branch && !$request->client && !$request->captain){
            flash(translate("Please select branch , client or captain"))->error();
            return back();
        }
        $types[Transaction::CAPTAIN] = "captain";
        $types[Transaction::CLIENT] = "client";
        $types[Transaction::BRANCH] = "branch";
        $types[Transaction::FRANCHISE] = "franchise";

        if($request->wallet_type == "add"){
            $amount_sign = Transaction::CREDIT;
        }elseif($request->wallet_type == "deduct"){
            $amount_sign = Transaction::DEBIT;
        }else{
            flash(translate("Invalid Wallet Type"))->error();
            return back();
        }

        $transaction = new TransactionHelper();

        if($types[$request->type] == "captain"){
            $captain = Captain::where('id', $request->captain)->withCount(['transaction AS wallet' => function ($query) { $query->select(DB::raw("SUM(value)")); }])->first();
            if($request->wallet_type == "deduct"){
                if(($captain->wallet - (int) $request->amount) < 0 ){
                    flash(translate("Captain Not Have This Amount"))->error();
                    return back();
                }
            }
            
            $transaction->create_mission_transaction(null,abs($request->amount) ,Transaction::CAPTAIN,$request->captain,$amount_sign,Transaction::MANUAL_TYPE,$request->description, $captain->branch_id);
        }elseif($types[$request->type] == "client"){
            $client = Client::where('id', $request->client)->first();
            $transaction->create_mission_transaction(null,abs($request->amount) ,Transaction::CLIENT,$request->client,$amount_sign,Transaction::MANUAL_TYPE,$request->description, $client->branch_id);
        }elseif($types[$request->type] == "branch"){
            $transaction->create_mission_transaction(null,abs($request->amount) ,Transaction::BRANCH,$request->branch,$amount_sign,Transaction::MANUAL_TYPE,$request->description, $request->branch);
        }elseif($types[$request->type] == "franchise"){
            $transaction->create_mission_transaction(null,abs($request->amount) ,Transaction::FRANCHISE,$request->branch,$amount_sign,Transaction::MANUAL_TYPE,$request->description, $request->branch);
        }else{
            flash(translate("Invalid Data"))->error();
            return back();
        }
        flash(translate("Transaction created successfully"))->success();
        return back();
    }

    public function exportTransaction(Request $request)
    {
        $excelName = '';
        $time_zone = BusinessSetting::where('type', 'timezone')->first();
        if($time_zone->value == null){
            $excelName = 'export_transaction_'.Carbon::now()->toDateString().'.xlsx';
        }else {
            $excelName = 'export_transaction_'.Carbon::now($time_zone->value)->toDateString().'.xlsx';
        }

        return Excel::download( new TransactionsExportExcel($request) , $excelName );
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
    
    public function creditPayment($id) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = Transaction::findOrFail($id);
        if ($model != null) {
            $shipment = \App\Shipment::find($model->shipment_id);
            $bookAmt = $shipment->tax + $shipment->shipping_cost + $shipment->shipping_distance_cost + $shipment->pickup_cost + $shipment->cod_cost + $shipment->door_delivery_cost - $shipment->discount_amt;
            $from_branch = Branch::where('id', $shipment->from_branch_id)->first();
            $transaction = new TransactionHelper();
            $owner = Transaction::FRANCHISE;
            if ($from_branch->type == 1) {
                $owner = Transaction::BRANCH;
            }
            $transaction->create_shipment_transaction($shipment->id, $bookAmt, $owner, $from_branch->id, Transaction::CREDIT, Transaction::SHIPMENT_TYPE, 'Credit shipment');
            $shipment->paid = 1;
            $shipment->save();
            flash(translate('Invoice paid successfully'))->success();
            return redirect()->back();
        }
        return back();
    }
}
