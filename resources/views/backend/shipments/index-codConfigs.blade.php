@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Package Types')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('admin.codConfig.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New COD Config')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('COD Config')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th  width="3%">#</th>
                    <th >{{translate('From Amount')}}</th>
                    <th >{{translate('To Amount')}}</th>
                    <th >{{translate('Price')}}</th>
                    <th class="text-center">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($CodConfigs as $key => $time)
                    
                        <tr>
                            <td  width="3%">{{ ($key+1) + ($CodConfigs->currentPage() - 1)*$CodConfigs->perPage() }}</td>
                            <td>{{format_price($time->from_amount)}}</td>
                            <td>{{format_price($time->to_amount)}}</td>
                            <td>{{format_price($time->price)}}</td>
                           
                           
                            <td class="text-center">
                                    
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('admin.codConfig.edit', $time->id)}}" title="{{ translate('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.codConfigs.delete-codConfig', ['codConfig'=>$time->id])}}" title="{{ translate('Delete') }}">
		                                <i class="las la-trash"></i>
		                            </a>
		                        </td>
                        </tr>
               
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $CodConfigs->appends(request()->input())->links() }}
        </div>
    </div>
</div>
{!! hookView('shipment_addon',$currentView) !!}

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
