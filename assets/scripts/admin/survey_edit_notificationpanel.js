/**
 * This javascript show or hide the googleanalytics parameters in function of the Notification settings to be used
 * (None, use setting below, use global settings )
 */
function updateParameters()
{
if ($('#googleanalyticsapikeysetting input:radio:checked').val()=='Y'){
        $("#googleanalyticsstyle").find('label').removeClass('disabled');
        $("#googleanalyticsstyle").closest('div.form-group').slideDown(400);

        $("#googleanalyticsapikey").removeAttr('disabled');
        $("#googleanalyticsapikey").closest('div.form-group').slideDown(400);
        if($("#googleanalyticsapikey").val() == "9999useGlobal9999"){
            $("#googleanalyticsapikey").val("");
        }
    }
    else if($('#googleanalyticsapikeysetting input:radio:checked').val()=='N') 
    {
        $("#googleanalyticsstyle").val(0);
        $("#googleanalyticsstyle").find('label').addClass('disabled');
        $("#googleanalyticsstyle").closest('div.form-group').slideUp(400);

        $("#googleanalyticsapikey").attr('disabled','disabled');
        $("#googleanalyticsapikey").closest('div.form-group').slideUp(400);
    }
    else 
    {
        $("#googleanalyticsstyle").find('label').removeClass('disabled');
        $("#googleanalyticsstyle").closest('div.form-group').slideDown(400);

        $("#googleanalyticsapikey").attr('disabled','disabled');
        $("#googleanalyticsapikey").closest('div.form-group').slideUp(400);
    }
}

$(document).ready(function(){
    updateParameters();
    $("input:radio[id^='googleanalyticsapikeysetting']").on('change',function(){
        updateParameters();
    });
});