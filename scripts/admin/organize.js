var formSubmitting = false;
var changed = false;
$(document).ready(function(){
    var sourceItem;
    $('ol.organizer').nestedSortable({
        doNotClear: true,
        disableNesting: 'no-nest',
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
        stop: function(event, ui) {
            var itemLevel = $(ui.item).attr('data-level');
            var listLevel = $(ui.item).closest('ol').attr('data-level');
            if (itemLevel != listLevel) {
                $('ol.organizer').nestedSortable('cancel');
            }
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

    $('#btnSave').click(function(){
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
        frmOrganize.submit();
    });
});


window.onload = function() {
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
}

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
