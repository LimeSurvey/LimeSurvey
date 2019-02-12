/*
 * This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 */


var AdvancedRankingQuestion = function (options) {
    "use strict";
    //Define option parameters
    var questionId = options.questionId,
        max_answers = options.max_answers,
        min_answers = options.min_answers,
        rankingName = options.rankingName,
        showpopups = options.showpopups || true,
        samechoiceheight = options.samechoiceheight || true,
        samelistheight = options.samelistheight || true,
        numbersActive = options.numbersActive,
        withHandle = options.withHandle;

    //define reused variables
    var relevancename= "relevance"+rankingName,
        rankingID = "javatbd" + rankingName;

    //define HTML snippets
    var screenReader = "<div class='sr-only'>" + $('#question' + questionId + ' .em_default').html() + "</div><div aria-hidden='true'>" + LSvar.lang.rankhelp + "</div>"

    //define functions
    var createSorting = function(){
        //Add a class to the question
        $('#question' + questionId + '').addClass('sortable-activated');
        // Set up the connected sortable
        var sortableObjectChoice = {
            group: "sortable-" + questionId,
            ghostClass: "ls-rank-placeholder",
            onEnd: function(){updateRankingNumber();}
        },
        sortableObjectRank = {
            group: "sortable-" + questionId,
            ghostClass: "ls-rank-placeholder",
            onEnd: function(){updateRankingNumber();},
            onSort: function (evt) {
                if ($(evt.item).hasClass("disabled")) {
                    /* see https://github.com/RubaXa/Sortable/issues/933 */
                    $(evt.item).appendTo('#sortable-choice-' + questionId);
                    /* send a console notice 'from is undefined' in jquery.fn.sortable : must fix Sortable before */
                } else {
                    updateDragDropRank();
                }
            }
        };

        if(withHandle === 1){
            sortableObjectChoice.handle = '.selector__dragHandle';
            sortableObjectRank.handle = '.selector__dragHandle';
        }


        $('#sortable-choice-' + questionId).sortable(sortableObjectChoice);
        $('#sortable-rank-' + questionId).sortable(sortableObjectRank);

        $('#question' + questionId + ' .ls-remove').remove();
        // Adapt choice and list height
        fixChoiceListHeight();
    },
    fixScreenReaderCompatibility = function(){
        // Hide the default answers list but display for media oral or screen reader
        // We are in javascript, then default tip can be replaced
        $('#question' + questionId + ' .em_default').html(screenReader);
        $('#question' + questionId + ' .answers-list').on("change", ".select-item", 
            {source: false}, 
            function (event, data) {
                data = data || event.data;
                if (data.source != 'dragdrop')
                    loadDragDropRank();
            }
        );
    },
    bindActions = function () {
        // Allow users to double click to move to selections from list to list
        $('#sortable-choice-' + questionId).on('dblclick', '.answer-item.sortable-item.ls-choice:not(.disabled)', function () {

            if (max_answers > 0 && $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').length >= max_answers) {
                sortableAlert();
                return false;
            } else {
                $(this).appendTo('#sortable-rank-' + questionId + '');
            }
            updateDragDropRank();
        });

        $('#sortable-rank-' + questionId).on('dblclick', '.answer-item.sortable-item.ls-choice', function () {
            $(this).appendTo('#sortable-choice-' + questionId + '');
            updateDragDropRank();
        });
    },
    doDragDropRank = function () {
        fixScreenReaderCompatibility();
        createSorting();
        loadDragDropRank();
        bindActions();
    },
    
    updateRankingNumber= function () {
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function (index,item) {
            $(item).find('.selector__index_row').text('#'+(index+1));
        });
        $('#sortable-choice-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function (index,item) {
            $(item).find('.selector__index_row').text('#'+(index+1));
        });
    },
    /**
     * Update answers after updating drag and drop part
     */
    updateDragDropRank = function() {
        
        $('#question' + questionId + ' .select-item select').val('');

        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function (index,item) {
            if(numbersActive === 1){
                updateRankingNumber();
            }

            $('#question' + questionId + ' .select-item select').eq(index).val($(this).data("value"));

        });

        // Update #relevance and lauch checkconditions function
        $("[id^=" + relevancename + "]").val('0');

        $('#question' + questionId + ' .select-item select:lt(' + max_answers + ')').each(function (index) {
            if ($(this).val() != "") {
                $("#" + relevancename + (index+1) ).val("1");
            }
            $(this).trigger("change", { source: 'dragdrop' });
        });
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').removeClass("text-error");
        $('#sortable-choice-' + questionId + ' .answer-item.sortable-item.ls-choice').removeClass("text-error");
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice:gt(' + (max_answers * 1 - 1) + ')').addClass("text-error");
    },

    sortableAlert = function() {
        if (showpopups) {
            var txtAlert = $("#question" + questionId + " .em_num_answers").text()
            window.templateCore.alertSurveyDialog(txtAlert, '');
        }
    },
    loadDragDropRank = function (questionId) {
        
        // Update #relevance
        $("[id^=" + relevancename + "]").val('0');
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function () {
            $(this).appendTo('#sortable-choice-' + questionId );
        });
        $('#question' + questionId + ' .select-item select').each(function (index) {
            if ($(this).val() != '') {
                $("#" + relevancename + (index+1)).val("1");
                $('#sortable-choice-' + questionId + ' #' + rankingID + $(this).val()).appendTo('#sortable-rank-' + questionId);
            }
        });

        updateDragDropRank(); // Update to reorder select

        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').removeClass("error");
        $('#sortable-choice-' + questionId + ' .answer-item.sortable-item.ls-choice').removeClass("error");
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice:gt(' + (max_answers * 1 - 1) + ')').addClass("error");
    },

    fixChoiceListHeight = function() {
        //Keep the target field as big as the source field
        var minHeight = $('#sortable-choice-' + questionId).height();
        var minWidth = $('#sortable-choice-' + questionId).width();
        $('#sortable-choice-' + questionId).css('min-height', minHeight);
        $('#sortable-rank-' + questionId).css('min-height', minHeight);
        $('#sortable-choice-' + questionId).css('min-width', minWidth);
        $('#sortable-rank-' + questionId).css('min-width', minWidth);

        // if (samechoiceheight) {
        //     var maxHeight = 0;
        //     $('#sortable-choice-' + questionId + ' li,#sortable-rank-' + questionId + ' li').each(function () {
        //         if ($(this).actual('height') > maxHeight) {
        //             maxHeight = $(this).actual('height');
        //         }
        //     });
        //     $('#sortable-choice-' + questionId + ' li,#sortable-rank-' + questionId + ' li').css('min-height', maxHeight + 'px');
        // }

        // if (samelistheight) {
        //     var totalHeight = 0;
        //     $('#sortable-choice-' + questionId + ' li,#sortable-rank-' + questionId + ' li').each(function () {
        //         totalHeight = totalHeight + $(this).actual('outerHeight', {
        //             includeMargin: true
        //         }); /* Border not inside */
        //     });
        //     /* Add the padding to min-height */
        //     $('#sortable-choice-' + questionId + ',#sortable-rank-' + questionId).css('min-height', totalHeight + 'px').addClass("ls-sameheight");
        // }

    },

    triggerEmRelevanceSortable = function() {
        $(".sortable-item").on('relevance:on', function (event, data) {
            //~ if(event.target != this) return; // not needed now, but after maybe (2016-11-07)
            //~ data = $.extend({style:'hidden'}, data);
            $(event.target).closest(".ls-answers").find("option[value=" + $(event.target).data("value") + "]").prop('disabled', false);
            $(event.target).removeClass("disabled");
        });

        $(".sortable-item").on('relevance:off', function (event, data) {
            //~ if(event.target != this) return; // not needed now, but after maybe (2016-11-07)
            //~ data = $.extend({style:'hidden'}, data);
            $(event.target).closest(".ls-answers").find("option[value=" + $(event.target).data("value") + "]").prop('disabled', true);
            $(event.target).addClass("disabled");
            /* reset already ranked item */
            if ($(event.target).parent().hasClass("sortable-rank")) {
                $(event.target).appendTo($(event.target).closest(".answers-list").find(".sortable-choice"));
                var questionId = $(event.target).closest("[id^='question']").attr('id').replace("question", "");
                updateDragDropRank();
            }
        });
    };

    return {
        init : function(){
            doDragDropRank();
            triggerEmRelevanceSortable();
        }
    }

};
