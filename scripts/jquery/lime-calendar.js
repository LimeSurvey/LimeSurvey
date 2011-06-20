$(document).ready(function(){
    // pupup calendar
	$(".popupdate").each(function(i,e) { 
        var basename = e.id.substr(6);         
        format=$('#dateformat'+basename).val();
        language=$('#datelanguage'+basename).val();
        yearrange=$('#dateyearrange'+basename).val();
        range=yearrange.split(':');
        $(e).datepicker({ dateFormat: format,  
                          showOn: 'both',
                          changeYear: true, 
                          changeMonth: true,
                          yearRange: yearrange,
                          defaultDate:new Date(range[0]),
                          minDate:new Date(range[0]),
                          maxDate: new Date(range[1],12,31),
                          duration: 'fast'
                        }, $.datepicker.regional[language]);
    });

    // dropdown dates
    $('.month').change(dateUpdater);
    $('.day').change(dateUpdater)
    $('.year').change(dateUpdater);
    $('.hour').change(dateUpdater);
    $('.minute').change(dateUpdater);
    $('.month, .day, .year, .hour, .minute').change();
});


function dateUpdater() {

    if(this.id.substr(0,3)=='yea')
    {
        thisid=this.id.substr(4);
    }
    else if(this.id.substr(0,3)=='mon')
    {
        thisid=this.id.substr(5);
    }
    else if(this.id.substr(0,3)=='day')
    {
        thisid=this.id.substr(3);
    }
    else if(this.id.substr(0,3)=='hou')
    {
        thisid=this.id.substr(4);
    }
    else if(this.id.substr(0,3)=='min')
    {
        thisid=this.id.substr(6);
    }

    if ((!$('#year'+thisid).length || $('#year'+thisid).val()=='') &&
            (!$('#month'+thisid).length || $('#month'+thisid).val()=='') &&
            (!$('#day'+thisid).length || $('#day'+thisid).val()=='') &&
            (!$('#hour'+thisid).length || $('#hour'+thisid).val()=='') &&
            (!$('#minute'+thisid).length || $('#minute'+thisid).val()==''))
    {
        $('#qattribute_answer'+thisid).val('');
        $('#answer'+thisid).val('');
        $('#answer'+thisid).change();
    }
    else if (($('#year'+thisid).length && $('#year'+thisid).val()=='') ||
            ($('#month'+thisid).length && $('#month'+thisid).val()=='') ||
            ($('#day'+thisid).length && $('#day'+thisid).val()=='') ||
            ($('#hour'+thisid).length && $('#hour'+thisid).val()==''))
    {
        $('#qattribute_answer'+thisid).val('Please complete all parts of the date!');
        $('#answer'+thisid).val('');
    }
    else
    {
        var answer = $('#dateformat'+thisid).val();
        if ($('#year'+thisid).length) answer = answer.replace(/y+/, $('#year'+thisid).val());
        if ($('#month'+thisid).length) answer = answer.replace(/m+/, $('#month'+thisid).val());
        if ($('#day'+thisid).length) answer = answer.replace(/d+/, $('#day'+thisid).val());
        if ($('#hour'+thisid).length) answer = answer.replace(/H+/, $('#hour'+thisid).val());
        if ($('#minute'+thisid).length)
        {
            // The minute is optional (assumed as 00)
            if ($('#minute'+thisid).val()=='')
            {
                answer = answer.replace(/M+/, '00');
            }
            else
            {
                answer = answer.replace(/M+/, $('#minute'+thisid).val());
            }
        }
        $('#answer'+thisid).val(answer);
        $('#answer'+thisid).change();
        $('#qattribute_answer'+thisid).val('');
    }
}
