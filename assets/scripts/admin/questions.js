/*
* LimeSurvey (tm)
* Copyright (C) 2012-2016 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v3 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function(){
    $('#questionTypeContainer').css("overflow","visible");
    $('#collapseOne').on('shown.bs.collapse', function () {
        $('#questionTypeContainer').css("overflow","visible");
    });

    $('#collapseOne').on('hide.bs.collapse', function () {
        $('#questionTypeContainer').css("overflow","hidden");
    });

    if($('.loader-advancedquestionsettings').length){
        updatequestionattributes();
    }

    $('#question_type').change(updatequestionattributes);

    $('#question_type_button  li a').click(function(){
        $(".btn:first-child .buttontext").text($(this).text());
        $('#question_type').val($(this).data('value'));

        updatequestionattributes();
    });
    
});
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
        {
            title: $('#frmeditquestion [name="title"]:first').val(),
            other: $('#frmeditquestion [name="other"]:checked:first').val(),

        },
        function(data){
                // Remove all custom validity
                if(hasFormValidation)
                {
                    $('#frmeditquestion input.has-error').each(function(){
                        if(hasFormValidation)
                        {
                            $(this)[0].setCustomValidity('');
                        }
                        $(this).removeClass("has-error");
                        $(this).next('.errorMessage').remove();
                    });
                }
                // No error : submit
                if($.isEmptyObject(data))
                {
                    if($(jqObject).is(":submit")){
                        $(jqObject).trigger('click', { validated: true });
                    }
                }
                else
                {
                    // Add error information for each input
                    $.each(data, function(name, aError) {
                        if($(jqObject).is(":submit")){
                            $("#frmeditquestion").closest("#tabs").find(".ui-tabs-anchor:first").click();
                            $('#frmeditquestion [type!=hidden][name="'+name+'"]').focus();// Focus on the first input
                        }
                        $('#frmeditquestion [type!=hidden][name="'+name+'"]').addClass("has-error");
                        if(!$('#frmeditquestion [type!=hidden][name="'+name+'"]:last').next('.errorMessage').length)// :last for radio list
                        {
                            $("<span class='errorMessage text-warning' />").insertAfter('#frmeditquestion [type!=hidden][name="'+name+'"]:last');
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
    );
}
function updatequestionattributes()
{
    var type = $('#question_type').val();
    OtherSelection(type);

    $('.loader-advancedquestionsettings').removeClass("hidden");
    $('.panel-advancedquestionsettings').remove();
    var selected_value = qDescToCode[''+$("#question_type_child .selected").text()];
    if (selected_value==undefined) selected_value = $("#question_type").val();

    var postData = {
        'qid':$('#qid').val(),
        'question_type':selected_value,
        'sid':$('input[name=sid]').val()
    };

    $.ajax({
        url: attr_url,
        data : postData, 
        method: 'POST',
        success: function(data) {
            $('.loader-advancedquestionsettings').before(data);
            $('.loader-advancedquestionsettings').addClass("hidden");
            $('label[title]').qtip({
                style: {name: 'cream',
                    tip: true,
                    color:'#111111',
                    border: {
                        width: 1,
                        radius: 5,
                        color: '#EADF95'}
                },
                position: {adjust: {
                        screen: true, scroll:true},
                    corner: {
                        target: 'bottomRight'}
                },
                show: {effect: {length:50}}
            });
            renderBootstrapSwitch();
        }
    });
}
