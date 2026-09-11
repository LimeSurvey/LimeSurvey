/**
 * @file Language changer functionality for public survey
 * @copyright LimeSurvey <http://www.limesurvey.org/>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/**
 * Reload page when participant selects a new language.
 * Sets input[name=lang] to new language and submits limesurvey form.
 */
export function activateLanguageChanger() {
    var limesurveyForm = $("form#limesurvey");
    if (
        limesurveyForm.length == 0 &&
        $('form[name="limesurvey"]').length == 1
    ) {
        /* #form-token for example */
        limesurveyForm = $('form[name="limesurvey"]');
    }
    //autosizing for width of select so it hugs the selected option text
    //(the caret icon is placed in the select's reserved right padding, see language-changer.scss)
    var autoSizeSelect = function () {
        var text = $("#language-changer-select").find("option:selected").text();
        var $aux = $("<span/>").text(text);
        $aux.addClass("semi-14");
        $("#language-changer-select").after($aux);
        var width = $aux.width() + 2;
        $("#language-changer-select").width(width);
        $aux.remove();
    };
    /**
     * @param {string} lang Language to change to.
     */
    var applyChangeAndSubmit = function (lang) {
        // Remove existing onsubmitbuttoninput, no need to remove lang : last one is the submitted
        $("#onsubmitbuttoninput").remove();
        // Append new input.
        $('<input type="hidden">')
            .attr("name", "lang")
            .val(lang)
            .appendTo(limesurveyForm);
        // Append move type.
        /* onsubmitbuttoninput is related to template (and ajax) : MUST move to template with ajax … */
        $(
            '<input type="hidden" id="onsubmitbuttoninput" name="move" value="changelang" />',
        ).appendTo(limesurveyForm);
        limesurveyForm.submit();
    };
    autoSizeSelect();
    $(document).on("click", "a.ls-language-link", function () {
        var closestForm = $(this).closest("form");
        if (!closestForm.length) {
            /* we are not in a forum, can not submit directly */
            if (limesurveyForm.length == 1) {
                /* The limesurvey form exist in document, move select and button inside and click */
                var newLang = $(this).data("limesurvey-lang");
                applyChangeAndSubmit(newLang);
                // TODO: Check all code below. When does it happen?
            } else {
                // If there are no form : we can't use it */
                if ($(this).data("targeturl")) {
                    /* If we have a target url : just move location to this url with lang set */
                    /* possible usage : in clear all */
                    var target = $(this).data("targeturl");
                    /* adding lang in get param manually */
                    if (target.indexOf("?") >= 0) {
                        target += "&lang=" + $(this).val();
                    } else {
                        target += "?lang=" + $(this).val();
                    }
                    /* directly move to location */
                    location.href = target;
                    return false;
                } else {
                    var lang = $(this).data("limesurvey-lang");
                    /* No form, not targeturl : just see what happen */
                    $("<form>", {
                        class: "ls-js-hidden",
                        html:
                            '<input type="hidden" name="lang" value="' +
                            lang +
                            '" />',
                        action: target,
                        method: "get",
                    })
                        .appendTo(document.body)
                        .submit();
                }
            }
        } else {
            /* we are inside a form : just submit : but remove other lang input if exist : be sure it's this one send */
            $(this).closest("form").find("[name='lang']").not($(this)).remove();
            $(this)
                .closest(".ls-language-changer-item")
                .find(":submit")
                .click();
        }
    });
    /* Language changer dropdown */
    /* Don't activate change when using key up / key down */
    $('.form-change-lang [name="lang"]').on(
        "keypress keydown keyup",
        function (event) {
            var code = event.keyCode || event.which;
            /* packaje name : limesurvey */
            $(this).data("limesurvey-lastkey", code);
        },
    );
    $('.form-change-lang [name="lang"]').on("click", function (event) {
        /* didn't work with chrome , chrom bug : onclick are an intrinsic event see https://www.w3.org/TR/html401/interact/forms.html#h-17.6 */
        /* Happen rarely (keyboard + mouse + still have the button */
        $(this).data("limesurvey-lastkey", null);
    });
    $('.form-change-lang [name="lang"]').on("change", function (event) {
        autoSizeSelect();
        if (
            $(this).data("limesurvey-lastkey") == 38 ||
            $(this).data("lastkey") == 40
        ) {
            /* Last key is up or down : disable auto submit mantis #16024 */
            return;
        }
        var closestForm = $(this).closest("form");
        var newLang = $(this).val();
        if (!closestForm.length) {
            /* we are not in a form, can not submit directly */
            // Remind user can put language changer everywhere, not only in home page, but for example in clear all page etc … in form or not etc ...
            if (limesurveyForm.length == 1) {
                /* The limesurvey form exist in document, move select and button inside and click */
                applyChangeAndSubmit(newLang);
            } else {
                // If there are no form : we can't use it */
                if ($(this).parent().data("targeturl")) {
                    /* If we have a target url : just move location to this url with lang set */
                    /* targeturl was used for preview gropup and question in 2.6lts : check if still used/usable */
                    var target = $(this).parent().data("targeturl");
                    /* adding lang in get param manually */
                    if (target.indexOf("?") >= 0) {
                        target += "&lang=" + $(this).val();
                    } else {
                        target += "?lang=" + $(this).val();
                    }
                    /* directly move to location */
                    location.href = target;
                    return false;
                } else {
                    /* No form, not targeturl : just see what happen */
                    /* This must not happen : issue in theme */
                    $("<form>", {
                        class: "ls-js-hidden",
                        html:
                            '<input type="hidden" name="lang" value="' +
                            newLang +
                            '" />',
                        action: target,
                        method: "get",
                    })
                        .appendTo(document.body)
                        .submit();
                }
            }
        } else {
            /* we are inside a form : just submit : but remove other lang input if exist : be sure it's this one send */
            $(this).closest("form").find("[name='lang']").not(this).remove();
            $(this).closest(".form-change-lang").find(":submit").click();
        }
    });
}

// register to global scope
window.activateLanguageChanger = activateLanguageChanger;
