$('#survey_gsid, #survey_active').change(function (e) {
    console.log($(this).find(':selected').val());
    $('#survey-search').submit();
});

$('.search-bar input')
    .blur(function (e) {
        $('#survey-search').submit();
    })
    .keydown(function (e) {
        if (e.keyCode === 13) {
            $('#survey-search').submit();
        }
    });

$('.search-bar i').click(function (e) {
    $('#survey-search').submit();
});

$('#survey_reset').click(function (e) {
    e.preventDefault()
    let url = location.toString().replace(location.search, "")
    let params = Object.fromEntries(new URLSearchParams(location.search));
    delete params['Survey[searched_value]'];
    delete params['active'];
    delete params['gsid'];
    location.href = url + '?' + new URLSearchParams(params).toString()
});

$('.view-switch').click(function (e) {
    e.preventDefault();
    var viewtype = $(this).data('action')
    $(this).append('<input type="hidden" name="viewtype" value="'+viewtype+'" /> ');
    $('#survey-search').submit();
});
