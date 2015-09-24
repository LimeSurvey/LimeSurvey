/**
 * This file adds support for unobtrusive javascript.
 */
(function($) {

    var handlers = {};
    // This handles the click event for an anchor tag.
    handlers.A = function() {
        if (typeof this.data('method') != 'undefined') {
            var $form = $('<form/>')
                .attr('method', 'post')
                .append($('<input name="_method"/>').attr('value', this.data('method')))
                .attr('action', this.attr('href'))
                .submit();
        } else {
            window.location.href = this.attr('href');
        }
    };

    // This handles the click event for a button tag.
    handlers.BUTTON = function() {
        // Get the form.
        if (typeof(this.attr('form')) != 'undefined') {
            var $form = $('#' + this.attr('form'));
        } else if (this.closest('form').length == 1) {
            var $form = this.closest('form')
        } else {
            var $form = $('<form/>').attr('method', 'post');


        }
        // Formaction
        if (typeof this.attr('formaction') != 'undefined') {
            $form.attr('action', this.attr('formaction'));
        }
        // Data-method
        if (typeof this.data('method') != 'undefined') {
            $form.append($('<input type="hidden" name="_method"/>').attr('value', this.data('method')));
        }

        $form.submit();



    };

    // Support for confirmation.
    $(document).on('click', '[data-confirm], [data-method]', function(e) {
        e.preventDefault();
        var $this = $(this);

        // Check for confirm.
        if (typeof $this.data('confirm') != 'undefined') {
            bootbox.confirm($this.data('confirm'), function (confirmed) {
                if (confirmed && typeof handlers[$this.prop('tagName')] != 'undefined') {
                    handlers[$this.prop('tagName')].call($this);
                } else if (confirmed) {
                    console.log("No handler for tag: " + $this.prop('tagName'));
                }
            });
        } else {
            handlers[$this.prop('tagName')].call($this);
        }
    });


    // Support for animated resizing of some target element.
    $(document).on('click', '[data-width][data-height][data-target]', function(e) {
        e.preventDefault();
        var $elem = $(this);
        $($elem.attr('data-target')).animate({
            "width": $elem.attr('data-width'),
            "height": $elem.attr('data-height')
        });
    })


})(jQuery);