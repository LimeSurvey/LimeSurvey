/**
 * This javascript show or hide the token bounce parameters in function of the Bounce settings to be used
 * (None, use setting below, use global settings )
 */
function hideParameters()
{
    $("#bounceaccounttype").attr('disabled','disabled');
    $("#bounceaccounthost").attr('disabled','disabled');
    $("#bounceaccountuser").attr('disabled','disabled');
    $("#bounceaccountpass").attr('disabled','disabled');
    $("#bounceaccountencryption").attr('disabled','disabled');

    $('#bounceparams').hide( "fade" );
}

function showParameters()
{
    $('#bounceparams').show( "fade" );
    $("#bounceaccounttype").removeAttr('disabled');
    $("#bounceaccounthost").removeAttr('disabled');
    $("#bounceaccountuser").removeAttr('disabled');
    $("#bounceaccountpass").removeAttr('disabled');
    $("#bounceaccountencryption").removeAttr('disabled');
}


$(document).ready(function(){
    if ( $('#bounceprocessing').val() !="L" ){
        hideParameters();
    }


    $( "#bounceprocessing" ).change(function() {
        if (this.value !="L" ){
            hideParameters();
        }
        else
        {
            showParameters();
        }
    });
});
