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

    // Old save button, not used
    /*
    $('#btnSave').click(function(){
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
        frmOrganize.submit();
    })
    */

    // Save
    $('#organizebar-save-btn').click(function(){
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
        $('#close-after-save').val('false');
        frmOrganize.submit();
    })

    // Save and close
    $('#organizebar-save-and-close-btn').click(function(){
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
        $('#close-after-save').val('true');
        frmOrganize.submit();
    })

});
