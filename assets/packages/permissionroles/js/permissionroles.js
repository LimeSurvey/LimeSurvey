var RoleControl = function () {
    var loaderSpinner = '  <div class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
    loaderSpinner += '    <div id="loader-rolecontrol" class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">';
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
        }
    };

    var runAction = function (el) {
        $('#in_survey_common_action').append('<div class="RoleControl-loading">' + loaderSpinner + '</div>');
        var url = $(el).data('url'),
            action = $(el).data('action'),
            ptid = $(el).data('ptid');
        var form = $('<form></form>');
        form.attr('method', 'post');
        form.attr('action', url);
        form.append('<input type="hidden" name="ptid" value="' + ptid + '" />');
        form.append('<input type="hidden" name="action" value="' + action + '" />');
        form.append('<input type="hidden" name="YII_CSRF_TOKEN" value="' + LS.data.csrfToken + '" />');
        form.appendTo('body');
        form.submit();
    };

    var triggerModalClose = function () {
        $('#RoleControl-action-modal').find('.modal-content').empty();
        $.fn.yiiGridView.update('rolecontrol--identity-gridPanel', {});
        $('#RoleControl-action-modal').modal('hide');
    };

    /**
     *
     * @param modalSize
     */
    var startModalLoader = function (modalSize) {
        $('#RoleControl-action-modal').find('.modal-content').html(loaderHtml);
        let modalDialog = $('#userrole-modal-dialog');
        if(modalSize===''){
            modalDialog.removeClass('modal-lg');
        } else {
            modalDialog.addClass(modalSize);
        }
        $('#RoleControl-action-modal').modal('show');
    };
    var startSubmit = function () {
        $('#submitForm').append(
            '<i class="ri-loader-2-fill remix-pulse RoleControl-spinner"></i>'
        ).prop('disabled', true);
    };
    var stopSubmit = function () {
        $('.RoleControl-spinner').remove();
        $('#submitForm').prop('disabled', false);
    };

    var wireForm = function () {
        $('#RoleControl--modalform').on('submit.ROLECONTROLMODAL', function (e) {
            e.preventDefault();
            startSubmit();
            var data = $('#RoleControl--modalform').serializeArray();
            $.ajax({
                url: $('#RoleControl--modalform').attr('action'),
                data: data,
                method: 'POST',
                dataType: 'json',
                success: function (result) {
                    stopSubmit();
                    if (result.success === true)
                    {
                        $('#RoleControl--modalform').off('submit.ROLECONTROLMODAL');
                        $('#RoleControl-action-modal').find('.modal-content').html(result.html);

                        if (!result.hasOwnProperty('html')){
                            triggerModalClose();
                            window.LS.ajaxAlerts(result.message, 'success', {showCloseButton: true});
                            return;
                        }
                        $('#exitForm').on('click.ROLECONTROLMODAL', function (e) {
                            e.preventDefault();
                            $('#exitForm').off('click.ROLECONTROLMODAL');
                            triggerModalClose();
                        });
                        return;
                    }
                    window.LS.ajaxAlerts(result.errors, 'danger', {inline: '#RoleControl--errors'});
                },
                error: function () {
                    window.LS.ajaxAlerts('An error occured while trying to save, please reload the page Code:1571314170100', 'danger', {showCloseButton: true});
                }
            });
        });

        $('#exitForm').on('click.ROLECONTROLMODAL', function (e) {
            e.preventDefault();
            $('#exitForm').off('click.ROLECONTROLMODAL');
            triggerModalClose();
        });
    };

    var wirePermissions = function () {
        var tableObject = $('#RoleControl--rolepermissions-table');

        $(".general-row-selector").on('click', function () {
            $(this).removeClass('incomplete-selection');
            bChecked = this.checked;
            $(this).closest('tr').find('input').prop('checked', bChecked);
        });

        $('.specific-permission-selector').on('click', function () {
            var thisRow = $(this).closest('tr');
            if (thisRow.find('.specific-settings-block input:checked').size() == thisRow.find('.extended input').size()) {
                thisRow.find('.general-row-selector').prop('checked', true);
                thisRow.find('.general-row-selector').removeClass('incomplete-selection');
            } else if (thisRow.find('.specific-settings-block input:checked').size() == 0) {
                thisRow.find('.general-row-selector').prop('checked', false);
            } else {
                thisRow.find('.general-row-selector').prop('checked', true);
                thisRow.find('.general-row-selector').addClass('incomplete-selection');
            }
        });

        $('#perm_superadmin_read').on(' click', function () {
            tableObject.find('input').prop('checked', this.checked).fadeTo(1, 1);
        })

        $('#RoleControl--permissions-table tr').each(function () {
            if ($(this).find('.specific-permission-selector:checked').size() == $(this).closest('tr').find('.specific-permission-selector').size()) {
                $(this).find('.general-row-selector').prop('checked', true);
                $(this).find('.general-row-selector').removeClass('incomplete-selection');
            } else if ($(this).find('.specific-settings-block input:checked').size() == 0) {
                $(this).find('.general-row-selector').prop('checked', false);
            } else {
                $(this).find('.general-row-selector').prop('checked', true);
                $(this).find('.general-row-selector').addClass('incomplete-selection');
            }
        });

        $('#permission-modal-exitForm').on('click', function(e){
            e.preventDefault();
            triggerModalClose();
        });
    };

    var wireMassPermissions = function () {
        wirePermissions();

        var oCheckedItems = $('#RoleControl--identity-gridPanel').yiiGridView('getChecked', $('.listActions').data('pk'));
        $('#hereBePtIds').html('');
        console.ls.log(oCheckedItems);

        LS.ld.forEach(oCheckedItems, function(item,key) {
            console.ls.log(item,key);
            $('#hereBePtIds').append('<input type="hidden" name="ptids[]" value="'+item+'" />');
        });
    };

    var applyModalHtml = function (html) {
        $('#RoleControl-action-modal').find('.modal-content').html(html);
        wirePermissions();
        wireForm();
    }

    var bindButtons = function () {
        $('.action_usercontrol_button').on('click', function () {
            runAction(this);
        });
        $('input[name="alltemplates"]').on('switchChange.bootstrapSwitch', function (event, state) {
            $('input[id$="_use"]').prop('checked', state).trigger('change');
        });
        $('.RoleControl--action--openmodal').on('click', function () {
            var href = $(this).data('href');
            var modalSize = '';
            if (typeof $(this).data('modalsize') !== 'undefined') {
                modalSize = $(this).data('modalsize');
            }
            startModalLoader(modalSize);
            $.ajax({
                url: href,
                success: function (html) {
                    applyModalHtml(html);
                }
            });

        });
        $('#RoleControl--action-toggleAllRoles').on('click', function(){
            var curVal = $(this).prop('checked');
            $('.RoleControl--selector-roleCheckbox').each(function(){
                $(this).prop('checked', curVal);
            });
        });
        $('#pageSize').on('change', function(){
            $('#RoleControl--identity-gridPanel').yiiGridView('update',{ data:{ pageSize: $(this).val() }});
        });
        $(document).trigger('actions-updated');
    };

    var bindModals = function () {
        $('#RoleControl-action-modal').on('hide.bs.modal', function () {
            $.fn.yiiGridView.update('RoleControl--identity-gridPanel', {});
        });

        $('#massive-actions-modal-batchPermissions-2').on('shown.bs.modal', function () {
            wireMassPermissions();
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
        wirePermissions: wirePermissions,
        wireMassPermissions: wireMassPermissions
    }
};

LS.RoleControl = LS.RoleControl || new RoleControl();
