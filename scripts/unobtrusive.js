/**
 * This file adds support for unobtrusive javascript.
 * Created by sam on 6/16/15.
 */
(function($) {

    var handlers = {};
    handlers.A = function() {
        if (this.attr('data-method')) {
            var $form = $('<form/>')
                .attr('method', 'post')
                .append($('<input name="_method"/>').attr('value',
                    this.attr('data-method')))
                .attr('action', this.attr('href'))
                .submit();
        } else {
            window.location.href = this.attr('href');
        }
    }

    // Support for confirmation.
    $(document).on('click', '[data-confirm]', function(e) {
        e.preventDefault();
        var $this = $(this);
        bootbox.confirm($this.data('confirm'), function(confirmed) {
            if (confirmed && typeof handlers[$this.prop('tagName')] != 'undefined') {
                handlers[$this.prop('tagName')].call($this);
            } else if (confirmed) {
                console.log("No handler for tag: " + $this.prop('tagName'));
            }
        });
    });

})(jQuery);