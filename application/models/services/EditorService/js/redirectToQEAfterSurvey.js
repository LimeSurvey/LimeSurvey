$(document).on('ready pjax:scriptcomplete', function () {
    $('#addnewsurvey').unbind('submit');
    $('#addnewsurvey').on('submit', function (event) {
        event.preventDefault();
        var form = this;
        var data = $(form).serializeArray();
        var uri = $(form).attr('action');
        $.ajax({
            url: uri,
            method: 'POST',
            data: data,
            success: function (result) {
                if (result.redirecturl != undefined) {
                    const urlParams = new URLSearchParams(result.redirecturl.split('?')[1]);
                    const sid1 = urlParams.get('surveyid');
                    const sid2 = urlParams.get('iSurveyID');
                    const sid = sid1 || sid2;
                    window.location.href = '/editorLink/index?route=survey%2F' + sid;
                } else {
                    window.location.reload();
                }
            },
            error: function (result) {
                console.log({
                    result: result
                });
            }
        });
        return false;
    });
});
