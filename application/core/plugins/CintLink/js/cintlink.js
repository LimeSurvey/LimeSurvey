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
     * Show the CintLink widget
     *
     * @return void
     */
    function showWidget()
    {
        console.log("showWidget");
        var options = {
            locale: "en",
            introText: "My Survey Company Name",
            surveyLink: {
                value: "http://mysurveycompany.example.com/takesurvey/15",
                readOnly: true
            },
            surveyTitle: {
                value: "My Survey",
               readOnly: true
            }
        };

        CintLink.show(options, function(hold, release) {
            // A purchase was made, and we're going to POST the hold URL back to ourselves
            console.log("purchase was made");
            /*
            $.ajax("<?php echo addslashes(htmlentities($_SERVER['PHP_SELF'])); ?>", {
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
            */
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
