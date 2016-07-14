/**
 * @since 2016-07-14
 * @author Olle Haerstedt
 */

// Namespace
var LS = LS || {};
LS.plugin = LS.plugin || {};
LS.plugin.cintlink = LS.plugin.cintlink || {};

$(document).ready(function() {

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
                        // Login OK
                    }
                    else {
                        $('#error-modal .modal-body-text').html("Could not login. Please make sure username and password is correct.");
                        $('#error-modal').modal();
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
        }
        else
        {
            // User not logged in, show user form
            showLoginForm();
        }
    });

});
