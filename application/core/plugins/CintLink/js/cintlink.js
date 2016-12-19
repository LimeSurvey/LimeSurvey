/**
 * @since 2016-07-14
 * @author Olle Haerstedt
 */

// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.cintlink = LS.plugin.cintlink || {};

$(document).ready(function() {

    function showLoader() {
        $('.ajaxLoading').show();
    }

    function hideLoader() {
        $('.ajaxLoading').hide();
    }

    /**
     * Run when user click "Login"
     * @param {object} ev - Event
     * @return
     */
    function onLoginClick(ev) {
        ev.preventDefault();

        var formValues = $('#cintlink-login-form').serialize();

        showLoader();

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=login&' + formValues
        }).done(function(response) {
            console.log(response);
            hideLoader();

            var response = JSON.parse(response);

            if (response.error) {
                $('#error-modal .modal-body-text').html(response.error);
                $('#error-modal').modal();
            }
            else if (response.result) {
                // Login OK
                showDashboard();
            }
            else {
                $('#error-modal .modal-body-text').html(LS.plugin.cintlink.lang.couldNotLogin);
                $('#error-modal').modal();
            }
        })
    }

    /**
     * Show the login form if user is not already logged in
     * @return
     */
    function showLoginForm() {
        hideLoader();
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=getLoginForm'
        }).done(function(response) {
            $('#cintlink-container').html(response);

            $('#cintlink-login-submit').on('click', function(ev) {
                onLoginClick(ev);
            });
        });
    }

    /**
     * CintLink widget supports a couple of languages.
     * Get one of those. Default to English.
     * @param {string} surveyLanguage
     * @return {string}
     */
    function getWidgetLanguage(surveyLanguage) {
        var supportedLanguages = [
            'en',
            'de',
            'es',
            'fi',
            'fr',
            'ja',
            'ru',
            'sv'
        ];

        if (supportedLanguages.indexOf(surveyLanguage) === -1) {
            return 'en';
        }
        else {
            return surveyLanguage;
        }
    }

    /**
     * Fetch dashboard HTMl from server and inject it
     * @return
     */
    function showDashboard() {
        showLoader();
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=getDashboard',
            surveyId: LS.plugin.cintlink.surveyId
        }).done(function(response) {
            console.log('response', response);
            hideLoader();
            $('#cintlink-container').html(response);
            doToolTip();

            // It's possible that dashboard will add a notification, e.g. tutorial.
            // Make sure notifications are updated even if we are using Ajax.
            LS.updateNotificationWidget(LS.plugin.cintlink.notificationUpdateUrl, false);

            // If surveyId is defined we have survey specific dashboard
            if (LS.plugin.cintlink.surveyId) {

                if (LS.plugin.cintlink.tourPromise) {
                    LS.plugin.cintlink.tourPromise.then(function() {
                        LS.plugin.cintlink.tour.init();
                        LS.plugin.cintlink.tour.start();
                    });
                }
                /*
                var useLangWizard = $('#cint-lang-wizard input[name="lang"]').length > 1 ;
                var shouldShowWidget = $('#cintlink-gridview table tr').length == 2;

                if (shouldShowWidget) {
                    if (useLangWizard) {
                        showLangWizard();
                    }
                    else {
                        showWidget();
                    }
                }
                */
            }

        });
    }

    /**
     * Show the CintLink widget
     * @param {string|undefined} lang Language to put in the survey link
     * @return
     */
    function showWidget(lang) {

        // Disable tour
        if (LS.plugin.cintlink.tour) {
            LS.plugin.cintlink.tour.end();
        }

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=getSurvey&surveyId=' + LS.plugin.cintlink.surveyId
        }).done(function(response) {
            console.log(response);
            var response = JSON.parse(response);
            console.log('response', response);
            var survey = JSON.parse(response.result);
            console.log('survey', survey);

            var completeLink;
            if (lang === undefined) {
                completeLink = response.link + '&lang=' + response.language;
            }
            else {
                completeLink = response.link + '&lang=' + lang;
            }
            console.log('completeLink', completeLink);
            console.log('response.numberOfQuestions', response.numberOfQuestions);

            var options = {
                locale: getWidgetLanguage(survey.surveyls_language),
                introText: response.loggedIn ? 'LimeSurvey' : response.warningMessage,
                surveyLink: {
                    value: completeLink,
                    readOnly: true
                },
                surveyTitle: {
                    value: survey.surveyls_title,
                    readOnly: true
                },
                contactName: {
                    value: response.name,
                    readOnly: false
                },
                contactEmail: {
                    value: response.email,
                    readOnly: false
                },
                numberOfQuestions: {
                    value: response.numberOfQuestions,
                    readOnly: true
                }
            };

            CintLink.show(options, function(hold, release) {
                // An order was made, and we're going to POST the hold URL back to ourselves
                $.ajax({
                    url: LS.plugin.cintlink.pluginBaseUrl + '&method=purchaseRequest',
                    data: {
                        purchaseRequest: hold,
                        surveyId: LS.plugin.cintlink.surveyId
                    },
                    type: "POST",
                    dataType: "json",
                    success: function(data) {
                        console.log('here');
                        console.log(data);

                        $('#order').text(data.text);

                        orderUrl = data.id;

                        $('#release-order').show();

                        CintLink.close();

                        if (data.error) {
                            showErrorOrSuccessModal(data);
                        }
                        else {
                            showErrorOrSuccessModal({
                                result: LS.plugin.cintlink.lang.orderPlacedOnHold
                            });
                        }
                    }
                });
            });

        });

    }

    /**
     * After e.g. cancellation, depending on response,
     * show either error modal or success modal.
     * Reloads the dashboard.
     * @param {object} response Expected to have response.error or response.result
     * @return
     */
    function showErrorOrSuccessModal(response) {
        if (response.error) {
            $('#error-modal .modal-body-text').html(response.error);
            $('#error-modal').modal();
        }
        else if (response.result) {
            $('#success-modal .modal-body-text').html(response.result);
            $('#success-modal').modal();
        }
        showDashboard();
    }

    /**
     * Cancel an order at Cint
     * @param {string} orderUrl
     * @return
     */
    function cancelOrder(orderUrl) {
        showLoader();

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=cancelOrder',
            data: {orderUrl: orderUrl}
        }).done(function(response) {
            hideLoader();
            console.log(response);
            var response = JSON.parse(response);
            console.log(response);

            showErrorOrSuccessModal(response);
        });
    }

    /**
     * Soft deletes an order so it no longer shows up
     * in dashboard.
     * @param {string} orderUrl
     * @return
     */
    function softDeleteOrder(orderUrl) {
        showLoader();

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=softDeleteOrder',
            data: {
                orderUrl: orderUrl,
                surveyId: LS.plugin.cintlink.surveyId
            }
        }).done(function(response) {
            hideLoader();
            console.log(response);
            var response = JSON.parse(response);
            console.log(response);

            showErrorOrSuccessModal(response);
        });
    }

    /**
     * Run when user clicks 'Pay now'
     * @return
     */
    function userTriedToPay() {
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=userTriedToPay',
            data: {
                surveyId: LS.plugin.cintlink.surveyId
            }
        });
    }

    /**
     * When survey has more than one language, show a
     * popup to let user choose lang before ordering
     * participants.
     * @return
     */
    function showLangWizard() {
        $('#cint-lang-wizard').modal();
    }

    /**
     * Fired when user clicks OK in language wizard
     * @return
     */
    function langWizardOK() {
        var lang = $('#cint-lang-wizard input[name="lang"]:checked').val();
        showWidget(lang);
    }

    // Needs to be accessed from the outside (dashboard view)
    LS.plugin.cintlink.showWidget = showWidget;
    LS.plugin.cintlink.showLoginForm = showLoginForm;
    LS.plugin.cintlink.cancelOrder = cancelOrder;
    LS.plugin.cintlink.softDeleteOrder = softDeleteOrder;
    LS.plugin.cintlink.userTriedToPay = userTriedToPay;
    LS.plugin.cintlink.showDashboard = showDashboard;
    LS.plugin.cintlink.showLangWizard = showLangWizard;
    LS.plugin.cintlink.langWizardOK = langWizardOK;

    // Check if user is logged in on limesurvey.org
    // If yes, show widget
    // If no, show login form
    $.ajax({
        method: 'POST',
        url: LS.plugin.cintlink.pluginBaseUrl + '&method=checkIfUserIsLoggedInOnLimesurveyorg'
    }).done(function(response) {
        console.log(response);

        var response = JSON.parse(response);

        showDashboard();

        /*
        if (response.result)
        {
            // User logged in
            showDashboard();
        }
        else
        {
            // User not logged in, show user form
            showLoginForm();
        }
        */
    });

});
