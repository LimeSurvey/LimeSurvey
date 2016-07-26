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

    hideLoader();

    /**
     * Run when user click "Login"
     *
     * @param {object} ev - Event
     * @return
     */
    function onLoginClick(ev) {
        ev.preventDefault();

        var formValues = $('#cintlink-login-form').serialize();

        showLoader();

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=login&' + formValues
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
     *
     * @return
     */
    function showLoginForm() {
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=getLoginForm'
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
     * 
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
     *
     * @return
     */
    function showDashboard() {
        showLoader();
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=getDashboard',
            surveyId: LS.plugin.cintlink.surveyId
        }).done(function(response) {
            console.log('response', response);
            hideLoader();
            $('#cintlink-container').html(response);
        });
    }

    /**
     * Fetch nBill order form HTML and put it in iframe
     *
     * @return
     */
    function showNBillOrderForm() {
        console.log('showNBillOrderForm');
        //$('#cintlink-iframe').attr('src', "https://www.limesurvey.org/index.php?option=com_nbill&action=orders&task=order&cid=10&tmpl=component");
        //$('#cintlink-iframe').toggleClass('hidden');
        return;
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=getNBillOrderForm'
        }).done(function(response) {
            //console.log('response', response);
            var response = JSON.parse(response);

            // If we use an iframe, we can't hack into the form and submit it using curl

            /*
            var orderForm = $(response.result);
            $('#cintlink-container').html($('#order_form', orderForm));
            $('input[name="ctl_next_1"]').attr('class', 'btn btn-default');
            $('.componentheading').hide();
            $('input[name="ctl_next_1"]').on('click', function(ev) {
                ev.preventDefault();
                var formValues = $('form[name="order_form"]').serialize();
                console.log('formValues', formValues);

                // Submit form using Ajax
                $.ajax({
                     method: 'POST',
                     url: LS.plugin.cintlink.pluginBaseUrl + '&function=submitFirstNBillPage',
                     data: {
                        'formValues': formValues
                     }
                }).done(function(response) {
                    console.log('response', response);
                    var response = JSON.parse(response);
                    $('#cintlink-iframe').attr('src', 'data:text/html;charset=utf-8,' + response.result);
                    $('#cintlink-iframe').toggleClass('hidden');
                });

            });
            */
        });
    }

    /**
     * Show the CintLink widget
     *
     * @return
     */
    function showWidget() {
        console.log("showWidget");

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=getSurvey&surveyId=' + LS.plugin.cintlink.surveyId
        }).done(function(response) {
            console.log(response);
            var response = JSON.parse(response);
            console.log('response', response);
            var survey = JSON.parse(response.result);
            console.log('survey', survey);

            var options = {
                locale: getWidgetLanguage(survey.surveyls_language),
                introText: "LimeSurvey",
                surveyLink: {
                    value: response.link,
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
                }
            };

            CintLink.show(options, function(hold, release) {
                // A purchase was made, and we're going to POST the hold URL back to ourselves
                $.ajax({
                    url: LS.plugin.cintlink.pluginBaseUrl + '&function=purchaseRequest',
                    data: {
                        purchaseRequest: hold,
                        surveyId: LS.plugin.cintlink.surveyId
                    },
                    type: "POST",
                    dataType: "json",
                    success: function(data) {
                        console.log(data);

                        $('#order').text(data.text);

                        orderUrl = data.id;

                        $('#release-order').show();

                        CintLink.close();

                        showErrorOrSuccessModal({
                            result: LS.plugin.cintlink.lang.orderPlacedOnHold
                        });
                    }
                });
            });

        });

    }

    /**
     * After e.g. cancellation, depending on response,
     * show either error modal or success modal.
     * Reloads the dashboard.
     *
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
            showDashboard();
        }
    }

    /**
     * Cancel an order at Cint
     *
     * @param {string} orderUrl
     * @return
     */
    function cancelOrder(orderUrl) {
        showLoader();

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=cancelOrder',
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
     *
     * @param {string} orderUrl
     * @return
     */
    function softDeleteOrder(orderUrl) {
        showLoader();

        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=softDeleteOrder',
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

    // Needs to be accessed from the outside (dashboard)
    LS.plugin.cintlink.showWidget = showWidget;
    LS.plugin.cintlink.cancelOrder = cancelOrder;
    LS.plugin.cintlink.softDeleteOrder = softDeleteOrder;

    // Check if user is logged in on limesurvey.org
    // If yes, show widget
    // If no, show login form
    $.ajax({
        method: 'POST',
        url: LS.plugin.cintlink.pluginBaseUrl + '&function=checkIfUserIsLoggedInOnLimesurveyorg'
    }).done(function(response) {
        console.log(response);

        var response = JSON.parse(response);

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
    });

});
