var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function(){
    wireAddUserGroupForm();

    $(':checkbox:not(:checked)[data-indeterminate=1]').prop('indeterminate', true)

    $(".surveysecurity").tablesorter({
        widgets: ['zebra'],
        sortList: [[2,0]],
        headers: { 19: { sorter: false} }
    });


    $(".table-permissions-set").tablesorter({
         widgets: ['zebra'],
         headers: { 0: { sorter: false},
                    2: { sorter: false},
                    3: { sorter: false},
                    4: { sorter: false},
                    5: { sorter: false},
                    6: { sorter: false},
                    7: { sorter: false},
                    8: { sorter: false}
                  }
    });

    $(".markrow").click(
        function(){
            $(this).removeClass('mixed');
            $(this).closest('tr').find('input').prop('checked',$(this).prop('checked')).prop('indeterminate',false);
            updateAllCheckboxes();
        }
    )

    // mark all checkboxes
    $(".markall").click(
        function(){
            $(this).removeClass('mixed');
            var checked = $(this).prop('checked');
            $('.table-permissions-set tbody tr').each(function(){
                var rowSelector = $(this).find('input');
                $(rowSelector).prop('checked',checked).prop('indeterminate',false);
            });
        }
    )

    $('.extended input').click(
        function(){
            if ($(this).closest('tr').find('.extended input:checked').size()==$(this).closest('tr').find('.extended input').size())
            {
                $(this).closest('tr').find('.markrow').prop('checked',true).removeClass('mixed');
            }
            else if ($(this).closest('tr').find('.extended input:checked').size()==0)
            {
                $(this).closest('tr').find('.markrow').prop('checked',false).removeClass('mixed');
            }
            else
            {
                $(this).closest('tr').find('.markrow').prop('checked',true).addClass('mixed');
            }
            updateAllCheckboxes();
        }
    )

    if (Cookies.get('surveysecurityas')!='true')
    {
        $('.table-permissions-set .extended').hide();
    }

    $('.table-permissions-set tbody tr').each(function(){
        if ($(this).find('.extended input:checkbox:checked').length == $(this).find('.extended input:checkbox').length)
        {
            $(this).find('.markrow').prop('checked',true).removeClass('mixed');
        }
        else if (!$(this).find('.extended input:checkbox:checked').length)
        {
            $(this).find('.markrow').prop('checked',false).removeClass('mixed');
        }
        else
        {
            $(this).find('.markrow').prop('checked',true).addClass('mixed');
        }
    })

    $('#btnToggleAdvanced').click(function(){
        extendoptionsvisible=$('.table-permissions-set .extended').is(':visible');
        if (extendoptionsvisible==false)
        {
            $('.table-permissions-set .extended').fadeIn('slow');
        }
        else
        {
            $('.table-permissions-set .extended').fadeOut();
        }
        updateExtendedButton(!extendoptionsvisible);
        Cookies.set('surveysecurityas',!extendoptionsvisible);
    });
    updateExtendedButton(true);

    updateAllCheckboxes();
});

function updateExtendedButton(bVisible)
{
    if (bVisible==true)
    {
        $('#btnToggleAdvanced').val('<<');
    }
    else
    {
        $('#btnToggleAdvanced').val('>>');
    }

}

function updateAllCheckboxes(){
    var iFullCheckedRows = 0;
    var iHalfCheckedRows = 0;
    var iNoCheckedRows = 0;
    $('.table-permissions-set tbody tr').each(function(){
        var rowSelector = $(this).find('.markrow');
        if (rowSelector.prop('checked') === true && !rowSelector.hasClass('mixed')){
            iFullCheckedRows += 1;
        } else if (rowSelector.prop('checked') === true && rowSelector.hasClass('mixed')){
            iHalfCheckedRows += 1;
        } else if (rowSelector.prop('checked') === false){
            iNoCheckedRows += 1;
        }
    });

    var markAllSelector = $('.table-permissions-set thead tr').find('.markall');

    if (iFullCheckedRows > 0 && iHalfCheckedRows == 0 && iNoCheckedRows == 0){
        markAllSelector.prop('checked',true).removeClass('mixed');
    } else if (iFullCheckedRows > 0 || iHalfCheckedRows > 0){
        markAllSelector.prop('checked',true).addClass('mixed');
    } else {
        markAllSelector.prop('checked',false).removeClass('mixed');
    }
}

var startAddUserGroupSubmit = function () {
    $('#SurveyPermissions-addusergroup-submit').append(
        '<i class="ri-loader-2-fill remix-pulse SurveyPermissions-spinner"></i>'
    ).prop('disabled', true);
};

var stopAddUserGroupSubmit = function () {
    $('.SurveyPermissions-spinner').remove();
    $('#SurveyPermissions-addusergroup-submit').prop('disabled', false);
};

var triggerPermissionsModalClose = function () {
    $('#UserManagement-action-modal').find('.modal-content').empty();
    $.fn.yiiGridView.update('gridPanel', {});
    $('#UserManagement-action-modal').modal('hide');
};

var wireAddUserGroupForm = function () {
    $('#SurveyPermissions-addusergroup-submit').off('click.addusergroup').on('click.addusergroup', function (e) {
        addUserGroupToSurvey();
    });
};

var addUserGroupToSurvey = function () {
    startAddUserGroupSubmit();
    const form = $('#SurveyPermissions-addusergroup-form');
    var data = form.serializeArray();
    $.ajax({
        url: form.attr('action'),
        data: data,
        method: 'POST',
        dataType: 'json',
        success: function (result) {
            stopAddUserGroupSubmit();
            if (result.success === true) {
                if (!result.hasOwnProperty('html')) {
                    triggerPermissionsModalClose();
                    window.LS.ajaxAlerts(result.message, 'success', {showCloseButton: true});
                    if (result.hasOwnProperty('href')) {
                        setTimeout(function() {
                            const modalSize = result.hasOwnProperty('modalsize') ? result.modalsize : '';
                            LS.UserManagement.openModal(result.href, modalSize);
                        }, 500);
                    }
                    return;
                }
                $('#UserManagement-action-modal').find('.modal-content').html(result.html);
                $('#exitForm').on('click.addusergroup', function (e) {
                    e.preventDefault();
                    $('#exitForm').off('click.addusergroup');
                    triggerPermissionsModalClose();
                });
                return;
            }
            $("#usermanagement-modal-doalog").offset({ top: 10 });
            window.LS.ajaxAlerts(result.errors, 'danger', {showCloseButton: true, timeout: 10000});
        },
        error: function (request, status, error) {
            stopAddUserGroupSubmit();
            if (request && request.responseJSON && request.responseJSON.message) {
                window.LS.ajaxAlerts(
                    request.responseJSON.message,
                    'danger',
                    {showCloseButton: true, timeout: 10000}
                );
            } else {
                alert('An error occured while trying to save, please reload the page Code:1571926261195');
            }
        }
    });
};