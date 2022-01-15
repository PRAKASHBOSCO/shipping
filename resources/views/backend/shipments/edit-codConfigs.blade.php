@extends('backend.layouts.app')

@section('content')

<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('COD Information')}}</h5>
        </div>

        <form class="form-horizontal" action="{{ route('admin.codConfig.update',['codConfig'=>$CodConfig->id]) }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            {{ method_field('PATCH') }}
            <div class="card-body">
                <div class="form-group">
                    <label>{{translate('From Amount')}}:</label>
                    <input type="text" id="from_amount" class="form-control" value="{{$CodConfig->from_amount}}" placeholder="{{translate('From Amount')}}" name="CodConfig[from_amount]">
                </div>

                <div class="form-group">
                    <label>{{translate('To Amount')}}:</label>
                    <input type="text" id="to_amount" class="form-control" value="{{$CodConfig->to_amount}}" placeholder="{{translate('To Amount')}}" name="CodConfig[to_amount]">
                </div>

                <div class="form-group">
                    <label>{{translate('Price')}}:</label>
                    <input type="text" id="price" class="form-control" value="{{$CodConfig->price}}" placeholder="{{translate('Price')}}" name="CodConfig[price]">
                </div>
               
                
                {!! hookView('shipment_addon',$currentView) !!}               

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        FormValidation.formValidation(
            document.getElementById('kt_form_1'), {
                fields: {
                    "name": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "hours": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            },
                            numeric: {
                                message: 'This is should be valid Hours'
                            }
                        }
                    },

                },
                

                plugins: {
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    trigger: new FormValidation.plugins.Trigger(),
                    // Bootstrap Framework Integration
                    bootstrap: new FormValidation.plugins.Bootstrap(),
                    // Validate fields when clicking the Submit button
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    // Submit the form when all fields are valid
                    defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
                    icon: new FormValidation.plugins.Icon({
                        valid: 'fa fa-check',
                        invalid: 'fa fa-times',
                        validating: 'fa fa-refresh',
                    }),
                }
            }
        );
    });
</script>
@endsection