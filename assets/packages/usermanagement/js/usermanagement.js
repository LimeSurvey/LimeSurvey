
var UserManagement = function(){
    var loaderSpinner = '<div class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
    loaderSpinner  +='<i class="fa fa-gear fa-spin" style="font-size: 128px;color:rgba(50, 134, 55, 0.5);"></i>';
    loaderSpinner  +='</div>';

    var loaderHtml = '<div class="modal-body">';
    loaderHtml += loaderSpinner;
    loaderHtml +='</div>';
    loaderHtml +='</div>';

    var triggerRunAction = function (el){
        return function(){
            runAction(el);
        }
    };
    
    var runAction = function (el){
        $('body').append('<div class="UserManagement-loading">'+loaderSpinner+'</div>');
        var url = $(el).data('url'),
                action = $(el).data('action'),
                user = $(el).data('user'),
                userid = $(el).data('userid');
            var form = $('<form></form>');
            form.attr('method','post');
            form.attr('action',url);
            form.append('<input type="hidden" name="userid" value="'+userid+'" />');
            form.append('<input type="hidden" name="action" value="'+action+'" />');
            form.append('<input type="hidden" name="user" value="'+user+'" />');
            form.append('<input type="hidden" name="YII_CSRF_TOKEN" value="'+LS.data.csrfToken+'" />');
            form.appendTo('body');
            form.submit();
    };
    
    var triggerModalClose = function(){
        $('#UserManagement-action-modal').find('.modal-content').empty();
        $.fn.yiiGridView.update('advancedUserPanel--identity-gridPanel',{});
        $('#UserManagement-action-modal').modal('hide');
    };

    var startModalLoader = function(html) {
        $('#UserManagement-action-modal').find('.modal-content').html(loaderHtml);
        $('#UserManagement-action-modal').modal('show');
    };
    var startSubmit = function(){
        $('#submitForm').append(
            '<i class="fa fa-spinner fa-pulse UserManagement-spinner"></i>'
        ).prop('disabled',true);
    };
    var stopSubmit = function(){
        $('.UserManagement-spinner').remove();
        $('#submitForm').prop('disabled',false);
    };

    var wireForm = function(){
        $('#UserManagement--modalform').on('submit.SMKMODAL', function(e){
            console.log(e);
            e.preventDefault();
            startSubmit();
            var data = $('#UserManagement--modalform').serializeArray();
            $.ajax({
                url: $('#UserManagement--modalform').attr('action'),
                data: data,
                method: 'POST',
                dataType: 'json',
                success: function(result){
                    stopSubmit();
                    if(result.success == true){
                        $('#UserManagement--modalform').off('submit.SMKMODAL');
                        $('#UserManagement-action-modal').find('.modal-content').html(result.html);
                        wireExportDummyUser();
                        $('#exitForm').on('click.SMKMODAL', function(e){
                            e.preventDefault();
                            $('#exitForm').off('click.SMKMODAL');
                            triggerModalClose();
                        });
                        return;
                    }
                    $('#UserManagement--errors').append(
                        "<div class='alert alert-danger'>"+result.error+"</div>"
                    ).removeClass('hidden');
                } 
            })
        });

        $('#exitForm').on('click.AUMMODAL', function(e){
            e.preventDefault();
            $('#exitForm').off('click.AUMMODAL');
            triggerModalClose();
        });
    };

    var wireExportDummyUser = function(){
        $('#exportUsers').on('click', function(e){
            e.preventDefault();
            var users = $('#exportUsers').data('users');
            var csvContent = "data:text/csv;charset=utf-8,";
            csvContent+='users_name;password'+ "\r\n";
            $.each(users, function(i,user){
                csvContent += user.username + ';'+ user.password + "\r\n";
            });
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("class", 'hidden');
            link.setAttribute("download", "addedUsers_"+moment().format('YYMMDDHHmm')+".csv");
            link.innerHTML= "Click Here to download";
            document.body.appendChild(link); // Required for FF
            link.click();
        }) 
    };

    var wirePermissions = function(){
        $('#smk--selector--permissionclass').on('change', function(){
            if($(this).val() == 'classmanager') {
                $('#smk--selector--surveypermission').css('display','block');
                $("#smk--selector--entity-ids").select2();
            } else {
                $('#smk--selector--surveypermission').css('display','none');
            }
        });
    };

    var wireMassPermissions = function(){
        $('#smk--selector--permissionclass-mass').on('change', function(){
            if($(this).val() == 'classmanager') {
                $('#smk--selector--surveypermission-mass').css('display','block');
                $("#smk--selector--entity-ids-mass").select2();
            } else {
                $('#smk--selector--surveypermission-mass').css('display','none');
            }
        });
    };
    
    var wirePasswordChange = function(){
        $('#utility_change_password').on('change', function(){
            if($(this).prop('checked')) {
                $('#utility_change_password_container').removeClass('hidden');
                $('#User_Form_password').prop('disabled', false);
                $('#password_repeat').prop('disabled', false);
            } else {
                $('#utility_change_password_container').addClass('hidden');
                $('#User_Form_password').prop('disabled', true);
                $('#password_repeat').prop('disabled', true);
            }
            

        })
    };

    var applyModalHtml = function(html) {
        $('#UserManagement-action-modal').find('.modal-content').html(html);
        wireForm();
        wirePasswordChange();
        wirePermissions();
    }


    var bindButtons = function (){
        $('.action_usercontrol_button').on('click', function(){
            runAction(this);
        });
        $('input[name="alltemplates"]').on('switchChange.bootstrapSwitch', function(event, state) {
            $('input[id$="_use"]').prop('checked',state).trigger('change');
        });
        $('.UserManagement--action--openmodal').on('click', function(){
            var href = $(this).data('href');
            startModalLoader();
            $.ajax({
                url: href,
                success: function(html){
                    applyModalHtml(html);
                }
            });

        });
        bindListItemclick();
    };

    var bindModals = function(){
        $('#UserManagement-action-modal').on('hide.bs.modal', function(){
            $.fn.yiiGridView.update('advancedUserPanel--identity-gridPanel',{});
        });

        $('#massive-actions-modal-batchPermissions-1').on('shown.bs.modal', function(){
            wireMassPermissions();
        });
    };

    $(document).on('ready  pjax:scriptcomplete', function(){
        bindButtons();
        bindModals();
    });

    return {
        bindButtons : bindButtons,
        bindModals : bindModals,
        triggerRunAction: triggerRunAction,
        wirePermissions: wirePermissions
    }
};

LS.UserManagement = LS.UserManagement || new UserManagement();








