$(document).ready(function() {
    $('select[data-route]').on('change', function(event) {
        var params = {}
        params[$(this).attr('name')] = $(this).val();
        if($(this).data('route').indexOf("?")>=0)
            window.location.href=$(this).data('route')+"&"+$(this).attr('name')+"="+$(this).val();
        else
            window.location.href=$(this).data('route')+"?"+$(this).attr('name')+"="+$(this).val();
        //window.location.href = LS.createUrl($(this).data('route'), params);
    });
});
