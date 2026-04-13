$(document).ready(function () {
    var url = LS.plugin.updateCheck.url;
    var notificationUpdateUrl = LS.plugin.updateCheck.notificationUpdateUrl;
    $.ajax({
        url: url,
        data: {},
        method: 'GET',
        success: function (response) {
            console.log(response);
            LS.updateNotificationWidget(notificationUpdateUrl);
        },
        error : function (arguments) {
            console.log(arguments);
        }
    });
});
