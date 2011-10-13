$(document).ready(function(){
    var sourceItem;
    $('ol.organizer').nestedSortable({
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
        stop: function(event, ui) {
            if (ui.item[0].sourceLevel!=ui.placeholder.destinationLevel)
               $('ol.organizer').nestedSortable('cancel');
        },
        change: function(event, ui) {
            if (typeof ui.item[0] != 'undefined' && typeof ui.placeholder != 'undefined')
            {
                 if (ui.item[0].sourceLevel!=ui.placeholder.destinationLevel)
                 {
                 $('.placeholder').addClass('ui-nestedSortable-error');
                 }
            }
        },
        tolerance: 'pointer',
        toleranceElement: '> div'
    });

    $('#btnSave').click(function(){
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
        frmOrganize.submit();
    })

});
