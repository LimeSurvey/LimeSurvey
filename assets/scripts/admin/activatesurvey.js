// Namespace
var LS = LS || {  onDocumentReady: {} };

function openModalActivate(){
    let activateBtn = document.getElementById('ls-activate-survey');
    let surveyId = activateBtn.dataset.surveyid;
    let url = activateBtn.dataset.url;
    //LS.AjaxHelper.ajax
    $.ajax({
        url: url,
        data: {surveyId},
        method: 'POST',
        success: function (result) {
            //set data in modal
            let modalDialog = $('#surveyactivation-modal');

            modalDialog.find('.modal-body').empty().html(result.html);
            if (result.checkFailed) {
                //footer buttons have to be different, if any activation-checks failed
                modalDialog.find('.modal-footer').empty().html(result.footerButton);
            } else {
                if (result?.footerButton != '') {
                    // if footerButton has content, it should also be used when checks are ok
                    modalDialog.find('.modal-footer').empty().html(result.footerButton);
                }
            }
            modalDialog.modal('show');
        },
        error: function (result) {
            console.log('error: no data from request for activation modal');
            console.log(result);
        }
    });
}

/**
 * Trigger submit button
 */
function activateWithOptions(){
    //get the selected options from modal
    //ajax request doing all what is necessary in  backend (tokens-table etc.)
    //open another modal to inform user
    document.getElementById('submitActivateSurvey').click();
}
