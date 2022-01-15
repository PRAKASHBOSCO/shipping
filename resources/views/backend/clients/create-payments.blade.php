@php
    $staff_permission = json_decode(Auth::user()->staff->role->permissions ?? "[]");
@endphp

@extends('backend.layouts.app')

@section('content')

<div class="mx-auto col-lg-12">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Payment Information')}}</h5>
        </div>

        <form class="form-horizontal" action="{{ route('admin.payments.store') }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            {!!redirect_input()!!}
            <div class="card-body">
                <div class="form-group">
                    <label>{{translate('Amount')}}:</label>
                    <select class="form-control kt-select2 select-client" name="Payment[client_id]">
                                            <option></option>
                                            @foreach($clients as $client)
                                            <option value="{{$client->id}}" data-phone="{{$client->responsible_mobile}}">{{$client->responsible_mobile}} ({{$client->name}})</option>
                                            @endforeach

                                        </select>
                </div>
                <div class="form-group">
                    <label>{{translate('Amount')}}:</label>
                    <input type="text" id="amount" class="form-control" placeholder="{{translate('Amount')}}" name="Payment[amount]" />
                </div>
                <div class="form-group">
                    <label>{{translate('Payment Date')}}:</label>
                    <input id="payment_date" type="text" class="form-control" placeholder="{{translate('Payment Date')}}" name="Payment[payment_date]" />
                </div>
            <div class="form-group">
                            <label>{{translate('Reference')}}:</label>
                            <textarea class="form-control" rows="5" placeholder="{{translate('Reference')}}" name="Payment[transaction_reference]"></textarea>
            </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                </div>
                </div>
        </form>
    </div>
    </div>
@endsection

@section('script')
<script src="{{ static_asset('assets/dashboard/js/geocomplete/jquery.geocomplete.js') }}"></script>

<script type="text/javascript">
    $('#payment_date').datepicker({
            orientation: "bottom auto",
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayBtn: true,
            todayHighlight: true,
            startDate: new Date(),
        });
        $('.select-client').select2({
            placeholder: "Select Client",
        })
    $(document).ready(function() {
        FormValidation.formValidation(
            document.getElementById('kt_form_1'), {
                fields: {
                    "Payment[amount]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "Payment[payment_date]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            },
                        }
                    },
                    "Payment[transaction_reference]": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
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
