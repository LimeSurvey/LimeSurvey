/******************
    User custom JS
    ---------------

   Put JS-functions for your template here.
   If possible use a closure, or add them to the general Template Object "Template"
*/
$(window).on('load', function() {
    $('.answer-container').each(function() {
        let NoAnswerIndex = $(this).find('.stars-list.answers-list > .star-rating.star-cancel').index();
        console.log($(this).find('.stars-list.answers-list > .star-rating.star-cancel'));
        console.log(NoAnswerIndex)
        $(this).find('.ls-answers.answers-list > li.noanswer-item').insertBefore($('.ls-answers.answers-list > li').eq(NoAnswerIndex));
    });
});

$(document).on('ready pjax:scriptcomplete', function() {
    if(typeof qID === 'undefined'){qID = -1}
    /**
     * Code included inside this will only run once the page Document Object Model (DOM) is ready for JavaScript code to execute
     * @see https://learn.jquery.com/using-jquery-core/document-ready/
     */
     if(qID !== -1){
         $('[role="region"].top-container .visually-hidden').attr('tabindex', -1);
         $('[role="region"].top-container .visually-hidden').focus();
     }
    if ($("#ls-valid-mesasge-help-container").text().trim() !== "") {
        $(".answer-container ul > li input, .answer-container .bootstrap-buttons-div input").attr("aria-describedby", "ls-valid-mesasge-help-container");
        $(".answer-container ul > li input, .answer-container .bootstrap-buttons-div input").first().focus();
    }
    if ($("#mobile-toggler").is(':visible')) {
        $('#navbar a').last().keydown(function(e) {
            if (e.key == "Tab") {
                if (!e.shiftKey) {
                    $("#mobile-toggler").focus();
                    e.preventDefault();
                }
            }
        });
        $('#mobile-toggler, #navbar a').keydown(function(e) {
            if (e.key == 'Escape') {
                $("#mobile-toggler").focus();
                $('#navbar').collapse('hide');
            }
        });
    }
    $('.answer-container').each(function() {
        const that = this;
        $(this).find('ul.ls-answers.answers-list.radio-list > li').each(function() {
            $(this).attr("role", "none");
            let label = $(this).find('label');
            let input = $(this).find('input');
            label.attr('aria-hidden', true);
            input.attr("aria-label", label.text().trim());
        });
        $(this).find('ul.ls-answers.answers-list.radio-list').find('input').change(function() {
            let answerIndex = $(this).parent().index();
            if ($(this).closest('.answers-list').next().hasClass('ls-emojislider')) {
                var index = answerIndex + 1;
                var answersList = $('#question' + qID + ' .answers-list.radio-list:not(.slidered-list)');
                answersList.find('input[type=radio][value=' + index + ']').trigger('click');
                $("#emoji_slider_container_" + qID).find(".slider-label").find('i').removeClass('emoji-color'); //remove all other color-classes
                if (index == 6) { //if it is the "no Answer" set, add text-danger instead of emoji-color
                    $("#emoji_slider_container_" + qID).find(".slider-label-6").find('i').addClass('text-danger');
                    answersList.find('input[type=radio][value=""]').trigger('click');
                } else {
                    $("#emoji_slider_container_" + qID).find(".slider-label-6").find('i').removeClass('text-danger');
                    $("#emoji_slider_container_" + qID).find(".slider-label-" + index).find('i').addClass('emoji-color');
                }
                var mapToStickToSelection = {
                    1: 31,
                    2: 123,
                    3: 214,
                    4: 304,
                    5: 397,
                    6: 476
                };
                $('#slider_handle_item_' + qID).css('left', mapToStickToSelection[index]);
            } else if ($(this).closest('.answers-list').next().hasClass('stars-list')) {
                var starsHtmlElement = $(this).closest('.answers-list').next();
                var thischoice = answerIndex + 1;
                var answersList = $('#question' + qID + ' .answers-list.radio-list:not(.starred-list)');
                //toggle the em-action on the hidden input
                answersList.find("input[type=radio]").prop('checked', false);
                answersList.find("input[value='" + thischoice + "']").prop('checked', true).trigger('change');
                THIS = starsHtmlElement.children().eq(answerIndex)
                //clean up classes
                $(THIS).siblings('.star-rating').removeClass("star-thisrated").removeClass("star-rated").removeClass("star-rated-on");
                //mark the chosen star
                $(THIS).addClass("star-rated").addClass("star-thisrated").addClass("star-rated-on");
                //iterate through the siblings to mark the stars lower than the current
                $(THIS).siblings('.star-rating').each(function() {
                    if ($(this).data("star") < thischoice) {
                        $(this).addClass("star-rated").addClass("star-rated-on");
                    }
                });
                // if cancel, remove all classes
                if ($(THIS).hasClass('star-cancel')) {
                    $(THIS).siblings('.star-rating').removeClass("star-rated-on").removeClass("star-rated");
                    answersList.find('.noanswer-item').find("input[type=radio]").prop('checked', true).trigger('change');
                }
            }
        });
        $(this).find('ul.ls-answers.answers-list.radio-list').find('input').focus(function() {
            let answerIndex = $(this).parent().index();
            if ($(this).closest('.answers-list').next().hasClass('ls-emojislider')) {
                var index = answerIndex + 1;
                var answersList = $('#question' + qID + ' .answers-list.radio-list:not(.slidered-list)');
                $("#emoji_slider_container_" + qID).find(".outlined-element").removeClass('outlined-element');
                $("#emoji_slider_container_" + qID).find(".slider-label-" + index).addClass('outlined-element');
            } else if ($(this).closest('.answers-list').next().hasClass('stars-list')) {
                var starsHtmlElement = $(this).closest('.answers-list').next();
                THIS = starsHtmlElement.children().eq(answerIndex)
                $(THIS).addClass("outlined-element");
                $(THIS).siblings().removeClass("outlined-element");
            }
        });
        $(this).find('ul.ls-answers.answers-list.radio-list').find('input').blur(function() {
            let answerIndex = $(this).parent().index();
            if ($(this).closest('.answers-list').next().hasClass('ls-emojislider')) {
                var index = answerIndex + 1;
                $("#emoji_slider_container_" + qID).find(".outlined-element").removeClass('outlined-element');
            } else if ($(this).closest('.answers-list').next().hasClass('stars-list')) {
                var starsHtmlElement = $(this).closest('.answers-list').next();
                starsHtmlElement.children().removeClass("outlined-element");
            }
        });
    });

    $('.imageselect-list').each(function() {
        if ($(this).parent().attr('role') == "radio-group") {
            $(this).parent().attr('role', 'radiogroup');
            $(this).attr("role", 'none');
            $(this).find('> li').attr("role", 'none');
        }
    });

    $('#LS_question' + qID + '_Timer').attr("role", "status");
    
    $('.form-control.ls-geoloc-search').each(function(){
    });
    
    $(document.body).append('<div id="liveRegion" role="status" class="visually-hidden"></div>');
    function liveAnnoucement(text){
        $("#liveRegion").html("");
        setTimeout(function(){
            $("#liveRegion").html("<p>"+text+"</p>");
        },1000);
    }
    if($(".selector--inputondemand-addlinebutton").hasClass('selector--inputondemand-addlinebutton')){
        $(".selector--inputondemand-addlinebutton").click(function(){
            liveAnnoucement("line added");
        });
    }
    
    $('.question-text a[target="_blank"]').each(function(){
        $(this).attr("aria-label", $(this).text().trim()+" - will open in a new tab");
    });
    
    $('.text-item.other-text-item.form-check > input').each(function(){
        const ID = $(this).attr('aria-labelledby');
        $(this).parent().attr('id', ID);
    });
    
    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);
        modal.find('.btn-close').attr("aria-label", "Close");
        modal.attr("aria-label", modal.find('.modal-title').text().trim());
        setTimeout(function(){
            modal.find('.modal-title').attr('tabindex', -1);
            modal.find('.modal-title').focus();
            $('#notice').attr('role', "alert");
        },500);
    });
    
    $(document.body).on('keypress', '.upload-div > button', function(e){
        if(e.key == "Enter"){
            const mouseoverEvent = new Event('mouseover');
            document.querySelector('.upload-div>button').dispatchEvent(mouseoverEvent);
            $('[name="uploadfile"]').click();
            e.preventDefault();
        }
    });
    $('.form-control.date-control.date + div.input-group-addon').each(function(){
        $(this).attr({"tabindex": "0", "role":"button", "aria-expanded":"false", "aria-label":$(this).text().trim()});
        $(this).click(function(){
            $(this).attr("aria-expanded", $(this).attr("aria-expanded") == "false");
        });
        $(this).keypress(function(event){
            if(event.key == "Enter"){
                event.preventDefault();
                $(this).click();
            }
        });
    });
    $('.question-item.answer-item .ls-slider-item-row').each(function(){
        const label = $(this).find('label').text().trim();
        const that = this;
        $(this).find('button.btn-slider-reset').attr("aria-label", "Reset "+ label + " Slider");
        $(this).find('button.btn-slider-reset').click(function(){
            setTimeout(function(){
                liveAnnoucement(label+ " Reset to "+ $(that).find('.form-control.answer-item.numeric-item[data-value]').val());
            },500);
        });
    });
    $('.subquestion-list.questions-list.text-array[role]').removeAttr("role");
    $('#limesurvey .completed-wrapper').attr('tabindex', -1);
    $('#limesurvey .completed-wrapper').focus();
    $('#form-load[name="form-load"] label').each(function() {
        const label = $(this).text().replace(/\s+/g,' ').trim();
        $(this).parent().find('input').attr('aria-label', label);
    });
    $("#navbar ul, #navbar ul li").attr("role", "none");
});





