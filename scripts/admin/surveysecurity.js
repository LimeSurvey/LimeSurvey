//$Id: surveysecurity.js 9376 2010-10-31 15:13:46Z c_schmitz $

$(document).ready(function(){
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
	
    $('.usersurveypermissions th.extended').wrapInner('<span />');

    $('.mixed').fadeTo(1, .4);

    $(".markrow").click(
        function(){
            $(this).fadeTo(1, 1);
            $(this).closest('tr').find('input').attr('checked',$(this).attr('checked'));
        }
    )

    $('.extended input').click(
     function(){
            $(this).closest('tr').find('.markrow').fadeTo(1, 1);
            if ($(this).closest('tr').find('.extended input:checked').size()==$(this).closest('tr').find('.extended input').size())
            {
                $(this).closest('tr').find('.markrow').attr('checked',true);
            }
            else if ($(this).closest('tr').find('.extended input:checked').size()==0)
            {
                $(this).closest('tr').find('.markrow').attr('checked',false);
            }
            else
            {
                $(this).closest('tr').find('.markrow').attr('checked',true);
                $(this).closest('tr').find('.markrow').fadeTo(1, .4); //greyed
            }
     }
    )

    if ($.cookie('surveysecurityas')=='false')
    {
		$('.usersurveypermissions .extended input, .usersurveypermissions .extended span').css({
			'visibility': 'hidden'
		});
		$('.usersurveypermissions .extended').css({
			'background-color': $('div.wrapper').css('background-color')
		});
		updateExtendedButton(false);
    }

    $('.usersurveypermissions tr').each(function(){
            $(this).find('.markrow').fadeTo(1, 1);
            if ($(this).find('.extended input:checked').size()==$(this).closest('tr').find('.extended input').size())
            {
                $(this).find('.markrow').attr('checked',true);
            }
            else if ($(this).find('.extended input:checked').size()==0)
            {
                $(this).find('.markrow').attr('checked',false);
            }
            else
            {
                $(this).find('.markrow').attr('checked',true);
                $(this).find('.markrow').fadeTo(1, .4); //greyed
            }
    })

    $('#btnToggleAdvanced').click(function(){
        var extendoptionsvisible = true;
		if($('.usersurveypermissions .extended input, .usersurveypermissions .extended span').css('visibility') == 'hidden') {
			extendoptionsvisible = false;
		}
        if (extendoptionsvisible==false)
        {
            $('.usersurveypermissions .extended input, .usersurveypermissions .extended span').css({
				'visibility': 'visible'
			});
            $('.usersurveypermissions .extended').css({
				'background-color':''
			});
        }
        else
        {
            $('.usersurveypermissions .extended input, .usersurveypermissions .extended span').css({
				'visibility': 'hidden'
			});
            $('.usersurveypermissions .extended').css({
				'background-color': $('div.wrapper').css('background-color')
			});
        }
        updateExtendedButton(!extendoptionsvisible);
        $.cookie('surveysecurityas',!extendoptionsvisible);
    });
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
