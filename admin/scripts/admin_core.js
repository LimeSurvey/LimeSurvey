$(document).ready(function(){
    setupAllTabs();
    if(typeof(userdateformat) !== 'undefined') 
    {
        $(".popupdate").datepicker({ dateFormat: userdateformat,  
                              showOn: 'button',
                              changeYear: true, 
                              changeMonth: true, 
                              duration: 'fast'
                            }, $.datepicker.regional[userlanguage]);
        $(".popupdatetime").datepicker({ dateFormat: userdateformat+' 00:00',  
                              showOn: 'button',
                              changeYear: true, 
                              changeMonth: true, 
                              duration: 'fast'
                            }, $.datepicker.regional[userlanguage]);
    }


    // Loads the tooltips for the toolbars
    $('img[alt],input[src]').each(function() {
        if($(this).attr('alt') != '')
        {
             $(this).qtip({
               style: { name: 'cream',
                        tip:true, 
                        color:'#111111', 
                        border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: { adjust: { 
                        screen: true, scroll:true },
                        corner: {
                                target: 'bottomRight'}
                        },
               show: {effect: { length:50}}

});
        }
    });    

    $('#noncompletedlbl[title]').each(function() {
        if($(this).attr('title') != '')
        {
             $(this).qtip({
               style: { name: 'cream',
                        tip:true, 
                        color:'#111111', 
                        border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: { adjust: { 
                        screen: true, scroll:true },
                        corner: {
                                target: 'bottomRight'}
                        },
               show: {effect: { length:50}}

});
        }
    });    


    if ($('#showadvancedattributes').length>0) updatequestionattributes();
    
    $('#showadvancedattributes').click(function(){
        $('#showadvancedattributes').hide();
        $('#hideadvancedattributes').show();
        $('#advancedquestionsettingswrapper').animate({
          "height": "toggle", "opacity": "toggle"
        });

    })                                                                                 
    $('#hideadvancedattributes').click(function(){
        $('#showadvancedattributes').show();
        $('#hideadvancedattributes').hide();
        $('#advancedquestionsettingswrapper').animate({
          "height": "toggle", "opacity": "toggle"
        });

    }) 
    $('#question_type').change(updatequestionattributes);
});


//We have form validation and other stuff..

function updatequestionattributes()
{
        $('.loader').show();
        $('#advancedquestionsettings').html('');
        $('#advancedquestionsettings').load('admin.php?action=ajaxquestionattributes',{qid:$('#qid').val(),
                                                                                   question_type:$('#question_type').val(),
                                                                                   sid:$('#sid').val()
                                                                                  }, function(){
            // Loads the tooltips for the toolbars
            
            // Loads the tooltips for the toolbars
           $('.loader').hide();
            $('label[title]').qtip({
               style: { name: 'cream', 
                         tip: true, 
                       color:'#111111', 
                      border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: { adjust: { 
                        screen: true, scroll:true },
                        corner: {
                                target: 'bottomRight'}
                        },
               show: {effect: { length:50}}
            });}                                                                                        
    );  
}

function validatefilename (form, strmessage )
{
    if (form.the_file.value == "") {
        alert( strmessage );
        form.the_file.focus();
        return false ;
    }
    return true ;
}


// If the length of the element's string is 0 then display helper message
function isEmpty(elem, helperMsg)
{
	if(elem.value.length == 0){
		alert(helperMsg);
		elem.focus(); // set the focus to this input
		return false;
	}
	return true;
}

function codeCheck(prefix, elementcount, helperMsg, reservedKeywordMsg)
{
    var i, j;
    var X = new Array();
    
    for (i=0; i<=elementcount; i++) {
        j = document.getElementById(prefix+i);
        if (j != undefined) 
        {
           j.value=trim(j.value);
           if (j.value == "other")
           {
              alert(reservedKeywordMsg);
              return false;
           }
           X.push(j.value);
        }
    }   
    if (arrHasDupes(X))
    {
    	alert(helperMsg);
        return false;
    }
    return true;
}


function arrHasDupes( A ) {                          // finds any duplicate array elements using the fewest possible comparison
	var i, j, n;
	n=A.length;
                                                     // to ensure the fewest possible comparisons
	for (i=0; i<n; i++) {                        // outer loop uses each item i at 0 through n
		for (j=i+1; j<n; j++) {              // inner loop only compares items j at i+1 to n
			if (A[i]==A[j]) return true;
	}	}
	return false;
}


// (c) 2006 Simon Wunderlin, License: GPL, hacks want to be free ;)
// This fix forces Firefox to fire the onchange event if someone changes select box with cursor keys 
function ev_gecko_select_keyup_ev(Ev) {
	// prevent tab, alt, ctrl keys from fireing the event
	if (Ev.keyCode && (Ev.keyCode == 1 || Ev.keyCode == 9 || 
	    Ev.keyCode == 16 || Ev.altKey || Ev.ctrlKey))
		return true;
	Ev.target.onchange();
    	return true;
}

function init_gecko_select_hack() {
	var selects = document.getElementsByTagName("SELECT");
	for(i=0; i<selects.length; i++)
			selects.item(i).addEventListener("keyup", ev_gecko_select_keyup_ev, false);
	return true;
}


function getkey(e)
{
   if (window.event) return window.event.keyCode;
      else 
         if (e) return e.which; 
             else return null;
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


function trim(stringToTrim) {
    return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function DoAdd()
{
	if (document.getElementById("available_languages").selectedIndex>-1)
	{
		var strText = document.getElementById("available_languages").options[document.getElementById("available_languages").selectedIndex].text;
		var strId = document.getElementById("available_languages").options[document.getElementById("available_languages").selectedIndex].value;
		AddItem(document.getElementById("additional_languages"), strText, strId);
		RemoveItem(document.getElementById("available_languages"), document.getElementById("available_languages").selectedIndex);
		sortSelect(document.getElementById("additional_languages"));
		UpdateLanguageIDs();
	}
}

function DoRemove(minItems,strmsg)
{
	var strText = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].text;
	var strId = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].value;
	if (document.getElementById("additional_languages").options.length>minItems)
	{
		AddItem(document.getElementById("available_languages"), strText, strId);
		RemoveItem(document.getElementById("additional_languages"), document.getElementById("additional_languages").selectedIndex);
		sortSelect(document.getElementById("available_languages"));
		UpdateLanguageIDs();
	}
	else
	if (strmsg!=''){alert(strmsg);}
}



function AddItem(objListBox, strText, strId)
{
	var newOpt;
	newOpt = document.createElement("OPTION");
	newOpt = new Option(strText,strId);
	newOpt.id = strId;
	objListBox.options[objListBox.length]=newOpt;
}

function RemoveItem(objListBox, strId)
{
	if (strId > -1)
	objListBox.options[strId]=null;
}

function GetItemIndex(objListBox, strId)
{
	for (var i = 0; i < objListBox.children.length; i++)
	{
		var strCurrentValueId = objListBox.children[i].id;
		if (strId == strCurrentValueId)
		{
			return i;
		}
	}
	return -1;
}


function UpdateLanguageIDs(mylangs,confirmtxt)
{
	document.getElementById("languageids").value = '';

	var lbBox = document.getElementById("additional_languages");
	for (var i = 0; i < lbBox.options.length; i++)
	{
		document.getElementById("languageids").value = document.getElementById("languageids").value + lbBox.options[i].value+ ' ';
	}
	if (mylangs)
	{
		if (checklangs(mylangs))
		{
			return true;
		} else
		{
			return confirm(confirmtxt);
		}
	}
}

function compareText (option1, option2) {
	return option1.text < option2.text ? -1 :
	option1.text > option2.text ? 1 : 0;
}

function compareValue (option1, option2) {
	return option1.value < option2.value ? -1 :
	option1.value > option2.value ? 1 : 0;
}

function compareTextAsFloat (option1, option2) {
	var value1 = parseFloat(option1.text);
	var value2 = parseFloat(option2.text);
	return value1 < value2 ? -1 :
	value1 > value2 ? 1 : 0;
}

function compareValueAsFloat (option1, option2) {
	var value1 = parseFloat(option1.value);
	var value2 = parseFloat(option2.value);
	return value1 < value2 ? -1 :
	value1 > value2 ? 1 : 0;
}

function sortSelect (select, compareFunction) {
	if (!compareFunction)
	compareFunction = compareText;
	var options = new Array (select.options.length);
	for (var i = 0; i < options.length; i++)
	options[i] =
	new Option (
	select.options[i].text,
	select.options[i].value,
	select.options[i].defaultSelected,
	select.options[i].selected
	);
	options.sort(compareFunction);
	select.options.length = 0;
	for (var i = 0; i < options.length; i++)
	select.options[i] = options[i];
}

function checklangs(mylangs)
{
	selObject=document.getElementById("additional_languages");
	var found;

	for (x = 0; x < mylangs.length; x++)
	{
		found = 0;
		for (i=0;i<selObject.options.length;i++)
		{
			if(selObject.options[i].value == mylangs[x])
			{
				found = 1;
				break;
			}
		}
		if (found == 0) { return false; }
	}
	return true;
}
