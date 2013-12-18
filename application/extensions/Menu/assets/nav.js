$(document).ready(function() {
    $('select[data-route]').on('change', function(event) {
        var params = {}
        params[$(this).attr('name')] = $(this).val();
        window.location.href = LS.createUrl($(this).data('route'), params);
    });
});
