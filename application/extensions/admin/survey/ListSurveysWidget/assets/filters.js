$('#survey_gsid, #survey_active').change(function (e) {
    if ($(this).find(":selected").val()) {
        $('#survey-search').submit();
    }
});

$('.search-bar input')
    .blur(function (e) {
        if ($(this).val()) {
            $('#survey-search').submit();
        }
    })
    .keydown(function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            if ($(this).val()) {
                $('#survey-search').submit();
            }
        }
    });

$('.search-bar i').click(function (e) {
    if ($(this).val()) {
        $('#survey-search').submit();
    }
});

$('#survey_reset').click(function (e) {
    e.preventDefault()
    location.href = location.href.split('?')[0]
});

