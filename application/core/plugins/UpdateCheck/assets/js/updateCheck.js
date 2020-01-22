$(document).ready(function() {
    var url = LS.plugin.updateCheck.url;
    var notificationUpdateUrl = LS.plugin.updateCheck.notificationUpdateUrl;
    $.ajax({
        url: url,
        data: {},
        method: 'GET',
        success: function(response) {
            console.ls.log(response);
            LS.updateNotificationWidget(notificationUpdateUrl, false);
        },
        error : function(arguments) {
            console.ls.log(arguments);
        }
    });
});
