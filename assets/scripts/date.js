/**
 * Function to check conditions in date question
 */
function doPopupDate(qId) {
    $("#question" + qId).find('.date-timepicker-group').on('show.td', function () {
        var minDate = $("#datemin" + $(this).data("basename")).text().trim();
        var maxDate = $("#datemax" + $(this).data("basename")).text().trim();

        let picker = window["picker_answer" + $(this).data("basename")];
        let locale = picker.optionsStore.options.localization.locale;

        // Setting minDate and maxDate on the Tempus Dominus instance
        if (minDate) {
            let min = moment(minDate.substr(0,10));
            min.set({h: 0, m: 0, s: 0});
            picker.optionsStore.options.restrictions.minDate = tempusDominus.DateTime.convert(min.toDate(), locale);
        }
        if (maxDate) {
            let max = moment(maxDate.substr(0,10));
            max.set({h: 23, m: 59, s: 59});
            picker.optionsStore.options.restrictions.maxDate = tempusDominus.DateTime.convert(max.toDate(), locale);
        }

        // Check current date is within the valid range
        if (minDate && picker.viewDate.isBefore(picker.optionsStore.options.restrictions.minDate)) {
            picker.dates.setValue(picker.optionsStore.options.restrictions.minDate);
        }
        if (maxDate && picker.viewDate.isAfter(picker.optionsStore.options.restrictions.maxDate)) {
            picker.dates.setValue(picker.optionsStore.options.restrictions.maxDate);
        }

        picker.display._update('all');
    });

    /* need to launch EM each time is updated */
    $("#question"+qId).find('.date-timepicker-group').on('change.td', function(){
        checkconditions($(this).find(":text").val(), $(this).find(":text").attr('name'), 'text', 'keyup')
    });
}

/**
 * Function to launch timepicker in question id
 * Using dropdown system
 */
function doDropDownDate(qId){
    $(document).on("change",'#question'+qId+' select',dateUpdater);
    $(document).on('ready pjax:scriptcomplete',function(){
        $("#question"+qId+" select").filter(':first').trigger("change");
        //dateUpdater();
    });
}

/**
 * dropdown date part is deprecated ?
 */
function dateUpdater() {
    var thisid = "", iFormatDate = "", iYear,iMonth,iDay,iHour,iMinute,parseddate,format;
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
        //nothing filled in
        $('#answer'+thisid).val('');
        $('#answer'+thisid).change();
    }
    else if (($('#year'+thisid).length && $('#year'+thisid).val()=='') ||
        ($('#month'+thisid).length && $('#month'+thisid).val()=='') ||
        ($('#day'+thisid).length && $('#day'+thisid).val()=='') ||
        ($('#hour'+thisid).length && $('#hour'+thisid).val()=='') ||
        ($('#minute'+thisid).length && $('#minute'+thisid).val()==''))
        {
            //incomplete
            $('#answer'+thisid).val('INVALID');
            $('#answer'+thisid).change();
            // QCODE.NAOK return "" if real value is INVALID (because can be shown to user), then do it manually (line 721 em_javascript)
        }
        else
        {
            if (!$('#year'+thisid).val())
            {
                iYear='1900';
            }
            else
            {
                iYear=$('#year'+thisid).val();
            }
            if (!$('#month'+thisid).val())
            {
                iMonth='01';
            }
            else
            {
                iMonth=$('#month'+thisid).val();
            }
            if (!$('#day'+thisid).val())
            {
                iDay='01';
            }
            else
            {
                iDay=$('#day'+thisid).val();
            }
            if (!$('#hour'+thisid).val())
            {
                iHour='00';
            }
            else
            {
                iHour=$('#hour'+thisid).val();
            }
            if (!$('#minute'+thisid).val())
            {
                iMinute='00';
            }
            else
            {
                iMinute=$('#minute'+thisid).val();
            }

            parseddate= new moment(iDay+'-'+iMonth+'-'+iYear+' '+iHour+':'+iMinute, 'DD-MM-YYYY HH:mm');
            format = $('#dateformat'+thisid).val();

            iFormatDate = moment(parseddate).format(format);

            $('#answer'+thisid).val(iFormatDate);
            $('#answer'+thisid).change();
        }
        return true;
}

function pad (str, max) {
    return str.length < max ? pad("0" + str, max) : str;
}

function ValidDate(oObject, value) {// Regular expression used to check if date is in correct format
    if(typeof showpopup=="undefined"){showpopup=1;}
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
    if(showpopup)
    {
        window.alert(translt.alertInvalidDate);
    }// TODO : use EM and move it to EM
    oObject.focus();
    return false;
}
