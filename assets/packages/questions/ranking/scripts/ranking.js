/*
 * This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 */

var RankingQuestion = function (options) {
    "use strict";
    //Define option parameters
    var max_answers = options.max_answers,
        min_answers = options.min_answers,
        rankingName = options.rankingName,
        showpopups = (typeof options.showpopups != 'undefined') ? parseInt(options.showpopups) : 1,
        samechoiceheight = (typeof options.samechoiceheight != 'undefined') ? parseInt(options.samechoiceheight) : 1,
        samelistheight = (typeof options.samelistheight != 'undefined') ? parseInt(options.samelistheight) : 1,
        questionId = options.questionId;
    //define reused variables
    var relevancename = "relevance" + rankingName,
        rankingID = "javatbd" + rankingName;

    //define HTML snippets
    var screenReader = "<div class='sr-only'>" + $('#question' + questionId + ' .em_default').html() + "</div><div aria-hidden='true'>" + LSvar.lang.rankhelp + "</div>"

    //define functions
    var createSorting = function () {
            //Add a class to the question
            $('#question' + questionId + '').addClass('sortable-activated');
            // Set up the connected sortable
            console.ls.log("Sortable activated");
            console.ls.log("With options: ", options);
            $('#sortable-choice-' + questionId).sortable({
                group: "sortable-" + questionId,
                filter: '.ls-irrelevant',
                ghostClass: "ls-rank-placeholder",
                sort: false,
                scroll: false,
                onStart: function () {
                    console.ls.log("Move started (choice)");
                    document.ontouchmove = function (e) {
                        e.preventDefault();
                    }
                },
                onEnd: function () {
                    console.ls.log("Move ended (choice)");
                    document.ontouchmove = function (e) {
                        return true;
                    }
                },
                onMove: function (ev) {
                    if (max_answers > 0 && $('#sortable-rank-' + questionId + ' li').length >= max_answers) {
                        //sortableAlert();
                        return false;
                    }
                }
            });

            $('#sortable-rank-' + questionId).sortable({
                group: "sortable-" + questionId,
                ghostClass: "ls-rank-placeholder",
                onStart: function () {
                    console.ls.log("Move started (ranked)");
                    document.ontouchmove = function (e) {
                        e.preventDefault();
                    }
                },
                onEnd: function () {
                    console.ls.log("Move ended (ranked)");
                    document.ontouchmove = function (e) {
                        return true;
                    }
                },
                onSort: function (evt) {
                    if ($(evt.item).hasClass("disabled")) {
                        /* see https://github.com/RubaXa/Sortable/issues/933 */
                        $(evt.item).appendTo('#sortable-choice-' + questionId);
                        /* send a console notice 'from is undefined' in jquery.fn.sortable : must fix Sortable before */
                    } else {
                        updateDragDropRank.call(self, questionId);
                    }
                }
            });
            $('#question' + questionId + ' .ls-remove').remove();
            // Adapt choice and list height
            fixChoiceListHeight();

        },
        fixScreenReaderCompatibility = function () {
            // Hide the default answers list but display for media oral or screen reader
            // We are in javascript, then default tip can be replaced
            $('#question' + questionId + ' .em_default').html(screenReader);
            $('#question' + questionId + ' .answers-list').on("change", ".select-item", {
                    source: false
                },
                function (event, data) {
                    data = data || event.data;
                    if (data.source != 'dragdrop')
                        loadDragDropRank();
                }
            );
        },
        bindActions = function () {
            // Allow users to double click to move to selections from list to list
            $('#sortable-choice-' + questionId).on('dblclick', 'li:not(.ls-irrelevant)', function () {

                if (max_answers > 0 && $('#sortable-rank-' + questionId + ' li').length >= max_answers) {
                    sortableAlert();
                    return false;
                } else {
                    $(this).appendTo('#sortable-rank-' + questionId + '');
                }
                updateDragDropRank();
            });

            $('#sortable-rank-' + questionId).on('dblclick', 'li', function () {
                $(this).appendTo('#sortable-choice-' + questionId + '');
                updateDragDropRank();
            });
        },
        doDragDropRank = function () {
            fixScreenReaderCompatibility();
            createSorting();
            loadDragDropRank();
            fixDisableDropdown();
            bindActions();
        },

        /**
         * Update answers after updating drag and drop part
         */
        updateDragDropRank = function () {
            $('#question' + questionId + ' .select-item select').val('');
            $('#sortable-rank-' + questionId + ' li').each(function (index) {
                $('#question' + questionId + ' .select-item select').eq(index).data("old-val", $('#question' + questionId + ' .select-item select').eq(index).val());
                $('#question' + questionId + ' .select-item select').eq(index).val($(this).data("value"));
                /* todo ? Set next option with same value disable ? */
            });

            // Update #relevance and lauch checkconditions function
            $("[id^=" + relevancename + "]").val('0');

            $('#question' + questionId + ' .select-item select:lt(' + max_answers + ')').each(function (index) {
                if ($(this).val() != "") {
                    $("#" + relevancename + (index + 1)).val("1");
                }
                /* trigger change only if val is updated see #14425 */
                if ((typeof $(this).data("old-val") == 'undefined' && $(this).val() != "") || ($(this).val() != $(this).data("old-val"))) {
                    $(this).trigger("change", {
                        source: 'dragdrop'
                    });
                    $(this).data("old-val", $(this).val())
                }
            });
            $('#sortable-rank-' + questionId + ' li').removeClass("text-error");
            $('#sortable-choice-' + questionId + ' li').removeClass("text-error");
            $('#sortable-rank-' + questionId + ' li:gt(' + (max_answers * 1 - 1) + ')').addClass("text-error");
        },
        fixDisableDropdown = function() {
            /* Since due to ajax mode : loaded after default triggerEmRelevanceSubQuestion : must set disable on unavailbale option (sreen reader) */
            /* Not needed if script is in POS_END … */
            $('#question' + questionId + ' .sortable-item.ls-irrelevant').each(function() {
                $(this).closest(".ls-answers").find("option[value=" + $(this).data("value") + "]").prop('disabled', true);
            });
        },
        sortableAlert = function () {
            if (showpopups) {
                var txtAlert = $("#question" + questionId + " .em_num_answers").text()
                window.templateCore.alertSurveyDialog(txtAlert, '');
            }
        },
        loadDragDropRank = function () {
            // Update #relevance
            $("[id^=" + relevancename + "]").val('0');
            $('#sortable-rank-' + questionId + ' li').each(function () {
                $(this).appendTo('#sortable-choice-' + questionId);
            });
            $('#question' + questionId + ' .select-item select :selected').each(function (index) {
                if ($(this).val() != '') {
                    $("#" + relevancename + (index + 1)).val("1");
                    $('#sortable-choice-' + questionId + ' li#' + rankingID + $(this).val()).appendTo('#sortable-rank-' + questionId);
                }
                /* set old-val for updateDragDropRank see #14425 */
                $(this).closest("select").data("old-val", $(this).closest("select").val());
            });

            updateDragDropRank(); // Update to reorder select

            $('#sortable-rank-' + questionId + ' li').removeClass("error");
            $('#sortable-choice-' + questionId + ' li').removeClass("error");
            $('#sortable-rank-' + questionId + ' li:gt(' + (max_answers * 1 - 1) + ')').addClass("error");
        },

        fixChoiceListHeight = function () {
            if (samechoiceheight) {
                /* Do it at load */
                setChoiceHeight();
                /* Do it when any choice are updated by EM (mantis #14406) */
                $('#question' + questionId + ' .ls-choice').on('html:updated', function () {
                    $('#question' + questionId + ' .ls-choice').css("min-height", "");
                    setChoiceHeight();
                });
                /* Do it when a choice is shown/hidden by filter (mantis #14411) */
                /* attach to the last one only , see #14424. This can need update when EM javascript was improved */
                $('#question' + questionId).on('relevance:on relevance:off', '.ls-choice:last', function (event, data) {
                    if (event.target != this) return;
                    data = $.extend({
                        style: 'hidden'
                    }, data);
                    if (data.style == 'hidden') {
                        setChoiceHeight();
                    }
                });
                /* Do it on window resize */
                $(window).resize(function () {
                    $('#question' + questionId + ' .ls-choice').css("min-height", "");
                    setChoiceHeight();
                });
            }
            if (samelistheight) {
                /* Do it at same time it happen for choice */
                setListHeight();
                $('#question' + questionId + ' .ls-choice').on('html:updated', function () {
                    setListHeight();
                });
                /* attach to the last one only , see #14424. This can need update when EM javascript was improved */
                $('#question' + questionId).on('relevance:on relevance:off', '.ls-choice:last', function (event, data) {
                    if (event.target != this) return;
                    data = $.extend({
                        style: 'hidden'
                    }, data);
                    if (data.style == 'hidden') {
                        setListHeight();
                    }
                });
                $(window).resize(function () {
                    setListHeight();
                });
            }
        },
        setChoiceHeight = function () {
            $('#question' + questionId + ' .ls-choice').css("min-height", ""); // reset the min-height style
            var maxHeight = 0;
            $('#question' + questionId + ' .ls-choice:not(.ls-hidden)').each(function () {
                if ($(this).actual('height') > maxHeight) {
                    maxHeight = $(this).actual('height');
                }
            });
            $('#question' + questionId + ' .ls-choice').css("min-height", maxHeight + "px");
        },
        setListHeight = function () {
            var totalHeight = 0;
            $('#question' + questionId + ' .ls-choice:not(.ls-hidden)').each(function () {
                totalHeight = totalHeight + $(this).actual('outerHeight', {
                    includeMargin: true
                }); /* Border not inside */
            });
            /* Add the padding to min-height */
            $('#sortable-choice-' + questionId + ',#sortable-rank-' + questionId).css("min-height", totalHeight + "px");
        },
        triggerEmRelevanceSortable = function () {
            $('#question' + questionId + ' .sortable-item').on('relevance:on', function (event, data) {
                data = $.extend({
                    style: 'hidden'
                }, data);
                $(event.target).closest(".ls-answers").find("option[value=" + $(event.target).data("value") + "]").prop('disabled', false);
            });

            $('#question' + questionId + ' .sortable-item').on('relevance:off', function (event, data) {
                data = $.extend({
                    style: 'hidden'
                }, data);
                $(event.target).closest(".ls-answers").find("option[value=" + $(event.target).data("value") + "]").prop('disabled', true);
                /* reset already ranked item */
                if ($(event.target).parent().hasClass("sortable-rank")) {
                    $(event.target).appendTo($(event.target).closest(".answers-list").find(".sortable-choice"));
                    var questionId = $(event.target).closest("[id^='question']").attr('id').replace("question", "");
                    updateDragDropRank();
                }
            });
        };

    return {
        init: function () {
            /* must trigger hidden before set heght */
            triggerEmRelevanceSortable();
            doDragDropRank();
        }
    }

};
