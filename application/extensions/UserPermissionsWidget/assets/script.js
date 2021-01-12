var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function(){
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
            $(this).closest('tr').find('input:not(:disabled)').prop('checked',$(this).prop('checked')).prop('indeterminate',false);
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
    /* Show on the all columnn the state of included checkbox */
    $('.table-permissions-set tbody tr').each(function(){
        if ($(this).find('.extended input:checkbox:checked').length == $(this).find('.extended input:checkbox').length) {
            /* All is checked */
            $(this).find('.markrow').prop('checked',true).removeClass('mixed');
        } else if (!$(this).find('.extended input:checkbox:checked').length) {
            /* None is checked */
            if ($(this).find('.extended input:checkbox[data-indeterminate="1"]').length == $(this).find('.extended input:checkbox').length) {
                $(this).find('.markrow').prop('indeterminate',true).removeClass('mixed');
            } else if(!$(this).find('.extended input:checkbox[data-indeterminate="1"]').length) {
                $(this).find('.markrow').prop('indeterminate',false).removeClass('mixed');
            } else {
                $(this).find('.markrow').prop('indeterminate',true).addClass('mixed');
            }
        } else {
            /* Partially  checked */
            $(this).find('.markrow').prop('checked',true).addClass('mixed');
        }
        /* disabled : only in all */
        if ($(this).find('.extended input:checkbox:disabled').length == $(this).find('.extended input:checkbox').length) {
            $(this).find('.markrow').prop('disabled', true);
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
