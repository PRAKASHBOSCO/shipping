@extends('backend.layouts.app')

@section('content')

<div class="mt-2 mb-3 text-left aiz-titlebar">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Reasons')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('admin.reasons.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Reason')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Reasons')}}</h5>
    </div>
    <div class="card-body">
        <table class="table mb-0 aiz-table">
            <thead>
                <tr>
                    <th  width="3%">#</th>
                    <th >{{translate('Reason')}}</th>
                   
                    <th class="text-center">{{translate('Options')}}</th>
                    
                </tr>
            </thead>
            <tbody>
                @foreach($reasons as $key => $reason)
                    
                        <tr>
                            <td  width="3%">{{ ($key+1) + ($reasons->currentPage() - 1)*$reasons->perPage() }}</td>
                            <td>{{$reason->name}}</td>
                           
                           
                            <td class="text-center">
                                    
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('admin.reasons.edit', $reason->id)}}" title="{{ translate('Edit') }}">
                                    <i class="las la-edit"></i>
                                </a>
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.reasons.delete-reason', ['reason'=>$reason->id])}}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
               
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $reasons->appends(request()->input())->links() }}
        </div>
    </div>
</div>
{!! hookView('shipment_addon',$currentView) !!}

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
