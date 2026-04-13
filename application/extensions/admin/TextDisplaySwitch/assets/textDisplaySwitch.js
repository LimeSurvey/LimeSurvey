$(document).on('ready pjax:scriptcomplete', function(){

    $('.selector__toggle_full_text').find('label').on('click', function(){
        "use strict";
        var $targetItem = $($(this).data('target'));
        var state = $(this).find('input').prop('checked');
        $(this).find('input').prop('checked',!state);
                
        $targetItem.toggleClass('d-none');
        // $(this).find('span').toggleClass('d-none');
        $(this).toggleClass('d-none');
    });

});
