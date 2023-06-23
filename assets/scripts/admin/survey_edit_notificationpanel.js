/**
 * This javascript show or hide the googleanalytics parameters in function of the Notification settings to be used
 * (None, use setting below, use global settings )
 */
var LS = LS || {
    onDocumentReady: {}
};


function updateParameters()
{
if ($('#googleanalyticsapikeysetting input:radio:checked').val()=='Y'){
        $("#googleanalyticsstyle").find('label').removeClass('disabled');
        $("#googleanalyticsstyle").closest('div.ex-form-group').slideDown(400);

        $("#googleanalyticsapikey").prop('disabled',false);
        $("#googleanalyticsapikey").closest('div.ex-form-group').slideDown(400);
        if($("#googleanalyticsapikey").val() == "9999useGlobal9999"){
            $("#googleanalyticsapikey").val("");
        }
    }
    else if($('#googleanalyticsapikeysetting input:radio:checked').val()=='N') 
    {
        $("#googleanalyticsstyle").val(0);
        $("#googleanalyticsstyle").find('label').addClass('disabled');
        $("#googleanalyticsstyle").closest('div.ex-form-group').slideUp(400);

        $("#googleanalyticsapikey").prop('disabled',true);
        $("#googleanalyticsapikey").closest('div.ex-form-group').slideUp(400);
    }
    else if($('#googleanalyticsapikeysetting input:radio:checked').val()=='G') 
    {
        $("#googleanalyticsstyle").find('label').removeClass('disabled');
        $("#googleanalyticsstyle").closest('div.ex-form-group').slideDown(400);

        $("#googleanalyticsapikey").prop('disabled',true);
        $("#googleanalyticsapikey").closest('div.ex-form-group').slideUp(400);
    }
}

$(document).on('ready  pjax:scriptcomplete', function(){
    updateParameters();
    $("#googleanalyticsapikeysetting").on('change', 'input', function(){
        updateParameters();
    });
});
