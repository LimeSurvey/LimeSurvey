
$(document).on('ready pjax:scriptcomplete', function(){
    $('#selector_activateFacebookSharing').on('click', function(e){
        e.preventDefault();
        $('.selector_fb_share_disabled').addClass('d-none');
        $('.selector_fb_share').removeClass('d-none');
        $('.selector_fb_share').each(function(i,item){
            $(item).attr('src', $(item).data('src'));
        });
    });

    $('.selector__qrcode_trigger').on('click', function(e){
        e.preventDefault();
        var container = new QRCode($(this).closest('.selector__qrcode_container').find('.selector__qrcode')[0],{
            text: $(this).closest('.selector__qrcode_container').find('.selector__qrcode').data('url'),
            width: 128,
            height: 128,
            colorDark : "#000000",
            colorLight : "#ffffff",
        });
        $(this).css('display','none');
    })
});
