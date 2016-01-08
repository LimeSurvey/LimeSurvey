$(document).ready(function(){
    if($('.side-body').length){
        $('#notif-container .alert').attr('style', 'margin-top: 20px');
        $('#notif-container .alert').prependTo('.side-body');
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

        if($('.side-body').length){
            $('#update-container .alert').attr('style', 'margin-top: 20px');
            $('#update-container .alert').prependTo('.side-body');
        }

});
