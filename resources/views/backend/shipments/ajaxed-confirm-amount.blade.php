<style>
    canvas#signaturePad {
        background-color: #f7f8fa;
        border: 1px solid #ebedf2;
        width: 100%;
        display: block;
        border-radius: 5px;
        color: #000;
        margin-top:5px;
    }
    #signaturePadImg{
        display:none;
    }
</style>
<form id="kt_form_1" class="kt_form" action="{{route('admin.shipments.action',['to'=>\App\Shipment::DELIVERED_STATUS])}}" method="POST">
    @csrf
    <div class="modal-header">
        <h4 class="modal-title h6">{{translate('Confirm Shipment Amount')}}</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{translate('Amount')}}({{currency_symbol()}}):</label>
                        <input type="hidden" class="form-control" value="{{$shipment->id}}" name="checked_ids[]" />
                @if($shipment->payment_method_id == 16707)
                    <input type="number" class="form-control"  value="{{ $shipment->amount_to_be_collected }}" name="amount"
                                style="background:#f3f6f9;color:#3f4254;" disabled /> 
                @else
                    @if($shipment->payment_type == \App\Shipment::POSTPAID)
                        <input type="number" class="form-control"  value="{{ $shipment->amount_to_be_collected + $shipment->shipping_cost + $shipment->tax + $shipment->insurance + $shipment->shipping_distance_cost + $shipment->pickup_cost + $shipment->cod_cost + $shipment->door_delivery_cost - $shipment->discount_amt }}" name="amount" style="background:#f3f6f9;color:#3f4254;" disabled /> 
                    @elseif($shipment->payment_type == \App\Shipment::PREPAID)
                        <input type="number" class="form-control"  value="{{ $shipment->amount_to_be_collected }}" name="amount" style="background:#f3f6f9;color:#3f4254;" disabled /> 
                    @endif
                @endif
                </div>
            </div>

        </div>
        @if(\App\ShipmentSetting::getVal('def_shipment_conf_type') == 'seg')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{translate('Draw Customer Signature')}}:</label>
                    <div class="signature_container">
                        <div class="btn-group" role="group" aria-label="First group">
                            <button type="button" class="btn btn-sm btn-primary" id="undo"><i class="la la-undo"></i> {{'Undo'}}</button>
                            <button type="button" class="btn btn-sm btn-warning" id="clear"><i class="la la-remove"></i> {{'Clear'}}</button>
                        </div>
                        <canvas id="signaturePad"></canvas>
                        <textarea type="hidden" id="signaturePadImg" name="signaturePadImg" class="kt-hide"></textarea>
                    </div>
                    <span class="form-text text-muted">{{'You can use your mouse to draw it, or if you using your mobile then you can use the touch screen to write it by your finger'}}</span>
                </div>
            </div>
        </div>
        @elseif(\App\ShipmentSetting::getVal('def_shipment_conf_type') == 'otp')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{translate('OTP')}}:</label>

                    <input type="text" name="otp_confirm" class="form-control" value="" name="otp" />
                </div>
            </div>

        </div>
        @endif
        {{-- <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{translate('Attachments After Shipping')}}:</label>

                    <div class="input-group " data-toggle="aizuploader" data-type="image" data-multiple="true">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="Shipment[attachments_after_shipping]" class="selected-files" value="" max="3">
                    </div>
                    <div class="file-preview">
                    </div>
                </div>
            </div>
        </div> --}}

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
        <button type="submit" id="confirm" class="btn btn-primary">{{translate('Confirm amount and Done')}}</button>
    </div>
</form>
<script>
    var canvas = document.querySelector("canvas");
    var signaturePad = new SignaturePad(canvas);

    document.getElementById('clear').addEventListener('click', function () {
            signaturePad.clear();
    });

    document.getElementById('undo').addEventListener('click', function () {
        var data = signaturePad.toData();
            if (data) {
            data.pop(); // remove the last dot or line
            signaturePad.fromData(data);
            }
    });


    $('body').on('click', '#confirm', function(e, clickedIndex, newValue, oldValue){
        e.preventDefault();
        var dataURL = canvas.toDataURL();
        var teet = signaturePad.toDataURL("data:image/png;base64,signature");
        $('#signaturePadImg').val(dataURL);
        $('.kt_form').submit();
    });

    $( document ).ready(function() {
        FormValidation.formValidation(
            document.getElementById('kt_form_1'), {
                fields: {
                    "otp_confirm": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
                            }
                        }
                    },
                    "amount": {
                        validators: {
                            notEmpty: {
                                message: '{{translate("This is required!")}}'
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
                        valid: '',
                        invalid: 'fa fa-times',
                        validating: 'fa fa-refresh',
                    }),
                }
            }
        );
    });
</script>
