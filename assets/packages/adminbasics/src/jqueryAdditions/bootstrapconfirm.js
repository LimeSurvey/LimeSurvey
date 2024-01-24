'use strict';

$.fn.extend({
    bsconfirm: function bsconfirm(text, i18n, cbok, cbcancel) {

        cbok = cbok || function () {
            $('#identity__bsconfirmModal').modal('hide');
        };
        cbcancel = cbcancel || function () {
            $('#identity__bsconfirmModal').modal('hide');
        };
        i18n = i18n || {};

        var modalHtml = $(`
            <div id="identity__bsconfirmModal" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title">${text}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button id="identity__bsconfirmModal_button_cancel" type="button" data-bs-dismiss="modal" class="btn btn-cancel">
                                ${i18n.confirm_cancel || '<i class="ri-close-fill"></i>'}
                            </button>
                            <button id="identity__bsconfirmModal_button_ok" type="button" class="btn btn-danger">
                                ${i18n.confirm_ok || '<i class="ri-check-fill"></i>'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        if ($('body').find('#identity__bsconfirmModal').length == 0) {
            $('body').append(modalHtml);
        } else {
            $('body').find('#identity__bsconfirmModal').remove();
            $('body').append(modalHtml);
        }

        const modal = new bootstrap.Modal(document.getElementById('identity__bsconfirmModal'));
        modal.show();

        const modalElement = document.getElementById('identity__bsconfirmModal');
        modalElement.addEventListener('hidden.bs.modal', function () {
            modal.dispose();
        });
        modalElement.addEventListener('shown.bs.modal', function () {
            $('#identity__bsconfirmModal_button_ok').on('click', cbok);
            $('#identity__bsconfirmModal_button_cancel').on('click', cbcancel);
        });
    }
});

