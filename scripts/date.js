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
                          defaultDate: +0,
                          minDate:new Date(range[0],0,1),
                          maxDate: new Date(range[1],11,31),
                          duration: 'fast'
                        }, $.datepicker.regional[language]);
    });

    // dropdown dates
    $('.month').change(dateUpdater);
    $('.day').change(dateUpdater)
    $('.minute').change(dateUpdater)
    $('.hour').change(dateUpdater)
    $('.year').change(dateUpdater);
    $('.year').change();
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
         ValidDate(this,$('#year'+thisid).val()+'-'+$('#month'+thisid).val()+'-'+$('#day'+thisid).val());          
         parseddate=$.datepicker.parseDate( 'dd-mm-yy', $('#day'+thisid).val()+'-'+$('#month'+thisid).val()+'-'+$('#year'+thisid).val());
         $('#answer'+thisid).val($.datepicker.formatDate( $('#dateformat'+thisid).val(), parseddate)); 
        $('#answer'+thisid).change();
        $('#qattribute_answer'+thisid).val('');
    }
}


function ValidDate(oObject, value) {// Regular expression used to check if date is in correct format 
    var str_regexp = /[1-9][0-9]{3}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/; 
    var pattern = new RegExp(str_regexp); 
    if ((value.match(pattern)!=null)) 
    {
        var date_array = value.split('-'); 
        var day = date_array[2]; 
        var month = date_array[1]; 
        var year = date_array[0]; 
        str_regexp = /1|3|5|7|8|10|12/; 
        pattern = new RegExp(str_regexp); 
        if ( day <= 31 && (month.match(pattern)!=null)) 
        { 
            return true; 
        } 
        str_regexp = /4|6|9|11/; 
        pattern = new RegExp(str_regexp); 
        if ( day <= 30 && (month.match(pattern)!=null)) 
        { 
            return true; 
        } 
        if (day == 29 && month == 2 && (year % 4 == 0)) 
        { 
            return true; 
        } 
        if (day <= 28 && month == 2) 
        { 
            return true; 
        }         
    } 
    window.alert('Date is not valid!'); 
    oObject.focus(); 
    return false; 
} 
