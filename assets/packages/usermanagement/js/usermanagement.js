var UserManagement = function () {
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
        }
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
        $('#UserManagement-action-modal').find('.modal-content').empty();
        $.fn.yiiGridView.update('usermanagement--identity-gridPanel', {});
        $('#UserManagement-action-modal').modal('hide');
    };

    var startModalLoader = function (html) {
        $('#UserManagement-action-modal').find('.modal-content').html(loaderHtml);
        $('#UserManagement-action-modal').modal('show');
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

    var wireForm = function () {
        $('#UserManagement--modalform').on('submit.USERMANAGERMODAL', function (e) {
            console.log(e);
            e.preventDefault();
            startSubmit();
            var data = $('#UserManagement--modalform').serializeArray();
            $.ajax({
                url: $('#UserManagement--modalform').attr('action'),
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

    var wireExportDummyUser = function () {
        $('#exportUsers').on('click', function (e) {
            e.preventDefault();
            var users = $('#exportUsers').data('users');
            var csvContent = "data:text/csv;charset=utf-8,";
            csvContent += 'users_name;password' + "\r\n";
            $.each(users, function (i, user) {
                csvContent += user.username + ';' + user.password + "\r\n";
            });
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("class", 'hidden');
            link.setAttribute("download", "addedUsers_" + moment().format('YYMMDDHHmm') + ".csv");
            link.innerHTML = "Click Here to download";
            document.body.appendChild(link); // Required for FF
            link.click();
        })
    };

    var wireTemplatePermissions = function () {
        $('input[data-is-bootstrap-switch]').bootstrapSwitch();
        $('#UserManagement--action-userthemepermissions-select-all').on('click', function(e){
            e.preventDefault();
            $('.UserManagement--themepermissions-themeswitch').prop('checked',true).trigger('change');
        });
        $('#UserManagement--action-userthemepermissions-select-none').on('click', function(e){
            e.preventDefault();
            $('.UserManagement--themepermissions-themeswitch').prop('checked',false).trigger('change');
        });
    };

    var wirePermissions = function (modal) {
        var tableObject = $('#UserManagement--userpermissions-table', modal);

        $(".general-row-selector", modal).on('click', function () {
            $(this).removeClass('incomplete-selection');
            bChecked = this.checked;
            $(this).closest('tr').find('input').prop('checked', bChecked);
        });

        $('.specific-permission-selector', modal).on('click', function () {
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

        $('#all_superadmin', modal).on(' click', function () {
            tableObject.find('input').prop('checked', this.checked).fadeTo(1, 1);
        })

        $('#UserManagement--userpermissions-table tr', modal).each(function () {
            if ($(this).find('.specific-settings-block input:checked').size() == $(this).closest('tr').find('.specific-settings-block input').size()) {
                $(this).find('.general-row-selector').prop('checked', true);
                $(this).find('.general-row-selector').removeClass('incomplete-selection');
            } else if ($(this).find('.specific-settings-block input:checked').size() == 0) {
                $(this).find('.general-row-selector').prop('checked', false);
            } else {
                $(this).find('.general-row-selector').prop('checked', true);
                $(this).find('.general-row-selector').addClass('incomplete-selection');
            }
        });

        $('#permission-modal-exitForm', modal).on('click', function(e){
            e.preventDefault();
            triggerModalClose();
        });
    };

    var wireMassPermissions = function () {
        wirePermissions("#massive-actions-modal-usermanagement--identity-gridPanel-batchPermissions-2");

        var oCheckedItems = $('#usermanagement--identity-gridPanel').yiiGridView('getChecked', $('.listActions').data('pk'));
        $('#hereBeUserIds').html('');
        console.ls.log(oCheckedItems);
        
        var userIds = [];
        $('.usermanagement--selector-userCheckbox').each(function(){
            if($(this).prop('checked')){
                userIds.push($(this).attr('value'));
            }
        });
        console.ls.log(userIds);

        LS.ld.forEach(oCheckedItems, function(item,key) {
            console.ls.log(item,key);
            $('#hereBeUserIds').append('<input type="hidden" name="userids[]" value="'+item+'" />');
        });
    };

    var wirePasswordOptions = function () {
        $('#utility_change_password').on('change', function () {
            if ($(this).prop('checked')) {
                $('#utility_change_password_container').removeClass('hidden');
                $('#User_Form_password').prop('disabled', false);
                $('#password_repeat').prop('disabled', false);
            } else {
                $('#utility_change_password_container').addClass('hidden');
                $('#User_Form_password').prop('disabled', true);
                $('#password_repeat').prop('disabled', true);
            }
        });
        $('#utility_set_password').find('input[type=radio]').on('change', function () {
            console.log('#utility_set_password changed');
            if ($(this).attr('value') == '1') {
                $('#utility_change_password_container').removeClass('hidden');
                $('#User_Form_password').prop('disabled', false);
                $('#password_repeat').prop('disabled', false);
            } else {
                $('#utility_change_password_container').addClass('hidden');
                $('#User_Form_password').prop('disabled', true);
                $('#password_repeat').prop('disabled', true);
            }
        });
    };

    var wireRoleSet = function () {
        $('#UserManagement--modalform').find('select').each(
            function(i,item) {
                $(item).select2();
            }
        );
    }

    var applyModalHtml = function (html) {
        var modal = '#UserManagement-action-modal';
        $(modal).find('.modal-content').html(html);
        wirePasswordOptions();
        wirePermissions(modal);
        wireTemplatePermissions();
        wireRoleSet();
        wireForm();
    }


    var bindButtons = function () {
        $('.action_usercontrol_button').on('click', function () {
            runAction(this);
        });
        $('#usermanagement--action-toggleAllUsers').on('change', function () {
            var toggled = $(this).prop('checked');
            $('.usermanagement--selector-userCheckbox').each(function() {
                $(this).prop('checked', toggled);
            })
        });
        $('input[name="alltemplates"]').on('switchChange.bootstrapSwitch', function (event, state) {
            $('input[id$="_use"]').prop('checked', state).trigger('change');
        });
        $('.UserManagement--action--openmodal').on('click', function () {
            var href = $(this).data('href');
            startModalLoader();
            $.ajax({
                url: href,
                success: function (html) {
                    applyModalHtml(html);
                }
            });

        });
        bindListItemclick();
    };

    var bindModals = function () {
        $('#UserManagement-action-modal').on('hide.bs.modal', function () {
            $.fn.yiiGridView.update('usermanagement--identity-gridPanel', {});
        });

        $('#massive-actions-modal-usermanagement--identity-gridPanel-batchPermissions-2').on('shown.bs.modal', function () {
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
    };
};

LS.UserManagement = LS.UserManagement || new UserManagement();
