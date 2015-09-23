/**
 Ajax helpers for LimeSurvey.
 Must be loaded after jQuery.
 */

(function($) {

    $(document).on('click', '.ajaxSubmit', function(e) {
        var $form = $(this).closest('form').data('button', $(this));
        return;
    });
    $(document).on('submit', 'form', function(e) {
        var $form = $(this);
        if (typeof $form.data('button') != 'undefined') {
            e.preventDefault();
            var $button = $form.data('button');
            $form.removeData('button');
            ajaxSubmit($form, $button);
        }
        return;
    });

    var ajaxSubmit = function($form, $button) {
        $button.addClass('ajaxBusy').attr('disabled', true);

        // Get submit URL.
        var url = $button.attr('formaction') || $form.attr('action');
        var method = $form.attr('method');
        /**
         * This allows file uploads via AJAX as well.
         * @type {FormData}
         */
        var data = new FormData($form[0]);
        // Add name of the submit button.
        data.append($button.attr('name'), 1);

        $.ajax(url, {
            "data": data,
            "method": method,
            "contentType": false,
            "processData": false,
            "dataType": "json",
            "complete" : function() {
                $button.removeClass('ajaxBusy').attr('disabled', false);
            }

        });
    };

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
