// This file will auto convert slider divs to sliders
$(document).ready(function(){
	// call the init slider routine for each element of the .multinum-slider class
	$(".popupdate").each(function(i,e) { 
        var basename = e.id.substr(6);         
        format=$('#dateformat'+basename).val();
        language=$('#datelanguage'+basename).val();
        $(e).datepicker({ dateFormat: format,  
                          showOn: 'both',
                          changeYear: true, 
                          changeMonth: true, 
                          duration: 'fast',
                        }, $.datepicker.regional[language]);
    });
    $('.year').change(dateUpdater);
    $('.month').change(dateUpdater);
    $('.day').change(dateUpdater)
});


function dateUpdater() {

    if(this.id.substr(0,3)=='yea')
    {
        thisid=this.id.substr(4);
    }
    if(this.id.substr(0,3)=='mon')
    {
        thisid=this.id.substr(5);
    }
    if(this.id.substr(0,3)=='day')
    {
        thisid=this.id.substr(3);
    }

    if (($('#year'+thisid).val()=='') || ($('#month'+thisid).val()=='') || ($('#day'+thisid).val()=='')){
        $('#qattribute_answer'+thisid).val('Please complete all parts of the date!');
        $('#answer'+thisid).val('');
    }
    else
    {
         ValidDate(this,$('#year'+thisid).val()+'-'+$('#month'+thisid).val()+'-'+$('#day'+thisid).val());          
         parseddate=$.datepicker.parseDate( 'dd-mm-yy', $('#day'+thisid).val()+'-'+$('#month'+thisid).val()+'-'+$('#year'+thisid).val());
         $('#answer'+thisid).val($.datepicker.formatDate( $('#dateformat'+thisid).val(), parseddate)); 
         $('#answer'+thisid).change();
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