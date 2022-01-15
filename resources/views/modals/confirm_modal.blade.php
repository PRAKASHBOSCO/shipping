<!-- delete Modal -->
<div id="complete-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Confirmation')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mt-1">{{translate('Are you sure that this client paid this invoice?')}}</p>
                <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{translate('No')}}</button>
                <a href="" id="comfirm-link" class="btn btn-primary mt-2">{{translate('Yes')}}</a>
            </div>
        </div>
    </div>
</div><!-- /.modal -->
