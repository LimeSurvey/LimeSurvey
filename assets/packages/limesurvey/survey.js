/**
 * @file Javascript core function for public survey
 * @description loaded before template javascript : allow template to use own function (if function is called after template.js)
 * @copyright LimeSurvey <http://www.limesurvey.org/>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/**
 * Always set an empty LSvar
 */
var LSvar = LSvar || { };

/**
 * Action to do when relevance is set to on or off
 */
function triggerEmRelevance(){
    triggerEmRelevanceQuestion();
    triggerEmRelevanceGroup();
    triggerEmRelevanceSubQuestion();
}
/* On question */
function triggerEmRelevanceQuestion(){
    /* Action on this question */
    $("[id^='question']").on('relevance:on',function(event,data) {
        /* @todo : attach only to this. Use http://stackoverflow.com/a/6411507/2239406 solution for now. 
        Don't want to stop propagation. */
        if(event.target != this) return; 
        $(this).removeClass("ls-irrelevant ls-hidden");
    });
    $("[id^='question']").on('relevance:off',function(event,data) {
        if(event.target != this) return;
        $(this).addClass("ls-irrelevant ls-hidden");
    });
    /* In all in one mode : need updating group too */
    $(".allinone [id^='group-']:not(.ls-irrelevant) [id^='question']").on('relevance:on',function(event,data) {
        if(event.target != this) return;
        $(this).closest("[id^='group-']").removeClass("ls-hidden");
    });
    $(".allinone [id^='group-']:not(.ls-irrelevant) [id^='question']").on('relevance:off',function(event,data) {
        if(event.target != this) return;
        if($(this).closest("[id^='group-']").find("[id^='question']").length==$(this).closest("[id^='group-']").find("[id^='question'].ls-hidden").length){
            $(this).closest("[id^='group-']").addClass("ls-hidden");
        }
    });
}
/* On Group */
function triggerEmRelevanceGroup(){
    $("[id^='group-']").on('relevance:on',function(event,data) {
        if(event.target != this) return;
        $(this).removeClass("ls-irrelevant ls-hidden");
    });
    $("[id^='group-']").on('relevance:off',function(event,data) {
        if(event.target != this) return;
        $(this).addClass("ls-irrelevant ls-hidden");
    });
}
/* On sub-question and answers-list */
function triggerEmRelevanceSubQuestion(){
    $("[id^='question']").on('relevance:on',"[id^='javatbd']",function(event,data) {
        if(event.target != this) return; // not needed now, but after (2016-11-07)
        data = $.extend({style:'hidden'}, data);
        $(this).removeClass("ls-irrelevant ls-"+data.style);
        if(data.style=='disabled'){
            $(event.target).find('input').each(function(itrt, item ){
                $(item).prop("disabled", false );
            });
        }
        if(data.style=='hidden'){
            updateLineClass($(this));
            updateRepeatHeading($(this).closest(".ls-answers"));
        }
    });
    $("[id^='question']").on('relevance:off',"[id^='javatbd']",function(event,data) {
        if(event.target != this) return; // not needed now, but after (2016-11-07)
        data = $.extend({style:'hidden'}, data);
        $(this).addClass("ls-irrelevant ls-"+data.style);

        if(data.style=='disabled'){
            $(event.target).find('input')
            .each(function(itrt, item ){
                if($(item).attr('type') == 'checkbox' && $(item).prop('checked')) {
                    $(item).prop('checked', false).trigger('change');
                }
                $(item).prop("disabled", true );
            });
        }

        if(data.style=='hidden'){
            updateLineClass($(this));
            updateRepeatHeading($(this).closest(".ls-answers"));
        }
            
        console.ls.log($(this).find('input[disabled]'));
    });
}

/**
 * relevance:(on|off) event
 */
/* Update lines class when relevance:(on|off)  */
function updateLineClass(line){
    if($(line).hasClass("ls-odd") || $(line).hasClass("ls-even")){
        $(line).closest(".ls-answers").find(".ls-odd:visible,.ls-even:visible").each(function(index){ // not limited to table
            $(this).removeClass('ls-odd ls-even').addClass(((index+1)%2 == 0) ? "ls-odd" : "ls-even");
        });
    }
}
/* Update repeat heading */
function updateRepeatHeading(answers){
    /* Update only (at start) when all hidden line is done : @todo : do it only once */
    $(function() {
        if($(answers).data("repeatHeading") || $(answers).find("tbody").find(".ls-heading").length){
            /* set the data the first time */
            if(!$(answers).data("repeatHeading")){
                var repeatHeading=$(answers).find("tbody:first tr").length;/* first body don't have heading */
                $(answers).data("repeatHeading",repeatHeading)
                $(answers).data("repeatHeader",$(answers).find("tbody .ls-heading").filter(":first")[0].outerHTML);
            }else{
                var repeatHeading=$(answers).data("repeatHeading");
            }
            /* can remove the heading and clone this one of thead */
            var header = $(answers).data("repeatHeader");
            $(answers).find("tbody .ls-heading").remove();
            var lines=$(answers).find('tr:visible');
            var max=$(answers).find('tr:visible').length-1;
            $(lines).each(function(index){
                if(index != 0 && index % repeatHeading == 0 && index < max)
                {
                    $(header).insertAfter($(this));
                }
            });
        }
    });
}
/**
 * Manage the index
 */
function manageIndex(){
    /* only needed if it's not inside form (form#limesurvey) */
    $(".ls-index-buttons").on('click','[name="move"]',function(e){
        if(!$(this).closest('form').length && $('form#limesurvey').length==1){
            $(this).clone().addClass("hidden").appendTo('form#limesurvey').click();
        }
    });
}

/**
 * Reload page when participant selects a new language.
 * Sets input[name=lang] to new language and submits limesurvey form.
 */
function activateLanguageChanger(){
    var limesurveyForm = $('form#limesurvey');
    if(limesurveyForm.length == 0 && $('form[name="limesurvey"]').length == 1) { /* #form-token for example */
        limesurveyForm = $('form[name="limesurvey"]');
    }
    /**
     * @param {string} lang Language to change to.
     */
    var applyChangeAndSubmit = function(lang) {
        // Remove existing onsubmitbuttoninput, no need to remove lang : last one is the submitted
        $("#onsubmitbuttoninput").remove();
        // Append new input.
        $('<input type="hidden">')
            .attr('name', 'lang')
            .val(lang)
            .appendTo(limesurveyForm);
        // Append move type.
        /* onsubmitbuttoninput is related to template (and ajax) : MUST move to template with ajax … */
        $('<input type="hidden" id="onsubmitbuttoninput" name="move" value="changelang" />').appendTo(limesurveyForm);
        limesurveyForm.submit();
    };

    $('.form-change-lang a.ls-language-link').on('click', function() {
        var closestForm = $(this).closest('form');
        if (!closestForm.length) {
            /* we are not in a forum, can not submit directly */
            if (limesurveyForm.length == 1) {
                /* The limesurvey form exist in document, move select and button inside and click */
                var newLang = $(this).data('limesurvey-lang');
                applyChangeAndSubmit(newLang);
                // TODO: Check all code below. When does it happen?
            } else {
                // If there are no form : we can't use it */
                if($(this).data('targeturl')){
                    /* If we have a target url : just move location to this url with lang set */
                    /* possible usage : in clear all */
                    var target=$(this).data('targeturl');
                    /* adding lang in get param manually */
                    if(target.indexOf("?") >=0){
                        target+="&lang="+$(this).val();
                    }else{
                        target+="?lang="+$(this).val();
                    }
                    /* directly move to location */
                    location.href = target;
                    return false;
                }else{
                    var lang = $(this).data('limesurvey-lang');
                    /* No form, not targeturl : just see what happen */
                    $("<form>", {
                        "class":'ls-js-hidden',
                        "html": '<input type="hidden" name="lang" value="' + lang + '" />',
                        "action": target,
                        "method": 'get'
                    }).appendTo(document.body).submit();
                }

            }
        }else{
            /* we are inside a form : just submit : but remove other lang input if exist : be sure it's this one send */
            $(this).closest('form').find("[name='lang']").not($(this)).remove();
            $(this).closest('.ls-language-changer-item').find(":submit").click();
        }
    });
    /* Language changer dropdown */
    $('.form-change-lang [name="lang"] option:not(selected)').on('click', function(event) {
        var closestForm = $(this).closest('form');
        var newLang = $(this).val();
        if (!closestForm.length) {
            /* we are not in a forum, can not submit directly */
            if (limesurveyForm.length == 1) {
                /* The limesurvey form exist in document, move select and button inside and click */
                applyChangeAndSubmit(newLang);
                // TODO: Check all code below. When does it happen?
                // Answer : remind user can put language changer everywhere, not only in home page, but for example in clear all page etc …
            } else {
                // If there are no form : we can't use it */
                if($(this).parent().data('targeturl')){
                    /* If we have a target url : just move location to this url with lang set */
                    var target=$(this).parent().data('targeturl');
                    /* adding lang in get param manually */
                    if(target.indexOf("?") >=0){
                        target+="&lang="+$(this).val();
                    }else{
                        target+="?lang="+$(this).val();
                    }
                    /* directly move to location */
                    location.href = target;
                    return false;
                }else{
                    /* No form, not targeturl : just see what happen */
                    $("<form>", {
                        "class":'ls-js-hidden',
                        "html": '<input type="hidden" name="lang" value="' + newLang + '" />',
                        "action": target,
                        "method": 'get'
                    }).appendTo(document.body).submit();
                }

            }
        }else{
            /* we are inside a form : just submit : but remove other lang input if exist : be sure it's this one send */
            $(this).closest('form').find("[name='lang']").not($(this).parent()).remove();
            $(this).closest('.form-change-lang').find(':submit').click();
        }
    });
}

/**
 * Action link with submit object (json) : add params to form and submit
 */
function activateActionLink(){
    /* If no limesurvey form : don't need it */
    if(!$('form#limesurvey').length){
        $('[data-limesurvey-submit]').remove();
    }
    /* Submit limesurvey form on click */
    else{
        $('[data-limesurvey-submit]').on('click',function(event) {
            event.preventDefault();
            var submit=$(this).data('limesurvey-submit');
            var confirmedby=$(this).data('confirmedby');
            if(!confirmedby){
                $.each(submit, function(name, value) {
                    $("<input/>",{
                        'type':"hidden",
                        'name':name,
                        'value':value,
                    }).appendTo('form#limesurvey');
                });
                $('form#limesurvey').submit();
            }else{
                var submits=$.extend(submit,confirmedby);
                confirmSurveyDialog($(this).data('confirmlabel'),$(this).text(),submits)
            }
        });
    }
}
/**
 * function for replacing submit after confirm
 * @var string text : the text to be shown
 * @var string optionnal title
 * @var object[] submits : name.value to submit
 */
function confirmSurveyDialog(text,title,submits){
    if($.bsconfirm !== undefined) {
        $.bsconfirm(text, LSvar.lang.confirm, function(){
            $.each(submits, function(name, value) {
                $("<input/>",{
                    'type':"hidden",
                    'name':name,
                    'value':value,
                }).appendTo('form#limesurvey');
            });
            $('form#limesurvey').submit();
        });
    } else {
        if(confirm(text)){
            $.each(submits, function(name, value) {
                $("<input/>",{
                    'type':"hidden",
                    'name':name,
                    'value':value,
                }).appendTo('form#limesurvey');
            });
            $('form#limesurvey').submit();
        }
    }
}
/**
 *  Ask confirmation on click on .needconfirm
 */
function activateConfirmButton(){
    /* With ajax mode : using $(document).on attache X times the same event */
    $("button[data-confirmedby]").on('click',function(event){
        var btnConfirm=$(this);
        var cbConfirm=$(this).parent().find("[name='"+$(this).data('confirmedby')+"']");
        if(!$(cbConfirm).is(":checked")) {
            event.preventDefault();
            var submits = { };
            submits[$(btnConfirm).attr('name')]=$(btnConfirm).val();
            submits[$(cbConfirm).attr('name')]=$(cbConfirm).val();
            confirmSurveyDialog($(cbConfirm).parent("label").text(),$(btnConfirm).text(),submits)
        }
    });
}
/**
 * Trigger tip class when classChangeGood/classChangeError happen
 */
function triggerEmClassChange(){
    /* The tips */
    $(document).on('classChangeError','.ls-em-tip', function(event){
        $(this).removeClass("ls-em-success").addClass("ls-em-error text-danger");
    });
    $(document).on('classChangeGood','.ls-em-tip', function(event){
        $(this).removeClass("ls-em-error text-danger").addClass("ls-em-success");
    });
    /* The dynamic sum */
    $(document).on('classChangeError','.dynamic-total', function(event){
        $(this).removeClass("ls-em-success text-success").addClass("ls-em-error text-danger");
    });
    $(document).on('classChangeGood','.dynamic-total', function(event){
        $(this).removeClass("ls-em-error text-danger").addClass("ls-em-success text-success");
    });
    /* The input */
    $(document).on('classChangeError','input,select,textarea', function(event){
        $(this).closest(".form-control").addClass("has-warning"); // Use warning, not error : in multiple : if one input have error : it's apply to all input
    });
    $(document).on('classChangeGood','input,select,textarea', function(event){
        $(this).closest(".form-control").removeClass("has-warning");
    });
}

/**
 * has-error management for ls-error-mandatory
 * Only add ls-error-mandatory in PHP currently, not in js : different behaviour after try next and don't try next
 * /!\ We can more easily doing without js ( usage of :empty in css with :text & select) but then no boostrap, for before submit : use only css in template
 */
function updateMandatoryErrorClass(){
    $(".ls-error-mandatory .has-error,.ls-error-mandatory.has-error").on("blur",":text,textarea",function(event){
        if($(this).val()!==""){
            $(this).closest(".has-error").removeClass("has-error");
            if(!$(this).closest(".ls-error-mandatory").find(".has-error").length){
                $(this).closest(".ls-error-mandatory").find(".text-danger").removeClass("text-danger");
            }
        }
    });
    $(".ls-error-mandatory .has-error,.ls-error-mandatory.has-error").on("change","select",function(event){
        if($(this).val()!==""){
            $(this).closest(".has-error").removeClass("has-error");
            if(!$(this).closest(".ls-error-mandatory").find(".has-error").length){
                $(this).closest(".ls-error-mandatory").find(".text-danger").removeClass("text-danger");
            }
        }
    });
    $(".ls-error-mandatory.has-error").on("change",":radio",function(event){
        if($(this).closest(".array-flexible-duel-scale").length){
            if($(this).closest(".has-error").find("input:radio:checked").length>1){
                $(this).closest(".has-error").removeClass("has-error");
            }
        }else{
            $(this).closest(".has-error").removeClass("has-error");
        }
    });
    $(".ls-error-mandatory.has-error").on("change",":checkbox",function(event){
        $(this).closest(".has-error").removeClass("has-error");
    });
}

function resetQuestionTimers(sid) {
    var surveyTimersItemName = 'limesurvey_timers_by_sid_' + sid;
    var timers = JSON.parse(window.localStorage.getItem(surveyTimersItemName) || "[]");
    timers.forEach(function(timersessionname, idx){
        window.localStorage.removeItem('limesurvey_timers_' + timersessionname);
    });
    window.localStorage.removeItem(surveyTimersItemName);
}