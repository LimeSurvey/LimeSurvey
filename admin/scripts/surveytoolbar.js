// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $
// based on TTabs from http://interface.eyecon.ro/

$(document).ready(function(){
    $('.btnsurveybar').click(function(){
        $('.btnsurveybar').attr('disabled','disabled');
        $('#basicsurveybar').css('white-space','nowrap')
        $('#advancedsurveybar').css('white-space','nowrap')
        $('.btnsurveybar').toggle();
        $('#basicsurveybar').animate({width: 'toggle'}, {duration:1500, easing:'easeOutExpo'});
        $('#advancedsurveybar').animate({ width: 'toggle'}, 1500, 'easeOutExpo', function()
        {
            $('#basicsurveybar').css('white-space','normal')
            $('#advancedsurveybar').css('white-space','normal')
            $('.btnsurveybar').attr('disabled',false);
        });
        $.cookie('surveybarmode',$('#advancedsurveybar').css('width'));
        
               
    });

    if  ($.cookie('surveybarmode')!='1px')
    {
        $('#surveyhandleright').hide();  
        $('#advancedsurveybar').hide();  
        $('#advancedsurveybar').attr('width','0%');  
        $('#basicsurveybar').show();  
    }
    else
    {
        $('#surveyhandleleft').hide();  
        $('#advancedsurveybar').show();  
        $('#basicsurveybar').hide();  
        $('#basicsurveybar').attr('width','0%');  
    }

});
