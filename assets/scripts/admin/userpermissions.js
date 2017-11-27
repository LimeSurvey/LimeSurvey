
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function(){
    $('#perm_superadmin_read').insertAfter($('#all_superadmin'));
    $('#all_superadmin').remove();
    $(".userpermissions").tablesorter({
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

    $('.mixed').fadeTo(1, .4);

    $(".markrow").click(
        function(){
            $(this).fadeTo(1, 1);
            bChecked=this.checked;
            $(this).closest('tr').find('input').prop('checked',bChecked);
        }
    )

    $('.extended input,#perm_superadmin_read').click(
     function(){
            if (this.name=='perm_superadmin_read')
            {
                $(this).closest('table').find('input').prop('checked',this.checked).fadeTo(1, 1);
            }
            $(this).closest('tr').find('.markrow').fadeTo(1, 1);
            if ($(this).closest('tr').find('.extended input:checked').size()==$(this).closest('tr').find('.extended input').size())
            {
                $(this).closest('tr').find('.markrow').prop('checked',true);
            }
            else if ($(this).closest('tr').find('.extended input:checked').size()==0)
            {
                $(this).closest('tr').find('.markrow').prop('checked',false);
            }
            else
            {
                $(this).closest('tr').find('.markrow').prop('checked',true);
                $(this).closest('tr').find('.markrow').fadeTo(1, .4); //greyed
            }
     }
    )

    if (Cookies.get('userpermissions')!='true')
    {
        $('.userpermissions .extended').hide();
    }

    $('.userpermissions tr').each(function(){
            $(this).find('.markrow').fadeTo(1, 1);
            if ($(this).find('.extended input:checked').size()==$(this).closest('tr').find('.extended input').size())
            {
                $(this).find('.markrow').prop('checked',true);
            }
            else if ($(this).find('.extended input:checked').size()==0)
            {
                $(this).find('.markrow').prop('checked',false);
            }
            else
            {
                $(this).find('.markrow').prop('checked',true);
                $(this).find('.markrow').fadeTo(1, .4); //greyed
            }
    })

    $('#btnToggleAdvanced').click(function(){
        extendoptionsvisible=$('.userpermissions .extended').is(':visible');
        if (extendoptionsvisible==false)
        {
            $('.userpermissions .extended').fadeIn('slow');
        }
        else
        {
            $('.userpermissions .extended').fadeOut();
        }
        updateExtendedButton(!extendoptionsvisible);
        Cookies.set('userpermissions',!extendoptionsvisible);
    })
    updateExtendedButton(false);
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
