
$(document).on('ready pjax:scriptcomplete', function(){
    $('#selector_activateFacebookSharing').on('click', function(e){
        e.preventDefault();
        $('.selector_fb_share_disabled').addClass('hidden');
        $('.selector_fb_share').removeClass('hidden');
        $('.selector_fb_share').each(function(i,item){
            $(item).attr('src', $(item).data('src'));
        });
    });
});