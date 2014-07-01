/*
* LimeSurvey (tm)
* Copyright (C) 2012 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Validate question object on blur on title element
*/
$(document).on('blur','#frmeditquestion :not(:hidden)[name="title"]',function(){
    validateQuestion($(this));
});
/**
* Validate question object before click on a submit button
*/
$(document).on('click','#frmeditquestion :submit',{validated:false},function(event,data){
    data = data || event.data;
    if(data.validated){
        return true;
    }else{
        validateQuestion($(this));
        return false;
    }
});
/**
* Validate question object before submit : actually only title need to be validated
* This disallow submitting if Question code are not unique (else loose all fields)
*/
function validateQuestion(jqObject){
    if(typeof jqObject=="undefined"){jqObject=$([]);}
    $.post(
        validateUrl,
        { title: $('#frmeditquestion [name="title"]:first').val() },
        function(data){
                if($.isEmptyObject(data))
                {
                    if(hasFormValidation)
                    {
                        $('#frmeditquestion [type!=hidden][name="title"]').filter(":first")[0].setCustomValidity('');// Just title actually, more input needed after ($.each)
                    }
                    $('#frmeditquestion [type!=hidden][name="title"]').removeClass("has-error");
                    $('#frmeditquestion [type!=hidden][name="title"]').next('.errorMessage').remove();
                    if($(jqObject).is(":submit")){
                        $(jqObject).trigger('click', { validated: true });
                    }
                }
                else
                {
                    $.each(data, function(name, aError) {
                        if($(jqObject).is(":submit")){
                            $("#frmeditquestion").closest("#tabs").find(".ui-tabs-anchor:first").click();
                            $('#frmeditquestion [type!=hidden][name="'+name+'"]').focus();// Focus on the first input
                        }
                        $('#frmeditquestion [type!=hidden][name="'+name+'"]').addClass("has-error");
                        if(!$('#frmeditquestion [type!=hidden][name="'+name+'"]').next('.errorMessage').length)// $.each ?
                        {
                            $("<span class='errorMessage text-error' />").insertAfter('#frmeditquestion [type!=hidden][name="'+name+'"]');
                        }
                        $.each(aError,function(i,error){
                            if(hasFormValidation)
                            {
                                $('#frmeditquestion [type!=hidden][name="'+name+'"]').each(function(){
                                    $(this)[0].setCustomValidity(error);
                                });
                            }
                            $('#frmeditquestion [type!=hidden][name="'+name+'"]').next('.errorMessage').text(error);
                        });
                    });
                }
            },
        dataType="json"
    )
}
