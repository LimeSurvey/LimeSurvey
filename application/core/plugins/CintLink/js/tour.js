/**
 * Interactive tour as introduction
 * @since 2016-08-26
 */
LS.plugin.cintlink.tour = new Tour({
    name: 'limesurvey-cintlink-tour',
    storage: false,
    backdrop: true,
    debug: true,
    steps: [
    {
        element: '#cintlink-header',
        title: 'Welcome to the CintLink Tour',
        content: 'This is a short guided tour that will introduce you to the LimeSurvey CintLink plugin. For more detailed information, please visit the <a target="_blank" href="https://manual.limesurvey.org">LimeSurvey manual</a>.',
        placement: 'bottom'
    },
    {
        orphan: true,
        title: 'blaha',
        content: 'blasdfs'
    },
    {
        element: '#cintlink-widget-button',
        title: 'Cint widget',
        content: 'This button opens the Cint widget. From here you can order participant and decide what target group you want.'
    },
    {
        element: '#cintlink-login-button',
        title: 'Login',
        content: 'To be able to order participant, you must first login to <a href="https://www.limesurvey.org">limesurvey.org</a>. If you don\'t have an account, you can register one <a target="_blank" href="https://www.limesurvey.org/cb-registration/registers">here</a>. It\'s completely free.'
    },
    {
        element: '#cintlink-gridview',
        title: 'Orders',
        content: 'Your orders will be listed here. They can be in six different states:<br/><ul><li>Waiting for payment</li><li>Under review</li><li>Live</li><li>Completed</li><li>Cancelled</li><li>Denied</li></ul>',
        placement: 'top'
    },
    {
        element: '#cintlink-status-column',
        title: 'Orders',
        content: 'When your order is waiting for payment, you will see this button:<br/><button class="btn btn-default btn-sm"><span class="fa fa-credit-card"></span>&nbsp;Pay now</button><br/>Clicking this will take you to <a target="_blank" href="https://www.limesurvey.org">limesurvey.org</a> for the payment procedure.',
        placement: 'top'
    },
    {
        element: '#cintlink-refresh-button',
        title: 'Refresh',
        content: 'Don\'t forget to refresh or reload the page after you\'ve paid.',
        placement: 'left'
    },
    {
        element: '#cintlink-sidemenu-button',
        title: 'Side-menu',
        content: 'Your orders will also be listed here'
    },
    {
        element: '#ls-activate-survey',
        title: 'Activate survey',
        content: 'Before ordering participants from Cint you should make sure your survey is completed and activated',
    }
]});
