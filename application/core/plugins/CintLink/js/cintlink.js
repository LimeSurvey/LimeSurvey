/**
 * @since 2016-07-14
 * @author Olle Haerstedt
 */

// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.cintlink = LS.plugin.cintlink || {};

$(document).ready(function() {

    /**
     * Show the login form if user is not already logged in
     *
     * @return void
     */
    function showLoginForm()
    {
        $.ajax({
            method: 'POST',
            url: LS.plugin.cintlink.pluginBaseUrl + '&function=getLoginForm'
        }).done(function(response) {
            $('#cintlink-container').html(response);

            $('#cintlink-login-submit').on('click', function(ev) {
                ev.preventDefault();

                var formValues = $('#cintlink-login-form').serialize();

                $.ajax({
                    method: 'POST',
                    url: LS.plugin.cintlink.pluginBaseUrl + '&function=login&' + formValues
                }).done(function(response) {
                    console.log(response);

                    var response = JSON.parse(response);

                    if (response.error) {
                        $('#error-modal .modal-body-text').html(response.error);
                        $('#error-modal').modal();
                    }
                    else if (response.result) {
                        // Login OK, show widget
                        showWidget();
                    }
                    else {
                        $('#error-modal .modal-body-text').html("Could not login. Please make sure username and password is correct.");
                        $('#error-modal').modal();
                    }

                });

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
    function getWidgetLanguage(surveyLanguage)
    {
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
     * Show the CintLink widget
     *
     * @return void
     */
    function showWidget()
    {
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
                    value: "http://mysurveycompany.example.com/takesurvey/15",
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
                console.log("purchase was made");
                $.ajax({
                    url: LS.plugin.cintlink.pluginBaseUrl + '&function=purchaseRequest',
                    data: {purchaseRequest: hold},
                    type: "POST",
                    dataType: "json",
                    success: function(data) {
                        $('#order').text(data.text);
                        orderUrl = data.id;
                        $('#release-order').show();
                        CintLink.close();
                    }
                });
            });

        });

    }

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
            // User logged in, show Cint widget
            showWidget();
        }
        else
        {
            // User not logged in, show user form
            showLoginForm();
        }
    });

});
