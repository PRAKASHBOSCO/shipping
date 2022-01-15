@extends('backend.layouts.app')

@section('content')
@php
$auth_user = Auth::user();

$user_type = Auth::user()->user_type;
$countries = \App\Country::where('covered',1)->get();
@endphp
<div class="mx-auto col-lg-12">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Franchise Information')}}</h5>
        </div>

        <form class="form-horizontal" action="{{ route('admin.franchises.store') }}" id="kt_form_1" method="POST" enctype="multipart/form-data">
            @csrf
            {!!redirect_input()!!}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Franchise Name')}}:</label>
                            <input type="text" id="name" class="form-control" placeholder="{{translate('Franchise Name')}}" name="Branch[name]">
                            <input type="hidden" class="form-control" value="2" name="Branch[type]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Franchise Code')}}:</label>
                            <input type="text" id="code" class="form-control" placeholder="{{translate('Franchise Code')}}" name="Branch[code]">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Owner Phone')}}:</label>
                            <input type="text" class="form-control" placeholder="{{translate('Owner Phone')}}" name="Branch[responsible_mobile]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Email')}}:</label>
                            <input id="email-field" type="text" class="form-control" placeholder="{{translate('Email')}}" name="Branch[email]">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{translate('Profile Picture')}}:</label>

                            <div class="input-group " data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="img" class="selected-files" value="{{old('featured_image')}}">
                            </div>
                            <div class="file-preview">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Password')}}:</label>
                            <input type="password" class="form-control" id="password" placeholder="{{translate('Password')}}" name="User[password]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Confirm Password')}}:</label>
                            <input type="password" class="form-control" placeholder="{{translate('Confirm Password')}}" name="User[confirm_password]">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{translate('Country')}}:</label>
                            <select id="change-country-client-address" name="Branch[country_id]" class="form-control select-country">
                                <option value=""></option>
                                @foreach($countries as $country)
                                <option value="{{$country->id}}">{{$country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{translate('Region')}}:</label>
                            <select id="change-state-client-address" name="Branch[state_id]" class="form-control select-state">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{translate('Area')}}:</label>
                            <select name="Branch[area_id]" style="display: block !important;" class="form-control select-area">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{translate('Address')}}:</label>
                            <input type="text" class="form-control" placeholder="{{translate('Address')}}" name="Branch[address]">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Booking Commission (%)')}}:</label>
                            <input type="text" class="form-control" placeholder="{{translate('Booking Commission (%)')}}" name="Branch[booking_commission]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{translate('Delivery Commission (%)')}}:</label>
                            <input type="text" class="form-control" placeholder="{{translate('Delivery Commission (%)')}}" name="Branch[delivery_commission]">
                        </div>
                    </div>
                </div>

                {!! hookView('spot-cargo-shipment-branch-addon',$currentView) !!}               

                <div class="mb-0 text-right form-group">
                    <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function () {
        FormValidation.formValidation(
                document.getElementById('kt_form_1'), {
            fields: {
                "Branch[name]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        }
                    }
                },
                "Branch[email]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        },
                        emailAddress: {
                            message: '{{translate("This is should be valid email!")}}'
                        },
                        remote: {
                            data: {
                                type: 'Branch',
                            },
                            message: 'The email is already exist',
                            method: 'GET',
                            url: '{{ route("user.checkEmail") }}',
                        }
                    }
                },
                "User[password]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        }
                    }
                },
                "User[confirm_password]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        },
                        identical: {
                            compare: function () {
                                return  document.getElementById('kt_form_1').querySelector('[name="User[password]"]').value;
                            },
                            message: 'The password and its confirm are not the same'
                        }
                    }
                },
                "Branch[responsible_name]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        }
                    }
                },
                "Branch[responsible_mobile]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        }
                    }
                },
                "Branch[national_id]": {
                    validators: {
                        notEmpty: {
                            message: '{{translate("This is required!")}}'
                        },
                        numeric: {
                            message: 'This is should be valid national id'
                        }
                    }
                }

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
        });

        $('#change-country-client-address').change(function () {
            var id = $(this).val();
            $.get("{{route('admin.shipments.get-states-ajax')}}?country_id=" + id, function (data) {
                $('select[name="Branch[state_id]"]').empty();
                $('select[name="Branch[state_id]"]').append('<option value=""></option>');
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];

                    $('select[name="Branch[state_id]"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }


            });
        });

        $('#change-state-client-address').change(function () {
            var id = $(this).val();
            $.get("{{route('admin.shipments.get-areas-ajax')}}?state_id=" + id, function (data) {
                $('select[name="Branch[area_id]"]').empty();
                $('select[name="Branch[area_id]"]').append('<option value=""></option>');
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];
                    $('select[name="Branch[area_id]"]').append('<option value="' + element['id'] + '">' + element['name'] + '</option>');
                }


            });
        });
        
        $('.select-country').select2({
            placeholder: "Select country",
            language: {
              noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.shipments.covered_countries')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('Countries')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
              },
            },
            escapeMarkup: function(markup) {
              return markup;
            },
        });

        $('.select-state').select2({
            placeholder: "Select state",
            language: {
              noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.shipments.covered_countries')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('States')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
              },
            },
            escapeMarkup: function(markup) {
              return markup;
            },
        });
        
        $('.select-area').select2({
            placeholder: "Select Area",
            language: {
              noResults: function() {
                @if($user_type == 'admin' || in_array('1105', $staff_permission) )
                    return `<li style='list-style: none; padding: 10px;'><a style="width: 100%" href="{{route('admin.areas.create')}}?redirect=admin.shipments.create"
                    class="btn btn-primary" >Manage {{translate('Areas')}}</a>
                    </li>`;
                @else
                    return ``;
                @endif
              },
            },
            escapeMarkup: function(markup) {
              return markup;
            },
        });
    });
</script>
@endsection
