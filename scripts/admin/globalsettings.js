// $Id: globalsettings.js 8964 2010-07-20 20:46:47Z anishseth $

$(document).ready(function(){
    $("#emailmethod").change(Emailchange);
    Emailchange();

    $("#bounceaccounttype").change(Emailchanges);
    Emailchanges();
    $('#btnRemove').click(removeLanguages);
    $('#btnAdd').click(addLanguages);
    $("#frmglobalsettings").submit(UpdateRestrictedLanguages);
});


function removeLanguages(ui,evt)
{
   $('#includedLanguages').copyOptions('#excludedLanguages');
   $("#excludedLanguages").sortOptions();
   $("#includedLanguages").removeOption(/./,true);
}

function addLanguages(ui,evt)
{
   $('#excludedLanguages').copyOptions('#includedLanguages');
   $("#includedLanguages").sortOptions();
   $("#excludedLanguages").removeOption(/./,true);
}

function UpdateRestrictedLanguages(){
    aString='';
    if ($("#includedLanguages option").length==0)
    {
        alert (msgAtLeastOneLanguageNeeded);
        return false;
    }
    $("#includedLanguages option").each(function(){
       aString=aString+' '+$(this).val();
    });
    $('#restrictToLanguages').val(aString.trim());
}

function Emailchange(ui,evt)
{
    smtp_enabled=($("#emailmethod").val()=='smtp');
    if (smtp_enabled==true) {smtp_enabled='';}
    else {smtp_enabled='disabled';}
    $("#emailsmtphost").attr('disabled',smtp_enabled);
    $("#emailsmtpuser").attr('disabled',smtp_enabled);
    $("#emailsmtppassword").attr('disabled',smtp_enabled);
    $("#emailsmtpssl").attr('disabled',smtp_enabled);
    $("#emailsmtpdebug").attr('disabled',smtp_enabled);
}

function Emailchanges(ui,evt)
{
    bounce_disabled=($("#bounceaccounttype").val()=='off');
    if (bounce_disabled==true) {bounce_disabled='disabled';}
    else {bounce_disabled='';}
    $("#bounceaccounthost").attr('disabled',bounce_disabled);
    $("#bounceaccountuser").attr('disabled',bounce_disabled);
    $("#bounceaccountpass").attr('disabled',bounce_disabled);
    $("#bounceencryption").attr('disabled',bounce_disabled);
    $("#bounceaccountmailbox").attr('disabled',bounce_disabled);
}

