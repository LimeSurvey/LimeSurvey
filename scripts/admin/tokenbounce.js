// $Id: tokens.js 8633 2010-04-25 12:57:33Z c_schmitz


$(document).ready(function() {
    $("#bounceprocessing").change(turnoff);
    turnoff();
});



function turnoff(ui,evt) {
    bounce_disabled=($("#bounceprocessing").val()=='N' || $("#bounceprocessing").val()=='G');
    if (bounce_disabled==true) {bounce_disabled='disabled';}
    else {bounce_disabled='';}
    $("#bounceaccounttype").attr('disabled',bounce_disabled);
    $("#bounceaccounthost").attr('disabled',bounce_disabled);
    $("#bounceaccountuser").attr('disabled',bounce_disabled);
    $("#bounceaccountpass").attr('disabled',bounce_disabled);
    $("#bounceencryption").attr('disabled',bounce_disabled);
    $("#bounceaccountencryption").attr('disabled',bounce_disabled);
};
    