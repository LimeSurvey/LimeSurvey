$(document).ready(function(){
    // popup calendar
    $(".popupdate").each(function(i,e) {
        var basename = e.id.substr(6);
        format=$('#dateformat'+basename).val();
        format=format.replace(/H/gi,"0"); 
        format=format.replace(/N/gi,"0"); 
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

            if ($('#year'+thisid).size()==0)
            {
                iYear='1900';
            }
            else
            {
                iYear=$('#year'+thisid).val(); 
            }
            if ($('#month'+thisid).size()==0)
            {
                iMonth='01';
            }
            else
            {
                iMonth=$('#month'+thisid).val(); 
            }
            if ($('#month'+thisid).size()==0)
            {
                iDay='01';
            }
            else
            {
                iDay=$('#day'+thisid).val(); 
            }
            if ($('#hour'+thisid).size()==0)
            {
                iHour='00';
            }
            else
            {
                iHour=$('#hour'+thisid).val(); 
            }            
            if ($('#minute'+thisid).size()==0)
            {
                iMinute='00';
            }
            else
            {
                iMinute=$('#minute'+thisid).val(); 
            }
            ValidDate(this,iYear+'-'+iMonth+'-'+iDay);          
            parseddate=$.datepicker.parseDate( 'dd-mm-yy', iDay+'-'+iMonth+'-'+iYear);
            parseddate=$.datepicker.formatDate( $('#dateformat'+thisid).val(), parseddate);
            parseddate=parseddate.replace('HH',pad(iHour,2));
            parseddate=parseddate.replace('H',iHour);
            parseddate=parseddate.replace('NN',pad(iMinute,2));
            parseddate=parseddate.replace('N',iMinute);
            $('#answer'+thisid).val(parseddate); 
            $('#answer'+thisid).change();
            $('#qattribute_answer'+thisid).val('');
        }
}

function pad (str, max) {
    return str.length < max ? pad("0" + str, max) : str;
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
