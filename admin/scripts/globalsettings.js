// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
    $("#emailmethod").change(Emailchange);
    Emailchange();
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