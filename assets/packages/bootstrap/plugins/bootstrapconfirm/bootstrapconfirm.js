'use strict';

jQuery.extend({
    bsconfirm: function bsconfirm(text, i18n, cbok, cbcancel) {

        cbok = cbok || function () {
            $('#identity__bsconfirmModal').modal('hide');
        };
        cbcancel = cbcancel || function () {
            $('#identity__bsconfirmModal').modal('hide');
        };
        i18n = i18n || {};

        var modal = $('\n        <div id="identity__bsconfirmModal" class="modal fade">\n            <div class="modal-dialog">\n                <div class="modal-content">\n                <div class="modal-body">\n                    <button type="button" class="close" data-dismiss="modal">&times;</button>\n                    ' + text + '\n                </div>\n                <div class="modal-footer">\n                    <button id="identity__bsconfirmModal_button_cancel" type="button" class="btn btn-default">' + (i18n.confirm_cancel || '') + ' <i class="fa fa-times"></i></button>\n                    <button id="identity__bsconfirmModal_button_ok" type="button" class="btn btn-danger">' + (i18n.confirm_ok || '') + ' <i class="fa fa-check"></i></button>\n                </div>\n                </div>\n            </div>\n        </div>\n        ');

        if ($('body').find('#identity__bsconfirmModal').length == 0) {
            $('body').append(modal);
        } else {
            $('body').find('#identity__bsconfirmModal').remove();
            $('body').append(modal);
        }

        modal.modal({
            backdrop: "static",
            show: true
        });

        modal.on('hidden.bs.modal', function () {
            modal.remove();
        });
        modal.on('shown.bs.modal', function () {
            $('#identity__bsconfirmModal_button_ok').on('click', cbok);
            $('#identity__bsconfirmModal_button_cancel').on('click', cbcancel);
        });
    }
});

