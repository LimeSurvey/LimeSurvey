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

$(document).on('click', '.updateAccessModeBtn', function updateAccessMode(e) {
    const newAccessMode = e.target.dataset.newaccessmode;
    const surveyId = e.target.dataset.surveyid;
    const button = document.getElementById('access-mode-dropdown');

    // Show loading state
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Updating...';
    button.disabled = true;

    // Build URL using LS.createUrl helper
    const url = LS.createUrl('surveyAdministration/updateAccessMode');

    // Make AJAX request
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'surveyId=' + surveyId + '&accessMode=' + newAccessMode + '&' + LS.data.csrfTokenName + '=' + LS.data.csrfToken
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button display
                if (newAccessMode === 'O') {
                    button.innerHTML = '<i class="ri-global-line"></i> Anyone with link';
                } else {
                    button.innerHTML = '<i class="ri-lock-2-line"></i> Link with access code';
                }

                // Close dropdown
                const dropdownElement = document.getElementById('access-mode-dropdown');
                const dropdown = bootstrap.Dropdown.getInstance(dropdownElement);
                if (dropdown) {
                    dropdown.hide();
                }
            } else {
                // Show error message
                console.error('Error updating access mode:', data.message);
                button.innerHTML = originalText;
                alert('Error updating access mode: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = originalText;
            alert('Error updating access mode: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
        });
});
