$('#survey_reset').click(function (e) {
    e.preventDefault()
    // The reset button is hidden after the reload, so the search field gets the focus
    rememberFilterFocus(document.getElementById('survey_searched_value'), true);
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
        // Only the view changes, not the result: nothing to announce
        rememberFilterFocus(this, false);
        $('#survey-search').submit();
    }
});

$('#survey_gsid, #survey_active').change(function (e) {
    rememberFilterFocus(this, true);
    $('#survey-search').submit();
});

$('.search-bar input').each(function () {
    $(this).data('original-value', $(this).val());
});

$('.search-bar input').keydown(function (e) {
    if (e.key === "Enter" || e.keyCode === 13) {
        e.preventDefault();
        $(this).data('original-value', $(this).val());
        rememberFilterFocus(this, true);
        $('#survey-search').submit();  // 🔍 trigger search
    }
});

$('.search-bar input').on('blur', function (e) {
    if ($(this).val() !== $(this).data('original-value')) {
        $(this).data('original-value', $(this).val());
        // The user left the field towards another filter control (e.g. Tab to the status): That one gets the focus back.
        // Anything else (page background, result rows whose IDs change with the result) gives it back to the search field.
        var target = e.relatedTarget;
        var keepTarget = target && target.id && $(target).closest('#survey-search').length > 0;
        rememberFilterFocus(keepTarget ? target : this, true);
        $('#survey-search').submit();
    }
});

$('.search-bar i').click(function (e) {
    rememberFilterFocus($(this).siblings('input').get(0), true);
    $('#survey-search').submit();
});

/**
 * Filtering reloads the page, so screen reader users would start again at the top of the page
 * without knowing the result. The element that was used gets the focus back after the reload,
 * and the number of found surveys is announced.
 *
 * @param {HTMLElement|null} element The element to focus after the reload, null to keep the default focus
 * @param {boolean} announce Whether the number of found surveys is announced after the reload
 */
function rememberFilterFocus(element, announce) {
    try {
        sessionStorage.setItem('surveySearchFocus', JSON.stringify({
            id: element && element.id ? element.id : null,
            announce: announce,
            time: Date.now()
        }));
    } catch (e) {
        // Storage not available: the focus is just not restored
    }
}

$(function () {
    var state = null;
    try {
        state = JSON.parse(sessionStorage.getItem('surveySearchFocus'));
        sessionStorage.removeItem('surveySearchFocus');
    } catch (e) {
        // Storage not available or no valid value
    }
    // Ignore a value left over from a submit that never reached this page (e.g. redirect to the login)
    if (!state || typeof state.time !== 'number' || Date.now() - state.time > 60000) {
        return;
    }
    if (state.id && document.getElementById(state.id)) {
        document.getElementById(state.id).focus();
        // The survey grid would move the focus to its "No surveys found." message (CLSGridView): Keep it here
        window.LS = window.LS || {};
        LS.gridView = LS.gridView || {};
        LS.gridView.keepFocusOnLoad = true;
    }

    // Announce the result of the filtering, e.g. "No surveys found."
    // The text is added once the screen reader has read the focused element, otherwise the update may be dropped.
    var $status = $('#survey-search-status');
    if (state.announce && $status.length && $status.data('message')) {
        setTimeout(function () {
            $status.text($status.data('message'));
        }, 1500);
    }
});
