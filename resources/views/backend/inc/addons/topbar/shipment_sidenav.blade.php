@php 
$addon = \App\Addon::where('unique_identifier', 'spot-cargo-shipment-addon')->first();
@endphp
@if ($addon != null)
    @if($addon->activated)
        @if(in_array(Auth::user()->user_type , ['admin','customer','branch']) || in_array('1108', json_decode(Auth::user()->staff->role->permissions ?? "[]")))
            <li class="menu-item menu-item-rel ">
                <a href="{{ route('admin.shipments.create') }}" class="mr-3 btn btn-success btn-sm">
                    + {{translate('Add Shipment')}}<i class="ml-2 flaticon2-box-1"></i>
                </a>
            </li>
        @endif
        <li class="menu-item menu-item-rel ">
            <a href="{{ route('admin.shipments.track') }}" class="mr-3 btn btn-primary btn-sm">
                {{translate('Track Shipment')}}<i class="ml-2 flaticon2-search"></i>
            </a>
        </li>
    @endif
@endif
