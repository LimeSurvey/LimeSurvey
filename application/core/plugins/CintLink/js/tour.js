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
            ]});
        }
    });
});
