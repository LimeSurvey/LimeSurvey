/**
 * Save row to table
 *
 * @return void
 */
function saveParameter() {
    var sParamname = $.trim($('#paramname').val());
    if (sParamname == '' || !/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(sParamname) || sParamname == 'sid' || sParamname == 'newtest' || sParamname == 'token' || sParamname == 'lang') {
        LS.ajaxAlerts(window.sEnterValidParam, 'danger', { inline: '#parameterError' });
        return;
    }
    var modal = bootstrap.Modal.getInstance($('#dlgEditParameter'))
    modal.hide();
    try {
        var rowData = JSON.parse($('#dlgEditParameter').data('rawdata'));
    } catch (e) {
        rowData = {};
    }

    var URLParam = {};
    var postUrl = $('#dlgEditParameter').data('save-url');

    if ($('#dlgEditParameter').data('action') == 'add') {
        URLParam.parameter = sParamname;
        URLParam.targetqid = $('#targetquestion').val().split('-').shift() || '';
        URLParam.targetsqid = $('#targetquestion').val().split('-').pop() || '';
    } else {
        URLParam.id = rowData.id;
        URLParam.parameter = sParamname;
        URLParam.targetqid = $('#targetquestion').val().split('-').shift() || '';
        URLParam.targetsqid = $('#targetquestion').val().split('-').pop();
    }

    var postDatas = {
        surveyId: window.PanelIntegrationData.surveyid,
        URLParam: URLParam
    };

    sendPostAndUpdate(postUrl, postDatas);
}

function newParameter(data) {
    $('#parameterError').html('');
    $('#targetquestion').val('');
    $('#paramname').val('');
    $('#dlgEditParameter').data('action', 'add');
    $('#dlgEditParameter .modal-title').text(window.PanelIntegrationData.i10n['Add URL parameter']);
}

function editParameter(event, aRowData) {
    $('#parameterError').html('');
    $('#targetquestion').val((aRowData.qid || '') + '-' + (aRowData.sqid || ''));
    $('#paramname').val(aRowData.parameter);
    $('#dlgEditParameter').data('action', 'edit');
    $('#dlgEditParameter').data('rawdata', JSON.stringify(aRowData));
    $('#dlgEditParameter .modal-title').text(window.PanelIntegrationData.i10n['Edit URL parameter']);
    const modal = new bootstrap.Modal(document.getElementById('dlgEditParameter'));
    modal.show();
}

function in_array(needle, haystack, argStrict) {

    var key = '',
        strict = !!argStrict;

    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }

    return false;
}

function guidGenerator() {
    var S4 = function () {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return (S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4());
}

/**
 * Validate settings form depending on form name
 */
function validateSettingsForm($form) {
    switch ($form.attr('id')) {
        case 'publication':
            return LS.validateEndDateHigherThanStart(
                $('#startdate_datetimepicker').data('DateTimePicker'),
                $('#expires_datetimepicker').data('DateTimePicker'),
                () => {
                    LS.createAlert(expirationLowerThanStartError, 'danger', { 'showCloseButton': true })
                }
            );
        default:
            return true;
    }
}

/**
 * Validates that an end date is not lower than a start date
 */
function validateEndDateHigherThanStart(startDatePicker, endDatePicker, errorMessage) {
    if (!startDatePicker || !startDatePicker.date()) {
        return true;
    }
    if (!endDatePicker || !endDatePicker.date()) {
        return true;
    }
    const difference = endDatePicker.date().diff(startDatePicker.date());
    if (difference >= 0) {
        return true;
    }
    LS.LsGlobalNotifier.createAlert(errorMessage, 'danger');
    return false;
}

function sendPostAndUpdate(url, data) {
    var postDatas = data || {};
    postDatas[LS.data.csrfTokenName] = LS.data.csrfToken;

    // Ajax request
    $.ajax({
        url: url,
        type: 'POST',
        data: postDatas,

        success: function (result) {
            if (!result.success) {
                var errorMsg = result.message || '';
                if (!errorMsg) errorMsg = "Unexpected error";
                LS.LsGlobalNotifier.createAlert(errorMsg, 'danger');
                return;
            }

            LS.LsGlobalNotifier.createAlert(result.message, 'success');

            try {
                $.fn.yiiGridView.update('urlparams');
            } catch (e) {
                if (e) {
                    console.ls.error(e);
                }
            }
        },
        error: function (result) {
            LS.LsGlobalNotifier.createAlert(result.statusText ?? "Unexpected error", 'danger');
        }
    });
}

function searchParameters() {
    var data = {
        search_query: $('#search_query').val()
    };
    $.fn.yiiGridView.update('urlparams', { data: data });
}

$(document).on('click', '#addParameterButton', function (e) {
    e.preventDefault();
    newParameter(e);
});
$(document).on('click', '.surveysettings_edit_intparameter', function (e) {
    e.preventDefault();
    editParameter(e, $(this).data());
});

$(document).on('click', '#btnSaveParams', saveParameter);

$(document).on('click', '#searchParameterButton', searchParameters);
$(document).on('change', '#integrationPanelPager #pageSize', function () {
    $.fn.yiiGridView.update('urlparams', { data: { pageSize: $(this).val() } });
});

if (!window.accessModes) {
    window.accessModes = true;
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
}
