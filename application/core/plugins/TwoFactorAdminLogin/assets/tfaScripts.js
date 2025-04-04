// Creating a limesurvey option activated loggin facility
var TFALOG = new ConsoleShim('TFA', !window.debugState.backend);

// Spinner to indicate loading on ajax requests
var loaderSpinner = '<div class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
loaderSpinner  +='<i class="ri-settings-5-fill remix-spin" style="font-size: 128px;color:rgba(50, 134, 55, 0.5);"></i>';
loaderSpinner  +='</div>';
var loaderHtml = '<div class="modal-body">';
loaderHtml += loaderSpinner;
loaderHtml +='</div>';
loaderHtml +='</div>';

/**
 * Meta-function to wire a bootstrap confirm with a custom callback and a LS.notifyFader
 * 
 * @param function additionalCB 
 */
var confirmButtonAction = function(additionalCB) {
    return function(e){
        e.preventDefault();
        var $self = $(this);
        $.fn.bsconfirm($self.data('confirmtext'), $self.data('buttons'), function(){
            $.ajax({
                url: $self.data('href'),
                data: $.merge({uid: $self.data('uid')}, LS.data.csrfTokenData),
                method: 'post',
                success: function(resolve){
                    LS.ajaxAlerts(resolve.message, 'success', {showCloseButton: true});
                    $('#identity__bsconfirmModal').modal('hide');
                    additionalCB();
                },
                error: function(error) {
                    console.error(error);
                    LS.ajaxAlerts($self.data('errortext'), 'danger', {showCloseButton: true});
                    $('#identity__bsconfirmModal').modal('hide');
                    additionalCB();
                }
            });
        });
    };
};

/**
 * Defines functions for the TFA Administrative view
 */
var TFAUserManagementClass = function(){

    return {
        bind: function(){
            var gridbuttonaction = confirmButtonAction(function(){$.fn.yiiGridView.update('tfa-usermanagement-gridPanel');});
            $(".TFA--management--action-refreshToken").on('click', gridbuttonaction);          
            $(".TFA--management--action-deleteToken").on('click', gridbuttonaction);

            TFALOG.log('TFAUserManagementClass bind called');
        }
    }
};

/**
 * Defines functionality for the TFA user view
 */
var TFAUserSettingsClass = function(){
    
    var modalId = '#TFA--actionmodal';
    var formId = '#TFA--modalform';
    var modalCloseTimeout = null;


    var triggerModalClose = function(){
        $(modalId).find('.modal-content').empty();
        $(modalId).modal('hide');
        modalCloseTimeout = null;
    };

    var startModalLoader = function(html) {
        $(modalId).find('.modal-content').html(loaderHtml);
        $(modalId).modal('show');
    };

    var startSubmit = function(){
        $(formId).before(
            '<div class="col-12 text-center"><i class="ri-loader-2-fill remix-pulse remix-4x TFA--usereditspinner"></i></div>'
        ).find('button').prop('disabled',true);
    };
    var stopSubmit = function(){
        $('.TFA--usereditspinner').remove();
        $(formId).find('button').removeAttr('disabled');
    };

    var applyModalHtml = function(html) {
        $(modalId).find('.modal-content').html(html);
        wireCreateForm();
    }

    var wireCreateForm = function(){
        var onSubmit = function(e, self) {
            e.preventDefault();
            const authType = $('#TFAUserKey_authType').val();
            if (
                (authType == 'totp' && $('#confirmationKey').val() == '')
                || (authType == 'yubi' && $('#yubikeyOtp').val() == '')
            ) {
                return;
            }
            startSubmit();
            var formData = $(formId).serializeArray();
            $.ajax({
                url: $(formId).attr('action'),
                data: formData,
                method: 'POST',
                success: function(data){
                    stopSubmit();
                    if(data.success) {
                        $('#TFA--actionmodal').modal('hide');
                        $(formId).parent().html(data.message);
                        modalCloseTimeout = setTimeout(triggerModalClose, 2000);
                        if(data.data.reload != undefined) {
                            setTimeout(function(){window.location.reload();}, 1500);
                        }
                        LS.ajaxAlerts(data.message, 'success', {showCloseButton: true});
                        return;
                    }
                    $(formId).find('.errorContainer').html(data.message);
                }
            });
        };

        $(formId).on('submit',function(e) {onSubmit(e,this);});
        $('#TFA--submitform').on('click',function(e) {
            e.preventDefault();
            onSubmit(e,this);
        });

        $('#TFA--cancelform').on('click',function(e) {
            e.preventDefault();
            triggerModalClose();
        });

        $('#TFAUserKey_authType').on('change', function(){
            if($(this).val() == 'totp') {
                $('#yubiSection').hide();
                $('#totpSection').show();
                $('#confirmationKey').prop('required', true);
                $('#yubikeyOtp').prop('required', false);
            } else {
                $('#totpSection').hide();
                $('#yubiSection').show();
                $('#confirmationKey').prop('required', false);
                $('#yubikeyOtp').prop('required', true);
            }
        });

        $('#TFAUserKey_authType').trigger('change');
    };

    var bsButtonAction = confirmButtonAction(function(){
        setTimeout(function(){window.location.reload();}, 1750);
    });

    var bindButtons = function (){
        $('.TFA--actionopenmodal').on('click', function(){
            var href = $(this).data('href');
            startModalLoader();
            $.ajax({
                url: href,
                success: function(html){
                    applyModalHtml(html);
                }
            });

        });
        $('.TFA--actionconfirm').on('click', bsButtonAction);
    };

    var bindModal = function(){
        $(modalId).on('hidden.bs.modal', function(){
            if(modalCloseTimeout != null){
                clearTimeout(modalCloseTimeout);
                modalCloseTimeout = null;
            }
        })
    }

    var unlinkHrefs = function(){
        $('a').not('.TFA--excludefromlock').on('click', function(e){ e.preventDefault(); return false;});
    }

    return {
        bind: function(){
            TFALOG.log('TFAUserSettingsClass bind called');
            bindButtons();
            bindModal();
        },
        restrictAccess: function(){
            unlinkHrefs();
        }
    }
};
