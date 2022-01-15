<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Client;
use App\ClientPayment;
use App\Http\Helpers\UserRegistrationHelper;
use App\User;
use App\UserClient;
use DB;
use Auth;
use App\BusinessSetting;
use App\Events\AddClient;
use App\AddressClient;
use App\ClientPackage;
use Carbon\Carbon;
use App\Package;
use App\Branch;

class ClientPaymentController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $payments = ClientPayment::where('status', 1)->paginate(15);
        return view('backend.clients.index-payments', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $clients = Client::where('is_archived', 0)->get();
        return view('backend.clients.create-payments', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try {
            DB::beginTransaction();
            $model = new ClientPayment();

            $model->fill($_POST['Payment']);

            if (!$model->save()) {
                throw new \Exception();
            }

            $client = Client::where('id', $_POST['Payment']['client_id'])->first();
            $client->credit_balance_amount = ($client->credit_balance_amount - $_POST['Payment']['amount']);
            $client->save();
            DB::commit();
            flash(translate("Payment added successfully"))->success();
            return back();
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            exit;

            flash(translate("Error"))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($payment) {
        if (env('DEMO_MODE') == 'On') {
            flash(translate('This action is disabled in demo mode'))->error();
            return back();
        }
        $model = ClientPayment::findOrFail($payment);
        $client = Client::where('id', $model->client_id)->first();
        if ($client != null) {
            $client->credit_balance_amount = ($client->credit_balance_amount + $model->amount);
            $client->save();
        }
        if ($model->delete()) {
            flash(translate('Payment deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

}
