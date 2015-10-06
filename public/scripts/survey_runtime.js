
(function($) {




})(jQuery);
// Some function can be launch before document ready (and seems intersting)
needConfirmHandler();
tableCellAdapters();
activateLanguageChanger();
$(document).ready(function()
{
    navbuttonsJqueryUi();
    showStartPopups();
    addClassEmpty();
    noScrollOnSelect();
    doToolTipTable();

    if (typeof checkconditions!='undefined') checkconditions();
	if (typeof template_onload!='undefined') template_onload();
    if (typeof(focus_element) != 'undefined')
    {
        $(focus_element).focus();
    }


});

/**
 * setJsVar : Get all global used var
 */
function setJsVar(){
    bFixNumAuto=LSvar.bFixNumAuto;
    bNumRealValue=LSvar.bNumRealValue;
    LEMradix=LSvar.sLEMradix;
    numRegex = new RegExp('[^-' + LEMradix + '0-9]','g');
    intRegex = new RegExp('[^-0-9]','g');
}


// Ask confirmation on click on .needconfirm
function needConfirmHandler(){
    $(document).on('click',"[data-confirmedby]", function(event){
        text=$("label[for='"+$(this).data('confirmedby')+"']").text();
        if (confirm(text)) {
            $("#"+$(this).data('confirmedby')).prop('checked',true);
            return true;
        }
        $(".button.ui-button" ).button( "option", "disabled", false );
        $(".button").prop('disabled',false).removeClass('disabled');
        $(this).prop('active',false).removeClass('active');
        return false;
    });
}

/**
 * fixnum_checkconditions : javascript function attach to some element 
 * Update the answer of the user to be numeric and launch checkconditions
 */
function fixnum_checkconditions(value, name, type, evt_type, intonly)
{
    newval = new String(value);

    if (typeof intonly !=='undefined' && intonly==1) {
        newval = newval.replace(intRegex,'');
    }
    else {
        newval = newval.replace(numRegex,'');
    }
    aNewval = newval.split(LEMradix);
    if(aNewval.length>0){
        newval=aNewval[0];
    }
    if(aNewval.length>1){
        newval=newval+"."+aNewval[1];
    }
    if (newval != '-' && newval != '.' && newval != '-.' && newval != parseFloat(newval)) {// Todo : do it in reg
        newval = '';
    }

    /**
     * If have to fix numbers automatically.
     */    
    if(bFixNumAuto)
    {

        /**
         * Work on length of the number
         * Avoid numbers longer than 20 characters before the decimal separator and 10 after the decimal separator. 
         */
        var midval = newval;
        var aNewval = midval.split('.');
        var newval = '';
        
        // Treat integer part            
        if (aNewval.length > 0) {                           
            var intpart = aNewval[0];
            newval = (intpart.length > 20) ? '99999999999999999999' : intpart;
        }

        // Treat decimal part, if there is one.             
        // Trim after 10th decimal if larger than 10 decimals.
        if (aNewval.length > 1) {                
            var decpart = aNewval[1];
            if (decpart.length > 10){       
                decpart = decpart.substr(0,10);
            }
            else {
                decpart = aNewval[1];                
            }
            newval = newval + "." + decpart;
        }

        /**
         * Set display value
         */ 
        displayVal = newval;
        if (LEMradix === ',') {
            displayVal = displayVal.split('.').join(',');
        }
        if (name.match(/other$/)) {
            $('#answer'+name+'text').val(displayVal);
        }
        $('#answer'+name).val(displayVal);
    }

    /**
     * Check conditions
     */
    if (typeof evt_type === 'undefined')
    {
        evt_type = 'onchange';
    }
    checkconditions(newval, name, type, evt_type);
}

// Set jquery-ui to LS Button
function navbuttonsJqueryUi(){
    $('[dir!="rtl"] #moveprevbtn').button({
    icons: {
        primary: 'ui-icon-triangle-1-w'
    }
    });
    $('[dir="rtl"] #moveprevbtn').button({
    icons: {
        secondary: 'ui-icon-triangle-1-e'
    }
    });
    $('[dir!="rtl"] #movenextbtn').button({
    icons: {
        secondary: 'ui-icon-triangle-1-e'
    }
    });
    $('[dir="rtl"] #movenextbtn').button({
    icons: {
        primary: 'ui-icon-triangle-1-w'
    }
    });
    $(".button").button();
}
/**
 * showStartPopups : Take all message in startPopups json array and launch an alert with text
 */
function showStartPopups(){
    if(typeof showpopup=="undefined"){showpopup=1;}
    if(typeof startPopups=="undefined"){startPopups=[];}
    if(showpopup){
        $.each(startPopups,function(key, text){
            alert($("<div/>").html(text).text());// Parse HTML because of &#039;
        });
    }
}
/**
 * Update survey just when select a new language
 */
function activateLanguageChanger(){
    $(document).on('change','select.languagechanger', function() {
        if($(this).hasClass('previewmode'))
        {
            var target=$(this).data('targeturl');
            $('<form>', {
                "html": '<input type="hidden" name="lang" value="' + $(this).find('option:selected').val() + '" />',
                "action": target
            }).appendTo(document.body).submit();
            return false;
        }
        if(!$(this).closest('form').length){// If there are no form : we can't use it, we need to create and submit. This break no-js compatibility in some page (token for example).
            if($('form#limesurvey').length==1){ // The limesurvey form exist in document, move select and button inside and click
                $("form#limesurvey [name='lang']").remove();// Remove existing lang selector
                $("<input type='hidden']>").attr('name','lang').val($(this).find('option:selected').val()).appendTo($('form#limesurvey'));
                $("#changelangbtn").appendTo($('form#limesurvey'));
                $('#changelangbtn').click();
            }else{
                if($(this).data('targeturl')){
                    var target=$(this).data('targeturl');
                }else{
                    var target=document.location.href;
                }
                $('<form>', {
                    "html": '<input type="hidden" name="lang" value="' + $(this).find('option:selected').val() + '" />',
                    "action": target,
                    "method": 'post'
                }).appendTo(document.body).append($("input[name='YII_CSRF_TOKEN']")).submit();
            }
        }else{
            $(this).closest('form').find("[name='lang']").not($(this)).remove();// Remove other lang
            $('#changelangbtn').click();
        }
    });
    $(function(){
        $(".changelang.jshide").hide();
    });
}
/**
 * Manage the index
 */
function manageIndex(){
    $("#index .jshide").hide();
    $("#index").on('click','li,.row',function(e){ 
        if(!$(e.target).is('button')){
            $(this).children("[name='move']").click();
        }
    });
    $(function() {
        $(".outerframe").addClass("withindex");
        var idx = $("#index");
        var row = $("#index .row.current");
        if(row.length)
            idx.scrollTop(row.position().top - idx.height() / 2 - row.height() / 2);
    });
}
/**
 * Put a empty class on empty answer text item (limit to answers part) 
 * @author Denis Chenu / Shnoulle
 */
function addClassEmpty()
{
	$('.answer-item input.text[value=""]').addClass('empty');
	$('.answer-item input[type=text][value=""]').addClass('empty');
	$('.answer-item textarea').each(function(index) {
	if ($(this).val() == ""){
		$(this).addClass('empty');
	}
	});
	$("body").delegate(".answer-item input.text,.text-item input[type=text],.answer-item textarea","blur focusout",function(){
	if ($(this).val() == ""){
		$(this).addClass('empty');
	}else{
		$(this).removeClass('empty');
	}
	});
}

/**
 * Disable scroll on select, put it in function to allow update in template
 * 
 */
function noScrollOnSelect()
{
    $(".question").find("select").each(function () {
        hookEvent($(this).attr('id'),'mousewheel',noScroll);
    });
}
/**
 * Adapt cell to have a click on cell do a click on input:radio or input:checkbox (if unique)
 * Using delegate the can be outside document.ready (using .on is possible but on $(document) then : less readbale
 * @author Denis Chenu / Shnoulle
 */
function tableCellAdapters()
{
//	$('table.question').delegate('tbody td input:checkbox,tbody td input:radio,tbody td label',"click", function(e) {
//		e.stopPropagation();
//	});
	$(document).on('click','table.question tbody td',function(event) {// 'table.question tbody td' or 'td.radio-item,td.checkbox-item': maybe less js here
		var eventTarget=$(event.target).prop("tagName");// Alternative us data
		var eventActivate=$(this).find("input:radio,input:checkbox");
		if(eventActivate.length==1 && (eventTarget!='INPUT' && eventTarget!='LABEL' ) )
		{
			$(eventActivate).click();
			$(eventActivate).triggerHandler("click");
			// Why not use trigger('click'); only ?
		}
	});
}

//defined in group.php & question.php & survey.php, but a static function
function inArray(needle, haystack)
{
	for (h in haystack)
	{
		if (haystack[h] == needle)
		{
			return true;
		}
	}
	return false;
}

//defined in group.php & survey.php, but a static function
function match_regex(testedstring,str_regexp)
{
	// Regular expression test
	if (str_regexp == '' || testedstring == '') return false;
	pattern = new RegExp(str_regexp);
	return pattern.test(testedstring)
}

function addHiddenField(theform,thename,thevalue)
{
	var myel = document.createElement('input');
	myel.type = 'hidden';
	myel.name = thename;
	theform.appendChild(myel);
	myel.value = thevalue;
}


function noScroll(e)
{
  e = e ? e : window.event;
  cancelEvent(e);
}


function getkey(e)
{
   if (window.event) return window.event.keyCode;
    else if (e) return e.which; else return null;
}

function goodchars(e, goods)
{
    var key, keychar;
    key = getkey(e);
    if (key == null) return true;

    // get character
    keychar = String.fromCharCode(key);
    keychar = keychar.toLowerCase();
    goods = goods.toLowerCase();

   // check goodkeys
    if (goods.indexOf(keychar) != -1)
        return true;

    // control keys
    if ( key==null || key==0 || key==8 || key==9  || key==27 )
      return true;

    // else return false
    return false;
}


// round function from phpjs.org
function round (value, precision, mode) {
    // http://kevin.vanzonneveld.net
    var m, f, isHalf, sgn; // helper variables
    precision |= 0; // making sure precision is integer
    m = Math.pow(10, precision);
    value *= m;
    sgn = (value > 0) | -(value < 0); // sign of the number
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
        case 'PHP_ROUND_HALF_DOWN':
            value = f + (sgn < 0); // rounds .5 toward zero
            break;
        case 'PHP_ROUND_HALF_EVEN':
            value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
            break;
        case 'PHP_ROUND_HALF_ODD':
            value = f + !(f % 2); // rounds .5 towards the next odd integer
            break;
        default:
            value = f + (sgn > 0); // rounds .5 away from zero
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
}
function doToolTipTable()
{
   $(document).on("mouseover"," td.answer-item",function(){
        $( this).attr('title',$(this).find("label").text());
    });
}

