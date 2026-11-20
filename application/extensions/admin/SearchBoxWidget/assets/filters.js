$('#survey_reset').click(function (e) {
    e.preventDefault()
    let url = location.toString().replace(location.search, "")
    let params = Object.fromEntries(new URLSearchParams(location.search));
    delete params['Survey[searched_value]'];
    delete params['active'];
    delete params['gsid'];
    location.href = url + '?' + new URLSearchParams(params).toString()
});

$('.view-switch').on('click keydown', function (e) {
    // click, enter, spacebar
    if (e.type === 'click' || e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        var viewtype = $(this).data('action');
        $(this).append('<input type="hidden" name="viewtype" value="' + viewtype + '" />');
        $('#survey-search').submit();
    }
});

$('#survey_gsid, #survey_active').change(function (e) {
    rememberFilterFocus(this);
    $('#survey-search').submit();
});

$('.search-bar input').each(function () {
    $(this).data('original-value', $(this).val());
});

$('.search-bar input').keydown(function (e) {
    if (e.key === "Enter" || e.keyCode === 13) {
        e.preventDefault();
        $(this).data('original-value', $(this).val());
        rememberFilterFocus(this);
        $('#survey-search').submit();  // 🔍 trigger search
    }
});

$('.search-bar input').on('blur', function () {
    if ($(this).val() !== $(this).data('original-value')) {
        $(this).data('original-value', $(this).val());
        $('#survey-search').submit();
    }
});

$('.search-bar i').click(function (e) {
    $('#survey-search').submit();
});

/**
 * Filtering reloads the page, so screen reader users would start again at the top of the page
 * without knowing the result. The filter that was used is remembered and gets the focus back after the reload.
 */
function rememberFilterFocus(element) {
    try {
        sessionStorage.setItem('surveySearchFocus', element.id);
    } catch (e) {
        // Storage not available: the focus is just not restored
    }
}

$(function () {
    var focusId = null;
    try {
        focusId = sessionStorage.getItem('surveySearchFocus');
        sessionStorage.removeItem('surveySearchFocus');
    } catch (e) {
        // Storage not available
    }
    if (focusId && document.getElementById(focusId)) {
        document.getElementById(focusId).focus();
    }

    // Announce the result of the filtering, e.g. "No surveys found."
    // The text is added after the page has loaded, otherwise the live region is not announced.
    var $status = $('#survey-search-status');
    if ($status.length && $status.data('message')) {
        setTimeout(function () {
            $status.text($status.data('message'));
        }, 500);
    }
});
