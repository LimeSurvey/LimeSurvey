jQuery.extend({
    bsconfirm : function(text, i18n, cbok, cbcancel){

        cbok = cbok || function(){$('#identity__bsconfirmModal').modal('hide');};
        cbcancel = cbcancel || function(){$('#identity__bsconfirmModal').modal('hide');};
        i18n = i18n || {};

        const modal = $(`
        <div id="identity__bsconfirmModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    ${text}
                </div>
                <div class="modal-footer">
                    <button id="identity__bsconfirmModal_button_cancel" type="button" class="btn btn-default">${i18n.confirm_cancel || ''} <i class="fa fa-times"></i></button>
                    <button id="identity__bsconfirmModal_button_ok" type="button" class="btn btn-danger">${i18n.confirm_ok || ''} <i class="fa fa-check"></i></button>
                </div>
                </div>
            </div>
        </div>
        `);

        if($('body').find('#identity__bsconfirmModal').length == 0) {
            $('body').append(modal);
        } else {
            $('body').find('#identity__bsconfirmModal').remove();
            $('body').append(modal);
        }

        modal.modal({
            backdrop: "static",
            show: true,
        });

        modal.on('hidden.bs.modal', function(){
            modal.remove();
        });
        modal.on('shown.bs.modal', function(){
            $('#identity__bsconfirmModal_button_ok').on('click', cbok);
            $('#identity__bsconfirmModal_button_cancel').on('click', cbcancel);
        });
    }
});