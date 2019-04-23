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


    $(".usersurveypermissions").tablesorter({
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
                $(this).closest('tr').find('.markrow').prop('checked',true).addClass('mixed');;
            }
     }
    )

    if (Cookies.get('surveysecurityas')!='true')
    {
        $('.usersurveypermissions .extended').hide();
    }

    $('.usersurveypermissions tr').each(function(){
            if ($(this).find('.extended input:checked').size()==$(this).closest('tr').find('.extended input').size())
            {
                $(this).find('.markrow').prop('checked',true).removeClass('mixed');
            }
            else if ($(this).find('.extended input:checked').size()==0)
            {
                $(this).find('.markrow').prop('checked',false).removeClass('mixed');
            }
            else
            {
                $(this).find('.markrow').prop('checked',true).addClass('mixed');
            }
    })

    $('#btnToggleAdvanced').click(function(){
        extendoptionsvisible=$('.usersurveypermissions .extended').is(':visible');
        if (extendoptionsvisible==false)
        {
            $('.usersurveypermissions .extended').fadeIn('slow');
        }
        else
        {
            $('.usersurveypermissions .extended').fadeOut();
        }
        updateExtendedButton(!extendoptionsvisible);
        Cookies.set('surveysecurityas',!extendoptionsvisible);
    })
    updateExtendedButton(true);
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
