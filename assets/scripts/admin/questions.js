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
var QuestionFunctions = function() {
    var /**
         * Validate question object before submit : actually only title need to be validated
         * This disallow submitting if Question code are not unique (else loose all fields)
         */
        validateQuestion = function(jqObject) {
            return new Promise(function(resolve, reject){
                if (typeof jqObject == "undefined") {
                    jqObject = $([]);
                }
                $.post(
                    validateUrl,
                    {
                        title: $('#frmeditquestion [name="title"]:first').val(),
                        other: $(
                            '#frmeditquestion [name="other"]:checked:first'
                        ).val()
                    },
                    function(data) {
                        // Remove all custom validity
                        if (hasFormValidation) {
                            $("#frmeditquestion input.has-error").each(function() {
                                if (hasFormValidation) {
                                    $(this)[0].setCustomValidity("");
                                }
                                $(this).removeClass("has-error");
                                $(this)
                                    .next(".errorMessage")
                                    .remove();
                            });
                        }
                        // No error : submit
                        if ($.isEmptyObject(data)) {
                            if ($(jqObject).is(":submit")) {
                                $(jqObject).trigger("click", {
                                    validated: true
                                });
                                resolve(true);
                            }
                        } else {
                            // Add error information for each input
                            $.each(data, function(name, aError) {
                                if ($(jqObject).is(":submit")) {
                                    $("#frmeditquestion")
                                        .closest("#tabs")
                                        .find(".ui-tabs-anchor:first")
                                        .click();
                                    $(
                                        '#frmeditquestion [type!=hidden][name="' +
                                            name +
                                            '"]'
                                    ).focus(); // Focus on the first input
                                }
                                $(
                                    '#frmeditquestion [type!=hidden][name="' +
                                        name +
                                        '"]'
                                ).addClass("has-error");
                                if (
                                    !$(
                                        '#frmeditquestion [type!=hidden][name="' +
                                            name +
                                            '"]:last'
                                    ).next(".errorMessage").length
                                ) {
                                    // :last for radio list
                                    $(
                                        "<span class='errorMessage text-warning' />"
                                    ).insertAfter(
                                        '#frmeditquestion [type!=hidden][name="' +
                                            name +
                                            '"]:last'
                                    );
                                }
                                $.each(aError, function(i, error) {
                                    if (hasFormValidation) {
                                        $(
                                            '#frmeditquestion [type!=hidden][name="' +
                                                name +
                                                '"]'
                                        ).each(function() {
                                            $(this)[0].setCustomValidity(error);
                                        });
                                    }
                                    $(
                                        '#frmeditquestion [type!=hidden][name="' +
                                            name +
                                            '"]'
                                    )
                                        .next(".errorMessage")
                                        .text(error);
                                });
                            });
                            reject(false);
                        }
                    },
                    (dataType = "json")
                );
            });
        },
        getQuestionTypeImage = function(questioncode) {
            var multiple = 1;
            if (questioncode == "S") multiple = 2;

            if (questioncode == ":") questioncode = "COLON";
            else if (questioncode == "|") questioncode = "PIPE";
            else if (questioncode == "*") questioncode = "EQUATION";

            var imageArray = [];
            for (i = 1; i <= multiple; i++) {
                imageArray.push(
                    imgurl +
                        "/screenshots/" +
                        questioncode +
                        (i == 1 ? "" : i) +
                        ".png"
                );
            }

            return imageArray.reduce(function(prev, curr, idx) {
                return prev + '<img src="' + curr + '" />';
            }, "");
        },
        init = function() {
            var oldQuestionTemplate = "";
            updatequestionattributes("");
            $("#questionTypeSelector").on("change", function() {
                //  Simple Question type selector in General settings
                $("#question_type")
                    .val($(this).val())
                    .trigger("change");
                updatequestionattributes();
                updateQuestionTemplateOptions();
            });

            $("#question_type").on("change", function() {
                OtherSelection(this.value);
            });

            $(document)
                .on("click", "#question_template", function() {
                    // save old value before the change
                    oldQuestionTemplate = $(this).val();
                })
                .on("change", "#question_template", function() {
                    updatequestionattributes(
                        $(this).val(),
                        oldQuestionTemplate
                    );
                    updateQuestionTemplateOptions("question_template");
                });

            /**
             * Validate question object on blur on title element
             */
            $('#frmeditquestion :not(:hidden)[name="title"]').on(
                "blur",
                function() {
                    validateQuestion($(this));
                }
            );

            /**
             * Validate question object before click on a submit button
             */
            $("#frmeditquestion").on("submit", function(event) {
                validateQuestion().then(
                    function(result){
                        return result;
                    },
                    function(reject){
                        return false;
                    }
                )
            });
        };
    return {
        init: init
    };
};

$(document).on("ready  pjax:scriptcomplete", function() {
    window.questionFunctions =
        window.questionFunctions || new QuestionFunctions();
    window.questionFunctions.init();
    window.LS.fixAccordionPosition();
});

function updatequestionattributes(question_template_name, oldQuestionTemplate) {
    question_template_name = question_template_name || "";
    oldQuestionTemplate = oldQuestionTemplate || "";
    var type = $("#question_type").val();
    OtherSelection(type);
    $(".loader-advancedquestionsettings").removeClass("hidden");
    $(".panel-advancedquestionsettings").remove();

    var selected_value = $("#question_type").val();

    var postData = {
        qid: $("#qid").val(),
        question_type: selected_value,
        sid: $("input[name=sid]").val(),
        old_question_template: oldQuestionTemplate
    };

    if (
        Object.prototype.toString.call(question_template_name) ==
        "[object String]"
    ) {
        postData["question_template"] = question_template_name;
    }
    window.LS.unrenderBootstrapSwitch();
    $.ajax({
        url: attr_url,
        data: postData,
        method: "POST",
        success: function(data) {
            $("#container-advanced-question-settings").html(data);
            $(".loader-advancedquestionsettings").addClass("hidden");
            window.LS.renderBootstrapSwitch();
            if (question_template_name) {
                //$('#collapse-cat1').collapse('toggle');
            }

            $("label[title]").qtip({
                style: {
                    name: "cream",
                    tip: true,
                    color: "#111111",
                    border: {
                        width: 1,
                        radius: 5,
                        color: "#EADF95"
                    }
                },
                position: {
                    adjust: {
                        screen: true,
                        scroll: true
                    },
                    corner: {
                        target: "bottomRight"
                    }
                },
                show: {
                    effect: {
                        length: 50
                    }
                }
            });
        }
    });
}

function updateQuestionTemplateOptions(selector) {
    // selector is only set when this function is called from #question_template
    selector = selector || "";
    var type = $("#question_type").val();
    var template = $("#question_template").val();
    $.ajax({
        url: get_question_template_options_url,
        data: { type: type },
        method: "POST",
        success: function(data) {
            if (data[template] != undefined) {
                $("#QuestionTemplatePreview img").attr(
                    "src",
                    data[template]["preview"]
                );
            } else {
                $("#QuestionTemplatePreview img").attr(
                    "src",
                    data["core"]["preview"]
                );
            }

            if (selector === "") {
                // selector is not called from #question_template
                $("#question_template").html("");
                $.each(data, function(key, value) {
                    $("#question_template").append(
                        "<option value=" + key + ">" + value.title + "</option>"
                    );
                });
            }
        }
    });
}

var qtypes = new Array();
var qnames = new Array();
var qhelp = new Array();
var qcaption = new Array();

function OtherSelection(QuestionType) {
    if (QuestionType == undefined) {
        //console.log('Error: OtherSelection: QuestionType must not be undefined');
        return;
    }
    try {
        if (QuestionType == "") {
            QuestionType = document.getElementById("question_type").value;
        }
        if (
            QuestionType == "M" ||
            QuestionType == "P" ||
            QuestionType == "L" ||
            QuestionType == "!"
        ) {
            document.getElementById("OtherSelection").style.display = "";
            document.getElementById("Validation").style.display = "none";
            document.getElementById("MandatorySelection").style.display = "";
        } else if (QuestionType == "W" || QuestionType == "Z") {
            document.getElementById("OtherSelection").style.display = "";
            document.getElementById("Validation").style.display = "none";
            document.getElementById("MandatorySelection").style.display = "";
        } else if (QuestionType == "|") {
            document.getElementById("OtherSelection").style.display = "none";
            document.getElementById("Validation").style.display = "none";
            document.getElementById("MandatorySelection").style.display =
                "none";
        } else if (QuestionType == "F" || QuestionType == "H") {
            document.getElementById("OtherSelection").style.display = "none";
            document.getElementById("Validation").style.display = "none";
            document.getElementById("MandatorySelection").style.display = "";
        } else if (QuestionType == ":" || QuestionType == ";") {
            document.getElementById("OtherSelection").style.display = "none";
            document.getElementById("Validation").style.display = "";
            document.getElementById("MandatorySelection").style.display = "";
        } else if (QuestionType == "1") {
            document.getElementById("OtherSelection").style.display = "none";
            document.getElementById("Validation").style.display = "none";
            document.getElementById("MandatorySelection").style.display = "";
        } else if (
            QuestionType == "S" ||
            QuestionType == "T" ||
            QuestionType == "U" ||
            QuestionType == "N" ||
            QuestionType == "" ||
            QuestionType == "K"
        ) {
            document.getElementById("Validation").style.display = "";
            document.getElementById("OtherSelection").style.display = "none";
            if (document.getElementById("ON")) {
                document.getElementById("ON").checked = true;
            }
            document.getElementById("MandatorySelection").style.display = "";
        } else if (QuestionType == "X") {
            document.getElementById("Validation").style.display = "none";
            document.getElementById("OtherSelection").style.display = "none";
            document.getElementById("MandatorySelection").style.display =
                "none";
        } else if (QuestionType == "Q") {
            document.getElementById("Validation").style.display = "";
            document.getElementById("OtherSelection").style.display = "none";
            document.getElementById("MandatorySelection").style.display = "";
        } else {
            document.getElementById("OtherSelection").style.display = "none";
            if (document.getElementById("ON")) {
                document.getElementById("ON").checked = true;
            }
            document.getElementById("Validation").style.display = "none";
            document.getElementById("MandatorySelection").style.display = "";
        }

        if (QuestionType == "H") {
            // hide relevance equation input for array by column question type until it is fixed
            document.getElementById("relevanceContainer").style.display =
                "none";
        } else {
            document.getElementById("relevanceContainer").style.display = "";
        }
        
    } catch (e) {
        if (window.debugState.backend) console.ls.error(e);
    }
}
