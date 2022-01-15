@extends('backend.layouts.app')

@php
    $user_type = Auth::user()->user_type;
    $staff_permission = json_decode(Auth::user()->staff->role->permissions ?? "[]");
    $auth_user = Auth::user();
@endphp

@section('sub_title'){{translate('Transactions')}}@endsection
@section('subheader')
    <!--begin::Subheader-->
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <!--begin::Info-->
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <!--begin::Page Heading-->
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <!--begin::Page Title-->
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">{{translate('Transactions')}}</h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="p-0 my-2 mr-5 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard')}}" class="text-muted">{{translate('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="#" class="text-muted">{{ translate('Transactions') }}</a>
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                    @if($user_type == 'admin' || in_array('1106', $staff_permission) )
                        <a href="{{ route('admin.transactions.create') }}" class="btn btn-light-primary font-weight-bolder btn-sm"><i class="flaticon2-add-1"></i> {{translate('Add New Transaction')}}</a>
                    @endif
                </div>
                <!--end::Page Heading-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->
@endsection

@section('content')

<!--begin::Card-->
<div class="card card-custom gutter-b">
    <!--<div class="flex-wrap py-3 card-header">-->
    <!--    <div class="card-title">-->
    <!--        <h3 class="card-label">-->
    <!--            {{$page_name}}-->
    <!--        </h3>-->
    <!--    </div>-->
    <!--</div>-->
    <div class="m-5" id="tableForm">
        <!--<table class="table mb-0 aiz-table"  data-show-toggle="true" data-toggle-column="first">-->
            <table class="table mb-0 display" id="listing_data_table"   data-show-toggle="true" data-toggle-column="first">
            <thead>
                <tr>
                    <th width="3%">#</th>
                    <th>{{translate('Owner Type')}}</th>
                    <th>{{translate('Owner Name')}}</th>
                    <th>{{translate('Type')}}</th>
                    <th>{{translate('Value')}}</th>
                    <th>{{translate('Date')}}</th>
                    <th>{{translate('Created By')}}</th>
                    <th data-breakpoints="all" data-title="-">{{translate('Description')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $key=>$transaction)
                   @php 
                    $client = $transaction->{$transaction_owner[$transaction->transaction_owner]['key']} ?? "";
                    $created_by = \App\User::where('id', $transaction->created_by )->first();
                    @endphp
                    <tr @if($loop->first) data-expanded="true" @endif>
                        <td width="3%">{{ ($key+1) + ($transactions->currentPage() - 1)*$transactions->perPage() }}</td>
                        
                        <td>{{$transaction_owner[$transaction->transaction_owner]['text']  ?? ""}}</td>
                        
                        <td><a href="{{route('admin.'.($transaction_owner[$transaction->transaction_owner]['key']  ?? "").'s.show',($transaction->{$transaction_owner[$transaction->transaction_owner]['id']} ?? ""))}}"></a>{{$client['name']}}
                        </td>

                        <td>
                            @if($transaction_type[$transaction->type] == 'mission' && $transaction->mission_id)
                                <a href="{{route('admin.missions.show', $transaction->mission_id )}}">{{translate('Mission')}}({{$transaction->mission->code ?? ""}}) </a>
                            @elseif($transaction_type[$transaction->type] == 'shipment' && $transaction->shipment_id)
                                <a href="{{route('admin.shipments.show', $transaction->shipment_id )}}">{{$transaction->shipment->barcode ?? ""}} </a>
                            @elseif($transaction_type[$transaction->type] == 'manual')
                                {{translate('Manual')}}
                            @elseif($transaction_type[$transaction->type] == 'commission' && $transaction->shipment_id)
                                <a href="{{route('admin.shipments.show', $transaction->shipment_id )}}">{{translate('Commission')}}({{$transaction->shipment->barcode ?? ""}}) </a>
                            @elseif($transaction_type[$transaction->type] == 'credit' && $transaction->shipment_id)
                                <a href="{{route('admin.shipments.show', $transaction->shipment_id )}}">{{translate('Credit')}}({{$transaction->shipment->barcode ?? ""}}) </a>
                                @if($transaction->shipment->paid == 0)
                                    <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-complete" data-href="{{route('admin.transactions.paycredit', ['trancsction_id'=>$transaction->id])}}" title="{{ translate('Complete') }}">
                                        <i class="las la-money-bill-wave"></i>
                                    </a>
                                @endif
                            @endif
                        </td>
                        <td>{{format_price($transaction->value) ?? ""}}</td>
                        <td>{{$transaction->created_at->format("Y-m-d h:i") ?? ""}}</td>
                        <td>{{$created_by->name}}</td>
                        <td>{{$transaction->description ?? "-"}}</td>
                    </tr>

                @endforeach

            </tbody>
        </table>
    </div>

    <!--<div class="aiz-pagination">-->
    <!--    {{ $transactions->appends(request()->input())->links() }}-->
    <!--</div>-->
</div>

@endsection

@section('modal')
{{-- @include('modals.delete_modal') --}}
@include('modals.confirm_modal')
@endsection

@section('script')
    <script type="text/javascript">
    </script>
@endsection
