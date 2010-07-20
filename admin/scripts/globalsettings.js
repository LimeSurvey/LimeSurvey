// $Id$

$(document).ready(function(){
    $("#emailmethod").change(Emailchange);
    Emailchange();

   $("#bounceaccounttype").change(Emailchanges);
   Emailchanges();
});

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

