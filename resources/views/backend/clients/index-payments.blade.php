@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Payments')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('admin.payments.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Payment')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Customer Payments')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th  width="3%">#</th>
                    <th >{{translate('Client')}}</th>
                    <th >{{translate('Amount')}}</th>
                    <th >{{translate('Trans. Date')}}</th>
                    <th >{{translate('Reference')}}</th>
                    
                    <th  width="10%" class="text-center">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $key => $payment)
                    
                        <tr>
                            <td  width="3%">{{ ($key+1) + ($payments->currentPage() - 1)*$payments->perPage() }}</td>
                            <td width="20%">{{$payment->client->name}}</td>
                            <td width="20%">{{format_price($payment->amount)}}</td>
                            <td width="20%">{{$payment->payment_date}}</td>
                            <td width="20%">{{$payment->transaction_reference}}</td>
                           
                            <td class="text-center">
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.payments.delete-payment', ['payment'=>$payment->id])}}" title="{{ translate('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td>
                        </tr>
               
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $payments->appends(request()->input())->links() }}
        </div>
    </div>
</div>
{!! hookView('spot-cargo-shipment-client-addon',$currentView) !!}

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
