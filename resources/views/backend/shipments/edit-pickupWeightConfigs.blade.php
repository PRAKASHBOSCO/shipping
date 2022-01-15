@extends('backend.layouts.app')

@section('content')

<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Pickup Weight Information')}}</h5>
        </div>

        <form class="form-horizontal" action="{{ route('admin.pickupWeightConfig.update',['pickupWeightConfig'=>$PickupWeightConfig->id]) }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            {{ method_field('PATCH') }}
            <div class="card-body">
                <div class="form-group">
                    <label>{{translate('From Weight')}}:</label>
                    <input type="text" id="from_weight" class="form-control" value="{{$PickupWeightConfig->from_weight}}" placeholder="{{translate('From Weight')}}" name="PickupWeightConfig[from_weight]">
                </div>

                <div class="form-group">
                    <label>{{translate('To Weight')}}:</label>
                    <input type="text" id="to_weight" class="form-control" value="{{$PickupWeightConfig->to_weight}}" placeholder="{{translate('To Weight')}}" name="PickupWeightConfig[to_weight]">
                </div>

                <div class="form-group">
                    <label>{{translate('Percentage')}}:</label>
                    <input type="text" id="percentage" class="form-control" value="{{$PickupWeightConfig->percentage}}" placeholder="{{translate('Percentage')}}" name="PickupWeightConfig[percentage]">
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