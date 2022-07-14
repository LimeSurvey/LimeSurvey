var FailedEmail = function () {
    var loaderSpinner = '  <div class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
    loaderSpinner += '    <div id="loader-usermanagement" class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">';
    loaderSpinner += '      <div class="ls-flex align-content-center align-items-center">';
    loaderSpinner += '        <div class="loader-adminpanel text-center" :class="extraClass">';
    loaderSpinner += '            <div class="contain-pulse animate-pulse">';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '            </div>';
    loaderSpinner += '          </div>';
    loaderSpinner += '        </div>';
    loaderSpinner += '      </div>';
    loaderSpinner += '    </div>';

    var loaderHtml = '<div class="modal-body">';
    loaderHtml += loaderSpinner;
    loaderHtml += '  </div>';
    loaderHtml += '</div>';

    var triggerRunAction = function (el) {
        return function () {
            runAction(el);
        };
    };

    var runAction = function (el) {
        $('#in_survey_common_action').append('<div class="UserManagement-loading">' + loaderSpinner + '</div>');
        var url = $(el).data('url'),
            action = $(el).data('action'),
            user = $(el).data('user'),
            userid = $(el).data('userid');
        var form = $('<form></form>');
        form.attr('method', 'post');
        form.attr('action', url);
        form.append('<input type="hidden" name="userid" value="' + userid + '" />');
        form.append('<input type="hidden" name="action" value="' + action + '" />');
        form.append('<input type="hidden" name="user" value="' + user + '" />');
        form.append('<input type="hidden" name="YII_CSRF_TOKEN" value="' + LS.data.csrfToken + '" />');
        form.appendTo('body');
        form.submit();
    };

    var triggerModalClose = function () {
        let failedActionModal = $('#failedemail-action-modal');
        failedActionModal.find('.modal-content').empty();
        $.fn.yiiGridView.update('failedemail-grid', {});
        failedActionModal.modal('hide');
    };

    /**
     *
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
            '<i class="fa fa-spinner fa-pulse UserManagement-spinner"></i>'
        ).prop('disabled', true);
    };
    var stopSubmit = function () {
        $('.UserManagement-spinner').remove();
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
                    if (result.success === true)
                    {
                        $('#UserManagement--modalform').off('submit.USERMANAGERMODAL');
                        $('#UserManagement-action-modal').find('.modal-content').html(result.html);
                        wireExportDummyUser();
                        if (!result.hasOwnProperty('html')) {
                            triggerModalClose();
                            window.LS.notifyFader(result.message, 'well-lg text-center ' + (result.success ? 'bg-primary' : 'bg-danger'));
                            return;
                        }
                        $('#exitForm').on('click.USERMANAGERMODAL', function (e) {
                            e.preventDefault();
                            $('#exitForm').off('click.USERMANAGERMODAL');
                            triggerModalClose();
                        });
                        return;
                    }
                    $('#UserManagement--errors').html(
                        "<div class='alert alert-danger'>" + result.errors + "</div>"
                    ).removeClass('hidden');
                },
                error: function () {
                    alert('An error occured while trying to save, please reload the page Code:1571926261195');
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
    };


    var bindButtons = function () {
        $('.action_usercontrol_button').on('click', function () {
            runAction(this);
        });
        $('#usermanagement--action-toggleAllUsers').on('change', function () {
            var toggled = $(this).prop('checked');
            $('.usermanagement--selector-userCheckbox').each(function () {
                $(this).prop('checked', toggled);
            })
        });
        $('.failedemail-action-modal-open').on('click', function () {
            let href = $(this).data('href');
            let modalSize = $(this).data('modalsize');
            let contentFile = $(this).data('contentfile');
            openModal(href, contentFile, modalSize);
        });
    };

    var bindModals = function () {
        $('#failedemail-action-modal').on('hide.bs.modal', function () {
            $.fn.yiiGridView.update('usermanagement--identity-gridPanel', {});
        });
    };

    /**
     *
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
            error: function (xhr,status,error) {
                var err = JSON.parse(xhr.responseText);
            }
        });
    };

    $(document).on('ready  pjax:scriptcomplete', function () {
        bindButtons();
        bindModals();
    });

    return {
        bindButtons: bindButtons,
        bindModals: bindModals,
        triggerRunAction: triggerRunAction,
        submitForm: submitForm
    };
};

LS.FailedEmail = LS.FailedEmail || new FailedEmail();
