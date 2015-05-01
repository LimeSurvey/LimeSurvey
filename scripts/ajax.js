/**
 Ajax helpers for LimeSurvey.
 Must be loaded after jQuery.
 */

(function($) {

    $(document).on('click', '.ajaxSubmit', function(e) {
        var $this = $(this);
        $this.addClass('ajaxBusy').attr('disabled', true);
        var $form = $this.closest('form');
        // Prevent regular submit.
        e.preventDefault();

        // Get submit URL.
        var url = $this.attr('formaction') || $form.attr('action');
        var method = $form.attr('method');
        /**
         * This allows file uploads via AJAX as well.
         * @type {FormData}
         */
        var data = new FormData($form[0]);
        // Add name of the submit button.
        data.append($(this).attr('name'), 1);

        $.ajax(url, {
            "data": data,
            "method": method,
            "contentType": false,
            "processData": false,
            "dataType": "json",
            "complete" : function() {
                $this.removeClass('ajaxBusy').attr('disabled', false);
            }

        });
    });

    $(document).on('ajaxComplete', function(e, jqXHR, ajaxOptions) {
        console.log("Ajax success.");
        if (typeof jqXHR.responseJSON != 'undefined' && typeof jqXHR.responseJSON.alerts != 'undefined') {
            for (var type in jqXHR.responseJSON.alerts) {
                $.notify({
                    // options
                    message: jqXHR.responseJSON.alerts[type]
                }, {
                    // settings
                    type: type == 'error' ? 'danger' : type
                });
            }
        }


    });
    $(document).on('ajaxError', function(e) {
        console.log("Ajax error.");
    })
})(jQuery);
