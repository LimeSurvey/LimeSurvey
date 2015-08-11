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
    $("#includedLanguages option").each(function(){
       aString=aString+' '+$(this).val();
    });
    $('#restrictToLanguages').val($.trim(aString));
}

function Emailchange(ui,evt)
{
    smtp_enabled=($("#emailmethod").val()=='smtp');
    if (smtp_enabled==true) {smtp_enabled='';}
    else {smtp_enabled='disabled';}
    $("#emailsmtphost").prop('disabled',smtp_enabled);
    $("#emailsmtpuser").prop('disabled',smtp_enabled);
    $("#emailsmtppassword").prop('disabled',smtp_enabled);
    $("#emailsmtpssl").prop('disabled',smtp_enabled);
    $("#emailsmtpdebug").prop('disabled',smtp_enabled);
}

function Emailchanges(ui,evt)
{
    bounce_disabled=($("#bounceaccounttype").val()=='off');
    if (bounce_disabled==true) {bounce_disabled='disabled';}
    else {bounce_disabled='';}
    $("#bounceaccounthost").prop('disabled',bounce_disabled);
    $("#bounceaccountuser").prop('disabled',bounce_disabled);
    $("#bounceaccountpass").prop('disabled',bounce_disabled);
    $("#bounceencryption").prop('disabled',bounce_disabled);
    $("#bounceaccountmailbox").prop('disabled',bounce_disabled);
}
