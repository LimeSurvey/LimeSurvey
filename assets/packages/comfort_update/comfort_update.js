'use strict';
document.addEventListener('DOMContentLoaded', function () {
    $('#update-alert').on('closed.bs.alert', function () {
        $.ajax({
            url: $(this).attr('data-url-notification-state'),
            type: 'GET',
            success: function () {
                // success, do nothing
            },
            error: function () {
                // error, do nothing
            },
        });
    });

});