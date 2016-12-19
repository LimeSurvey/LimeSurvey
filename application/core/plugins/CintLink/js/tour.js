/**
 * Interactive tour as introduction
 * @since 2016-08-26
 */
$(document).ready(function() {
    // Fetch translated texts from server
    LS.plugin.cintlink.tourPromise = $.ajax({
        url: LS.plugin.cintlink.pluginBaseUrl + '&method=getTourTexts',
        success: function(response) {
            var response = JSON.parse(response);
            var texts = response.result;
            LS.plugin.cintlink.tour = new Tour({
                name: 'limesurvey-cintlink-tour',
                //storage: false,  // Enable this for debugging
                backdrop: true,
                //debug: true,
                onEnd: function() {
                    /*
                    $.ajax({
                        url: LS.plugin.cintlink.pluginBaseUrl + '&method=setTutorial',
                        surveyId: LS.plugin.cintlink.surveyId,
                        success: function(response) {
                            LS.updateNotificationWidget(LS.plugin.cintlink.notificationUpdateUrl, false);
                        }
                    });
                    */
                },
                steps: [
                {
                    element: '#cintlink-header',
                    title: texts.welcome.title,
                    content: texts.welcome.content,
                    placement: 'bottom'
                },
                {
                    element: '#cintlink-widget-button',
                    title: texts.widget.title,
                    content: texts.widget.content
                },
                {
                    element: '#cintlink-login-button',
                    title: texts.login.title,
                    content: texts.login.content
                },
                {
                    element: '#cintlink-gridview',
                    title: texts.orders.title,
                    content: texts.orders.content,
                    placement: 'top'
                },
                {
                    element: '#ls-activate-survey',
                    title: texts.activate.title,
                    content: texts.activate.content
                },
                {
                    element: '#cintlink-widget-button',
                    title: texts.getstarted.title,
                    content: texts.getstarted.content,
                    onShow: function() {
                        // Enable pointer events again
                        $('#cintlink-widget-button').css('pointer-events', 'all');
                    }
                }
                /*
                {
                    element: '#cintlink-status-column',
                    title: texts.payment.title,
                    content: texts.payment.content,
                    placement: 'top'
                },
                {
                    element: '#cintlink-refresh-button',
                    title: texts.refresh.title,
                    content: texts.refresh.content,
                    placement: 'left'
                },
                {
                    element: '#cintlink-sidemenu-button',
                    title: texts.sidemenu.title,
                    content: texts.sidemenu.content
                },
                {
                    element: '#ls-activate-survey',
                    title: texts.activate.title,
                    content: texts.activate.content
                }
                */
            ]});
        }
    });
});
