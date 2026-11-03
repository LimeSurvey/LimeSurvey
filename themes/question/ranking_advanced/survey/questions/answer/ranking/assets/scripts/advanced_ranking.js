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
        samechoiceheight = (typeof options.samechoiceheight != 'undefined') ? parseInt(options.samechoiceheight) : 1,
        samelistheight = (typeof options.samelistheight != 'undefined') ? parseInt(options.samelistheight) : 1,
        numbersActive = options.numbersActive,
        withHandle = options.withHandle;

    //define reused variables
    var relevancename= "relevance"+rankingName,
        rankingID = "javatbd" + rankingName;

    // Build a map from data-value (title) to element id from the DOM
    var buildValueToIdMap = function () {
        var map = {};
        $('#sortable-choice-' + questionId + ' .sortable-item').each(function () {
            var val = $(this).data('value');
            if (val) {
                map[val] = $(this).attr('id');
            }
        });
        return map;
    };

    //define HTML snippets
    var screenReader = "<div class='visually-hidden'>" + $('#question' + questionId + ' .em_default').html() + "</div><div aria-hidden='true'>" + LSvar.lang.rankadvancedhelp + "</div>"

    //define functions
    var createSorting = function(){
        //Add a class to the question
        $('#question' + questionId + '').addClass('sortable-activated');
        // Set up the connected sortable
        var sortableObjectChoice = {
            group: "sortable-" + questionId,
            ghostClass: "ls-rank-placeholder",
            onEnd: function(){
                updateRankingNumber();
            },
            onMove: function (ev) {
                if (max_answers > 0 && $('#sortable-rank-' + questionId + ' li').length >= max_answers) {
                    return false;
                }
            }
        },
        sortableObjectRank = {
            group: "sortable-" + questionId,
            ghostClass: "ls-rank-placeholder",
            onEnd: function(){
                updateRankingNumber();
            },
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


        Sortable.create(document.getElementById('sortable-choice-' + questionId), sortableObjectChoice);
        Sortable.create(document.getElementById('sortable-rank-' + questionId), sortableObjectRank);

        $('#question' + questionId + ' .ls-remove').remove();
        // Adapt choice and list height
        fixChoiceListHeight();
        $(window).on('resize.advancedRanking' + questionId, fixChoiceListHeight);
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
    
    /**
     * Show the rank number on ranked items only; items still in the choice list have no rank
     */
    updateRankingNumber= function () {
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function (index,item) {
            $(item).find('.selector__index_row').text('#' + (index + 1) + ' ');
        });
        $('#sortable-choice-' + questionId + ' .answer-item.sortable-item.ls-choice .selector__index_row').text('');
    },
    /**
     * Update answers after updating drag and drop part
     */
    updateDragDropRank = function() {
        $('#question' + questionId + ' .select-item select').val('');
        if (numbersActive === 1) {
            updateRankingNumber();
        }
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function (index,item) {
           var valToSet = $(this).data("value");
           var $targetSelect = $('#question' + questionId + ' .select-item select').eq(index);
        
            if ($targetSelect.length > 0) {
                $targetSelect.val(valToSet).trigger("change", { source: 'dragdrop' });
            }
        });

        // Update #relevance and launch checkconditions function
        $("[id^=" + relevancename + "]").val('0');

        $('#question' + questionId + ' .select-item select:lt(' + max_answers + ')').each(function (index) {
            if ($(this).val() != "") {
                $("#" + relevancename + (index+1) ).val("1");
            }
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
    loadDragDropRank = function () {
        // Update #relevance
        $("[id^=" + relevancename + "]").val('0');
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').each(function () {
            $(this).appendTo('#sortable-choice-' + questionId );
        });
        var valueToId = buildValueToIdMap();
        $('#question' + questionId + ' .select-item select :selected').each(function (index) {
            if ($(this).val() != '') {
                $("#" + relevancename + (index+1)).val("1");
                var elId = valueToId[$(this).val()];
                if (elId) {
                    $('#sortable-choice-' + questionId + ' #' + elId).appendTo('#sortable-rank-' + questionId);
                }
            }
        });

        updateDragDropRank(); // Update to reorder select

        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice').removeClass("error");
        $('#sortable-choice-' + questionId + ' .answer-item.sortable-item.ls-choice').removeClass("error");
        $('#sortable-rank-' + questionId + ' .answer-item.sortable-item.ls-choice:gt(' + (max_answers * 1 - 1) + ')').addClass("error");
    },

    /**
     * Apply the "Same height for all answer options" and "Same height for lists" settings
     * Called at load and on window resize, so it resets the previous values before measuring
     */
    fixChoiceListHeight = function() {
        var $choices = $('#question' + questionId + ' .sortable-item.ls-choice'),
            $lists = $('#sortable-choice-' + questionId + ',#sortable-rank-' + questionId);
        //No fixed width: The lists already fill their column, a pixel width would overflow once the viewport gets narrower
        $choices.css('min-height', '');
        $lists.css('min-height', '');

        if (samechoiceheight) {
            var maxChoiceHeight = 0;
            $choices.filter(':not(.ls-hidden)').each(function () {
                maxChoiceHeight = Math.max(maxChoiceHeight, getMinHeightBoxHeight($(this)));
            });
            $choices.css('min-height', maxChoiceHeight + 'px');
        }

        if (samelistheight) {
            //Keep both lists as high as the higher one, at load the rank list is empty: It gets the height of all choices
            var maxListHeight = 0;
            $lists.each(function () {
                maxListHeight = Math.max(maxListHeight, getMinHeightBoxHeight($(this)));
            });
            $lists.css('min-height', maxListHeight + 'px');
        }
    },

    /**
     * Height of the box that min-height applies to, depending on box-sizing
     * @param {jQuery} $element
     * @return {number}
     */
    getMinHeightBoxHeight = function($element) {
        return ($element.css('box-sizing') == 'border-box') ? $element.actual('outerHeight') : $element.actual('height');
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
