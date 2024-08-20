var FailedEmail = function () {
    var loaderSpinner = '   <div class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
    loaderSpinner += '          <div class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">';
    loaderSpinner += '              <div class="ls-flex align-content-center align-items-center">';
    loaderSpinner += '                  <div class="loader-adminpanel text-center">';
    loaderSpinner += '                      <div class="contain-pulse animate-pulse">';
    loaderSpinner += '                          <div class="square"></div>';
    loaderSpinner += '                          <div class="square"></div>';
    loaderSpinner += '                          <div class="square"></div>';
    loaderSpinner += '                          <div class="square"></div>';
    loaderSpinner += '                      </div>';
    loaderSpinner += '                  </div>';
    loaderSpinner += '              </div>';
    loaderSpinner += '          </div>';
    loaderSpinner += '      </div>';

    var loaderHtml = '  <div class="modal-body">';
    loaderHtml +=           loaderSpinner;
    loaderHtml += '     </div>';

    var triggerModalClose = function () {
        let failedActionModal = $('#failedemail-action-modal');
        failedActionModal.find('.modal-content').empty();
        $.fn.yiiGridView.update('failedemail-grid', {});
        failedActionModal.modal('hide');
    };

    /**
     * @param {string} modalSize empty string means ---> a default size (modal-dialog) 600px is taken
     *                           otherwise it could be 'modal-lg' or 'modal-sm' defining the size of
     *                           modal view
     */
    var startModalLoader = function (modalSize) {
        let modal = $('#failedemail-action-modal');
        let modalDialog = $('#failedemail-action-modal--dialog');
        if (modalSize === '') {
            modalDialog.removeClass('modal-lg');
            modalDialog.removeClass('modal-sm');
        } else {
            modalDialog.addClass(modalSize);
        }
        modal.find('.modal-content').html(loaderHtml);
        modal.modal('show');
    };

    var startSubmit = function () {
        $('#submitForm').append(
            '<i class="ri-loader-2-fill remix-pulse failedemail-action-modal--spinner"></i>'
        ).prop('disabled', true);
    };
    var stopSubmit = function () {
        $('.failedemail-action-modal--spinner').remove();
        $('#submitForm').prop('disabled', false);
    };

    var submitForm = function () {
        let modalForm = $('#failedemail-action-modal--form');
        modalForm.on('submit', function (e) {
            console.log(e);
            e.preventDefault();
            startSubmit();
            var data = modalForm.serializeArray();
            $.ajax({
                url: modalForm.attr('action'),
                data: data,
                method: 'POST',
                dataType: 'json',
                success: function (result) {
                    stopSubmit();
                    if (result.success === true) {
                        $('#failedemail-action-modal--form').off('submit');
                        $('#failedemail-action-modal').find('.modal-content').html(result.html);
                        if (!result.hasOwnProperty('html')) {
                            triggerModalClose();
                            window.LS.notifyFader(result.message, 'well-lg text-center ' + (result.success ? '' : 'bg-danger'));
                            return;
                        }
                        $('#exitForm').on('click', function (e) {
                            e.preventDefault();
                            $('#exitForm').off('click');
                            triggerModalClose();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    alert('An error occured while trying to save, please reload the page Code:1658139259132\n' +
                        'status: ' + status + '\n' +
                        'error: ' + error + '\n' +
                        'message: ' + xhr.responseText);
                }
            });
        });

        $('#exitForm').on('click.AUMMODAL', function (e) {
            e.preventDefault();
            $('#exitForm').off('click.AUMMODAL');
            triggerModalClose();
        });
    };

    var applyModalHtml = function (html) {
        $('#failedemail-action-modal').find('.modal-content').html(html);
        submitForm();
    };

    /**
     * @param href
     * @param contentFile The modal partial to be rendered "/partials/modal"
     * @param {string} modalSize empty string means ---> a default size (modal-dialog) 600px is taken
     *                           otherwise it could be 'modal-lg' or 'modal-sm' defining the size of
     *                           modal view
     */
    var openModal = function (href, contentFile, modalSize = '') {
        startModalLoader(modalSize);
        let data = {contentFile: contentFile};
        $.ajax({
            url: href,
            data: data,
            method: 'POST',
            success: function (html) {
                applyModalHtml(html);
            },
            error: function (xhr, status, error) {
                console.log(JSON.parse(xhr.responseText));
                triggerModalClose();
            }
        });
    };

    var bindButtons = function () {
        $('.failedemail-action-modal-open').on('click', function () {
            let href = $(this).data('href');
            let modalSize = $(this).data('modalsize');
            let contentFile = $(this).data('contentfile');
            openModal(href, contentFile, modalSize);
        });
        $(document).on('change', '#pageSize', function () {
            $.fn.yiiGridView.update('failedemail-grid', {
                data: {
                    pageSize: $(this).val()
                }
            });
        });
    };

    $(document).on('ready  pjax:scriptcomplete', function () {
        bindButtons();
        submitForm();
    });

    return {
        bindButtons: bindButtons,
        submitForm: submitForm
    };
};

LS.FailedEmail = LS.FailedEmail || new FailedEmail();
