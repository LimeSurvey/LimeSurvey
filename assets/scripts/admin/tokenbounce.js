/**
 * This javascript show or hide the token bounce parameters in function of the Bounce settings to be used
 * (None, use setting below, use global settings )
 */
var LS = LS || {
    onDocumentReady: {}
};

function updateParameters()
{
if ($('#bounceprocessing input:radio:checked').val()!='L'){
        $("#bounceaccounttype").attr('disabled','disabled');
        $("#bounceaccounthost").attr('disabled','disabled');
        $("#bounceaccountuser").attr('disabled','disabled');
        $("#bounceaccountpass").attr('disabled','disabled');
        $('#bounceaccountencryption label').addClass('disabled');
    }
    else {
        $("#bounceaccounttype").removeAttr('disabled');
        $("#bounceaccounthost").removeAttr('disabled');
        $("#bounceaccountuser").removeAttr('disabled');
        $("#bounceaccountpass").removeAttr('disabled');
        $('#bounceaccountencryption label').removeClass('disabled');
    }
}

$(document).on('ready  pjax:scriptcomplete', function(){
    updateParameters();
    $("input:radio[id^='bounceprocessing']").on('change',function(){
        updateParameters();
    });
});
