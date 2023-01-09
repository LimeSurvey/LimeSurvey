$(document).ready(function () {
    // Show alert
    const alert = '<p class="alert alert-warning ">' + GoogleOAuthSMTP.alertText + '</p>';
    $('#email > .container > .row').prepend($(alert));

    // Disable items
    $('#emailmethod label').addClass('disabled');
    $('#emailsmtphost').prop('readonly', true);
    $('#emailsmtpuser').prop('readonly', true);
    $('#emailsmtppassword').prop('readonly', true);
    $('#emailsmtpssl label').addClass('disabled');

    // Set up tooltips
    ['#emailmethod', '#emailsmtphost', '#emailsmtpuser', '#emailsmtppassword', '#emailsmtpssl'].forEach(selector => {
        new bootstrap.Tooltip($(selector).get(0), {
            'title': GoogleOAuthSMTP.tooltipText,
            'html': true
        });
    });
});