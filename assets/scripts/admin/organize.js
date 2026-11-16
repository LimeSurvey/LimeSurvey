
// Namespace
var LS = LS || {
    onDocumentReady: {}
};
var formSubmitting = false;
var changed = false;

$(document).on('ready  pjax:scriptcomplete', function(){
    /* @var boolean did we disable parent change , only on ol.organizer in page */
    let disableParentChange = $('ol.organizer').data('disableparentchange');
    $('ol.organizer').nestedSortable({
        disableParentChange: disableParentChange,
        doNotClear: true,
        disableNesting: 'no-nest',
        disableNestingClass: 'no-nest',
        forcePlaceholderSize: true,
        handle: 'div',
        helper: 'clone',
        items:  'li',
        maxLevels: 2,
        opacity: .6,
        placeholder: 'placeholder',
        revert: 250,
        tabSize: 25,
        rootID: 'root',
        protectRoot: true,
        isTree: true,
        startCollapsed: true,
        stop: function(event, ui) {
            var itemLevel = $(ui.item).attr('data-level');
            var listLevel = $(ui.item).closest('ol').attr('data-level');
            if (itemLevel != listLevel) {
                $('ol.organizer').nestedSortable('cancel');
            }
            updateMoveButtons();
        },
        change: function(event, ui) {
            changed = true;
            if (typeof ui.item != 'undefined' && typeof ui.placeholder != 'undefined') {
                var itemLevel = $(ui.item).attr('data-level');
                var listLevel = $(ui.placeholder).closest('ol').attr('data-level');
                if (itemLevel != listLevel) {
                    $('.placeholder').addClass('ui-nestedSortable-error');
                }
                else {
                    $('.placeholder').removeClass('ui-nestedSortable-error');
                }
            }
        },
        tolerance: 'pointer',
        toleranceElement: '> div'
    });

    // Move up / move down buttons: keyboard and single-pointer alternative to dragging
    $('ol.organizer').off('click.organizerMove').on('click.organizerMove', '.organizer-move', function() {
        moveItem($(this));
    });
    updateMoveButtons();

    $('.organizer .disclose').each(function() {
        updateDiscloseButton($(this));
    });

    $('.disclose').on('click', function() {
        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
        updateDiscloseButton($(this));
    });

    $('#btnSave').click(function(){
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
        frmOrganize.submit();
    });

    // Collapse all question groups
    $('#organizer-collapse-all').on('click', function() {
        $('.organizer').find('.mjs-nestedSortable-expanded').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded')
            .find('> .card-header .disclose').each(function() {
                updateDiscloseButton($(this));
            });
    });

    // Expand all question groups
    $('#organizer-expand-all').on('click', function() {
        $('.organizer').find('.mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded')
            .find('> .card-header .disclose').each(function() {
                updateDiscloseButton($(this));
            });
    });

    /**
     * Move a group or question one position up or down. A question at the edge
     * of its group moves into the neighbouring group, unless the survey is active.
     *
     * @param {jQuery} $button the clicked .organizer-move button
     * @return {void}
     */
    function moveItem($button) {
        var up = $button.data('direction') === 'up';
        var $item = $button.closest('li[data-level]');
        var $sibling = up ? $item.prev('li') : $item.next('li');
        var movedToOtherGroup = false;

        if ($sibling.length) {
            if (up) {
                $item.insertBefore($sibling);
            } else {
                $item.insertAfter($sibling);
            }
        } else if ($item.data('level') === 'question' && !disableParentChange) {
            var $group = $item.closest('li[data-level="group"]');
            var $targetGroup = up ? $group.prev('li') : $group.next('li');
            var $targetList = $targetGroup.children('ol.question-list');
            if (!$targetList.length) {
                return;
            }
            if (up) {
                $targetList.append($item);
            } else {
                $targetList.prepend($item);
            }
            if ($targetGroup.hasClass('mjs-nestedSortable-collapsed')) {
                $targetGroup.removeClass('mjs-nestedSortable-collapsed').addClass('mjs-nestedSortable-expanded');
                updateDiscloseButton($targetGroup.find('> .card-header .disclose'));
            }
            movedToOtherGroup = true;
        } else {
            return;
        }

        changed = true;
        updateMoveButtons();

        // Moving the item in the DOM drops focus, so put it back. If this button
        // just became disabled (item reached the edge) use the opposite one.
        var $focusTarget = $button.prop('disabled') ? $button.siblings('.organizer-move') : $button;
        $focusTarget.trigger('focus');

        var $siblings = $item.parent().children('li');
        var $liveRegion = $('#organizer-live-region');
        var message = movedToOtherGroup
            ? formatMessage($liveRegion.data('moved-group'), [
                $item.closest('li[data-level="group"]').find('> .card-header .organizer-group-name').text().trim(),
                $siblings.index($item) + 1,
                $siblings.length
            ])
            : formatMessage($liveRegion.data('moved-position'), [$siblings.index($item) + 1, $siblings.length]);
        $liveRegion.text('');
        // Change the content in a later tick so repeated identical messages are announced again
        setTimeout(function() {
            $liveRegion.text(message);
        }, 100);
    }

    /**
     * Disable move buttons that would move an item past the start or end of the list.
     *
     * @return {void}
     */
    function updateMoveButtons() {
        var $groups = $('ol.organizer > li[data-level="group"]');
        $groups.each(function(groupIndex) {
            var $group = $(this);
            var isFirstGroup = groupIndex === 0;
            var isLastGroup = groupIndex === $groups.length - 1;
            $group.find('> .card-header .organizer-move[data-direction="up"]').prop('disabled', isFirstGroup);
            $group.find('> .card-header .organizer-move[data-direction="down"]').prop('disabled', isLastGroup);

            var $questions = $group.find('> ol.question-list > li[data-level="question"]');
            $questions.each(function(questionIndex) {
                var $question = $(this);
                var canLeaveGroup = !disableParentChange;
                $question.find('.organizer-move[data-direction="up"]')
                    .prop('disabled', questionIndex === 0 && (isFirstGroup || !canLeaveGroup));
                $question.find('.organizer-move[data-direction="down"]')
                    .prop('disabled', questionIndex === $questions.length - 1 && (isLastGroup || !canLeaveGroup));
            });
        });
    }

    /**
     * Fill the numbered %1$s, %2$s, ... placeholders of a translated message.
     *
     * @param {string} template the translated message
     * @param {Array} values placeholder values, in placeholder order
     * @return {string} the formatted message
     */
    function formatMessage(template, values) {
        return String(template).replace(/%(\d+)\$s/g, function(match, position) {
            var value = values[position - 1];
            return value === undefined ? match : String(value);
        });
    }

    /**
     * Update a disclose button's icon, aria-expanded and aria-label to reflect
     * the collapsed/expanded state of the group it belongs to.
     *
     * @param {jQuery} $discloseButton the .disclose button to update
     * @return {void}
     */
    function updateDiscloseButton($discloseButton) {
        var isCollapsed = $discloseButton.closest('li').hasClass('mjs-nestedSortable-collapsed');
        $discloseButton
            .toggleClass('ri-arrow-right-s-fill', isCollapsed)
            .toggleClass('ri-arrow-down-s-fill', !isCollapsed)
            .attr('aria-expanded', isCollapsed ? 'false' : 'true')
            .attr('aria-label', isCollapsed ? $discloseButton.data('label-expand') : $discloseButton.data('label-collapse'));
    }
});

/**
 * Show confirmation message when user leaves without saving
 */
// Don't show the confirmation prompt for now. It doesn't work when "leaving" through PJAX,
// and it interferes with save.
/*window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
        if (formSubmitting) {
            return undefined;
        }

        if(changed == true) {
            var confirmationMessage = $('#didChange').data('message');

            (e || window.event).returnValue = confirmationMessage; //Gecko + IE
            return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        }
    });
}*/

/**
 * Fix big question part
 */
/** Update class when click on hide-button */
$(document).on("click",".question-item .hide-button",function(e){
    e.preventDefault();
    e.stopPropagation();
    $(this).closest(".question-item").toggleClass("stretched").toggleClass("opened").toggleClass("dropup");
});
/** Show the button only if needed */
/** Maybe brok if there are a lot of question : hide it when click ?*/
$(function() {
  $(".question-item").each(function(){
    var element = $(this).get(0);
    if(element.scrollHeight <= element.clientHeight)
    {
        $(this).find(".hide-button").addClass("invisible").css("visibility","hidden"); // See bug #10365
    }
  });
});
