@extends('backend.layouts.app')

@section('content')

<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Dimension Information')}}</h5>
        </div>

        <form class="form-horizontal" action="{{ route('admin.dimensionConfig.store') }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>{{translate('From Size')}}:</label>
                    <input type="text" id="from_weight" class="form-control" placeholder="{{translate('From Weight')}}" name="DimensionConfig[from_weight]">
                </div>

                <div class="form-group">
                    <label>{{translate('To Size')}}:</label>
                    <input type="text" id="to_weight" class="form-control" placeholder="{{translate('To Weight')}}" name="DimensionConfig[to_weight]">
                </div>
               
                
                <div class="form-group">
                    <label>{{translate('Price District')}}:</label>
                    <input type="text" id="price_district" class="form-control" placeholder="{{translate('Price District')}}" name="DimensionConfig[price_district]">
                </div>
                
                
                <div class="form-group">
                    <label>{{translate('Price State')}}:</label>
                    <input type="text" id="price_state" class="form-control" placeholder="{{translate('Price State')}}" name="DimensionConfig[price_state]">
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