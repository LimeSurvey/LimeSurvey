$(document).ready(function(){

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
            if (this.name=='all_global_superadmin')
            {
                $(this).closest('table').find('input').prop('checked',bChecked).fadeTo(1, 1);
            }
            else
            {
                $(this).closest('tr').find('input').prop('checked',bChecked);
            }
        }
    )

    $('.extended input').click(
     function(){
            if (this.name=='perm_global_superadmin_read')
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

    if ($.cookie('surveysecurityas')=='false')
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
        $.cookie('surveysecurityas',!extendoptionsvisible);
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
