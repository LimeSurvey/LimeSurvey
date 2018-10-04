$(document).ready(function() {
    var url = LS.plugin.updateCheck.url;
    $.ajax({
        url: url,
        data: {},
        method: 'GET',
        success: function(response) {
            console.ls.log(response);
            //LS.updateNotificationWidget();
        },
        error : function(arguments) {
            console.ls.log(arguments);
        }
    });
});
