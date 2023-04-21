// Namespace
var LS = LS || {  onDocumentReady: {} };

$('#ls-activate-survey').on('click', function (e) {
    e.preventDefault();

    let activateBtn = document.getElementById('ls-activate-survey');
    let surveyId = activateBtn.surveyid;
    //use survey id to get the data for the modal
    //create an ajax-request to get the data
    let url = activateBtn.url;
    console.log('in js activatesurvey');
    LS.AjaxHelper.ajax({
        url: url,
        data: {surveyId},
        method: 'POST',
        success: function (data) {
            //set data in modal
            console.log(data);
            //open the modal
        },
        error: function () {
            console.ls.log('in error');
        }
    });
    //open the modal with either the data or an error message
});
