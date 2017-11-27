$(document).on('ready pjax:scriptcomplete', function(){
    if($('.side-body').length){
        $('#notif-container .alert').attr('style', 'margin-top: 20px');
        if($('#vue-app-main-container').length>0)
        {
            //$('#notif-container .alert').prependTo('.side-body');
            //$('#notif-container .alert').before('#vue-app-main-container');
        }
        else
        {
            $('#notif-container .alert').prependTo('.side-body');
        }
    }

    if($('.login-content').length){
        $('#notif-container .alert').prependTo('.login-content-form');
    }

    $('#update-alert').on('closed.bs.alert', function ()
    {
        if (!$(this).hasClass("unstable-update"))
        {
            $('.update-small-notification').removeClass('hidden');
        }

        $.ajax({
            url: $(this).attr('data-url-notification-state'),
            type: 'GET',
            success: function(html) {
            },
            error :  function(html, statut){
            },

        });

    });

        if($('#absolute_notification').length){
            $('#update-container').css({
               'margin-top': '0px',
            });
        }


});

window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
}, 4000);
