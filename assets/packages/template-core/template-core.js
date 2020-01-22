/**
 * @file Default template functionnality
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

var TemplateCoreClass = function () {
    return {
        /**
         * Dialog and confirm
         */
        /* showStartPopups : replace core function : allow HTML and use it. Unusuable with ajax */
        showStartPopups: function () {
            if (LSvar.showpopup == 1  && $.isArray(LSvar.startPopups)) {
                startPopups = LSvar.startPopups.map( function (text) {
                    return "<p>"+text+"</p>";
                });
                window.templateCore.alertSurveyDialog(startPopups.join(""));
            }
        },
        /* alertSurveyDialog @see application/core/package/limesurvey
         * @var string[] startPopups array of text to be shown
         * @var string title
         */
        alertSurveyDialog: function (startPopups, title) {
            text = "";
            if (LSvar.showpopup == 1  && $.isArray(startPopups)) {
                startPopups = startPopups.map(function (text) {
                    return "<p>"+text+"</p>";
                });
                text = startPopups.join("");
            }
            if (text) {
                $("#bootstrap-alert-box-modal .modal-header .modal-title").text(title || "");
                $("#bootstrap-alert-box-modal .modal-body").html(text);
                $("#bootstrap-alert-box-modal").modal('show');
            }
        },
        /* confirmSurveyDialog @see application/core/package/limesurvey */
        confirmSurveyDialog: function (text, title, submits) {
            $("#bootstrap-alert-box-modal .modal-header .modal-title").text(title);
            $("#bootstrap-alert-box-modal .modal-body").html("<p>" + text + "</p>" + "<div class='btn-group btn-group-justified' role='group'><a class='btn btn-warning btn-confirm' data-dismiss='modal'>" + LSvar.lang.yes + "</a><a class='btn btn-default btn-cancel' data-dismiss='modal'>" + LSvar.lang.no + "</a></div>");
            $("#bootstrap-alert-box-modal").modal('show');
            $("#bootstrap-alert-box-modal .btn-confirm").on('click', function () {
                $.each(submits, function (name, value) {
                    $("<input/>", {
                        'type': "hidden",
                        'name': name,
                        'value': value,
                    }).appendTo('form#limesurvey');
                });
                $('form#limesurvey').submit();
            });
        },

        /**
         * Add class hover to column in table-col-hover
         * We can't use CSS solution : need no background
         */
        addHoverColumn: function () {
            $(".table-col-hover").on({
                mouseenter: function () {
                    $(this).closest(".table-col-hover").find("col").eq($(this).parent(".answers-list").children().index($(this))).addClass("hover");
                },
                mouseleave: function () {
                    $(this).closest(".table-col-hover").find("col").removeClass("hover");
                }
            }, ".answer-item");
        },

        /**
         * Update some class when em-tips is success/error
         * @see core/package/limesurvey/survey.js:triggerEmClassChange
         */
        triggerEmClassChangeTemplate: function () {
            $('.ls-em-tip').each(function () {
                $(this).on('classChangeError', function () {
                    /* If user choose hide-tip : leave it */
                    $parent = $(this).parent('div.hide-tip');
                    if ($parent.hasClass('hide-tip')) {
                        $parent.removeClass('hide-tip', 1);
                        $parent.addClass('tip-was-hidden', 1);
                    }
                    $questionContainer = $(this).parents('div.question-container');
                    $questionContainer.addClass('input-error'); /* No difference betwwen error after submit and error before submit : think (Shnoulle) it's better to have a difference */
                });

                $(this).on('classChangeGood', function () {
                    /* If user choose hide-tip : leave it */
                    $parent = $(this).parents('div.hide-tip');
                    $parent.removeClass('text-danger');
                    $parent.addClass('text-info');
                    if ($parent.hasClass('tip-was-hidden')) {
                        $parent.removeClass('tip-was-hidden').addClass('hide-tip');
                    }
                    $questionContainer = $(this).parents('div.question-container');
                    $questionContainer.removeClass('input-error'); /* Not working with mandatory question ... */
                });
            });


        },
        /**
         * Hide/show question if all sub-questions is hidden
         * @see core/package/limesurvey/survey.js:triggerEmRelevanceSubQuestion
         * @see https://bugs.limesurvey.org/view.php?id=10055 (partial)
         * Must be before ready (event happen before ready)
         */
        hideQuestionWithRelevanceSubQuestion: function () {
            $(".question-container:not(.ls-irrelevant)").on('relevance:on', "[id^='javatbd']", function (event, data) {
                if (event.target != this) return; // not needed now, but after (2016-11-07)
                data = $.extend({
                    style: 'hidden'
                }, data);
                if (data.style == 'hidden') {
                    $(this).closest(".question-container").removeClass("ls-hidden");
                }
            });
            $(".question-container:not(.ls-hidden)").on('relevance:off', "[id^='javatbd']", function (event, data) {
                if (event.target != this) return; // not needed now, but after (2016-11-07)
                data = $.extend({
                    style: 'hidden'
                }, data);
                if (data.style == 'hidden') {
                    var questionContainer = $(this).closest(".question-container");
                    $(this).addClass("ls-hidden");
                    if ($(questionContainer).find("[id^='javatbd']").filter(':not(.ls-hidden)').length == 0) {
                        $(questionContainer).addClass("ls-hidden");
                    }
                }
            });
        },
        /**
         * Hide/show parent multiple list
         * @see core/package/limesurvey/survey.js:triggerEmRelevanceSubQuestion
         * @see https://bugs.limesurvey.org/view.php?id=11787
         * Must be before ready (event happen before ready)
         */
        hideMultipleColumn: function () {
            $("[id^='question']").on('relevance:on', ".multiple-list [id^='javatbd']", function (event, data) {
                if (event.target != this) return;
                data = $.extend({
                    style: 'hidden'
                }, data);
                if (data.style == 'hidden') {
                    $(this).closest(".list-unstyled").removeClass("ls-hidden")
                }
            });
            $("[id^='question']").on('relevance:off', ".multiple-list [id^='javatbd']", function (event, data) {
                if (event.target != this) return;
                data = $.extend({
                    style: 'hidden'
                }, data);
                if (data.style == 'hidden') {
                    if ($(this).closest(".list-unstyled").find("[id^='javatbd']:visible").length == 0) {
                        $(this).closest(".list-unstyled").addClass("ls-hidden");
                    }
                }
            });
        }
    }
};


if (!window.templateCore) {
    window.templateCore = new TemplateCoreClass();
}

/* document ready function */
$(document).on('ready pjax:scriptcomplete', function () {
    window.templateCore.addHoverColumn();
    window.templateCore.triggerEmClassChangeTemplate();
});
