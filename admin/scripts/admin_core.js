//$Id$    

$(document).ready(function(){
    setupAllTabs();
    if(typeof(userdateformat) !== 'undefined') 
    {
        $(".popupdate").datepicker({dateFormat: userdateformat,  
                              showOn: 'button',
                              changeYear: true, 
                              changeMonth: true, 
                              duration: 'fast'
                            }, $.datepicker.regional[userlanguage]);
        $(".popupdatetime").datepicker({dateFormat: userdateformat+' 00:00',  
                              showOn: 'button',
                              changeYear: true, 
                              changeMonth: true, 
                              duration: 'fast'
                            }, $.datepicker.regional[userlanguage]);
    }

    $('button,input[type=submit],input[type=button],input[type=reset]').addClass("limebutton ui-state-default ui-corner-all");   
    $('button,input[type=submit],input[type=button],input[type=reset]').hover(
        function(){ 
            $(this).addClass("ui-state-hover"); 
        },
        function(){ 
            $(this).removeClass("ui-state-hover"); 
        }
    )
    

    // Loads the tooltips for the toolbars  except the surveybar
    $('img[alt],input[src]').not('.surveybar img').each(function() {
        if($(this).attr('alt') != '')
        {
             $(this).qtip({
               style: {name: 'cream',
                        tip:true, 
                        color:'#1D2D45', 
                        border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: {adjust: { 
                        screen: true, scroll:true},
                        corner: {
                                target: 'bottomRight'}
                        },
               show: {effect: {length:50}},
 			   hide: {when: 'mouseout'},
 			   api: {onRender: function() {$(this.options.hide.when.target).bind('click', this.hide);}}

               });
        }
    });    

    $(".progressbar").each(function(){
        var pValue = parseInt($(this).attr('name'));
    
        $(this).progressbar({
                            value: pValue
        });

        if (pValue > 85){
            $("div",$(this)).css({ 'background': 'Red' });
        }
	
	$("div",this).html(pValue + "%");
    });
    
    
    
    $('label[title]').each(function() {
        if($(this).attr('title') != '')
        {
             $(this).qtip({
               style: {name: 'cream',
                        tip:true, 
                        color:'#1D2D45', 
                        border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: {adjust: { 
                        screen: true, scroll:true},
                        corner: {
                                target: 'bottomRight'}
                        },
               show: {effect: {length:50}}
               });
        }
    });    
    
    $('.dosurvey').qtip({
        content:{
                text:$('#dosurveylangpopup')
        },
        style: {name: 'cream',
                        tip:true, 
                color:'#1D2D45', 
                border: {
                     width: 1,
                     radius: 5,
                     color: '#EADF95'}
               },  
        position: {adjust: { 
                screen: true, scroll:true},
                corner: {
                        target: 'bottomMiddle',
                        tooltip: 'topMiddle'}
                },
        show: {effect: {length:50},
               when: {
                   event:'click'
               }},
        hide: {fixed:true,
               when: {
                   event:'unfocus'
               }}
    }); 

    $('#previewquestion').qtip({
        content:{
                text:$('#previewquestionpopup')
        },
        style: {name: 'cream',
                        tip:true, 
                color:'#111111', 
                border: {
                     width: 1,
                     radius: 5,
                     color: '#EADF95'}
               },  
        position: {adjust: { 
                screen: true, scroll:true},
                corner: {
                        target: 'bottomMiddle',
                        tooltip: 'topMiddle'}
                },
        show: {effect: {length:50},
               when: {
                   event:'click'
               }},
        hide: {fixed:true,
               when: {
                   event:'unfocus'
               }}
    });            
    
    $('.tipme').each(function() {
        if($(this).attr('alt') != '')
        {
             $(this).qtip({
               style: {name: 'cream',
                        tip:true, 
                        color:'#111111', 
                        border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: {adjust: { 
                        screen: true, scroll:true},
                        corner: {
                                target: 'topRight',
                                tooltip: 'bottomLeft'
                        }
                        },
               show: {effect: {length:100}}

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

    $('#MinimizeGroupWindow').click(function(){
        $('#groupdetails').hide();
    });     
    $('#MaximizeGroupWindow').click(function(){
        $('#groupdetails').show();
    });
    $('#tabs').tabs();
    $("#flashmessage").notify().notify('create','themeroller',{},{custom:true,
    speed: 500,
    expires: 5000
});

    var old_owner = '';

    $(".ownername_edit").live('click',function(){
       var oldThis = this;
       var ownername_edit_id = $(this).attr('id');
       var survey_id = ownername_edit_id.slice(15);
       $.getJSON('admin.php', {
                    action: 'ajaxgetusers'
                },function(oData)
                {
                    old_owner =  $($(oldThis).parent()).html();
                    old_owner = (old_owner.split(" "))[0];
                    $($(oldThis).parent()).html('<select class="ownername_select" id="ownername_select_'+survey_id+'"></select>'
                    + '<input class="ownername_button" id="ownername_button_'+survey_id+'" type="button" value="Update">');
                    $(oData).each(function(key,value){
                        $('#ownername_select_'+survey_id).
                          append($("<option id='opt_"+value[1]+"'></option>").
                          attr("value",value[0]).
                          text(value[1]));
                    });
                    $("#ownername_select_"+survey_id+ " option[id=opt_"+old_owner+"]").attr("selected","selected");
         });
    });

    $(".ownername_button").live('click',function(){
       var oldThis = this;
       var ownername_select_id = $(this).attr('id');
       var survey_id = ownername_select_id.slice(17);
       var newowner = $("#ownername_select_"+survey_id).val();

       $.getJSON('admin.php',{
            action: 'ajaxowneredit',
            newowner: newowner,
            survey_id : survey_id
       }, function (data){
           var objToUpdate = $($(oldThis).parent());
           if (data.record_count>0)
               $(objToUpdate).html(data.newowner);
           else
               $(objToUpdate).html(old_owner);

           $(objToUpdate).html($(objToUpdate).html() + '(<a id="ownername_edit_69173" class="ownername_edit" href="#">Edit</a>)' );
       });
    });

    if ($("#question_type").length > 0){
        $("#question_type").msDropDown();

        $("#question_type").change(function(event){
           var selected_value = qDescToCode[''+$("#question_type_child .selected").text()];
           OtherSelection(selected_value);
	});

        $.getScript('../scripts/jquery/jquery-qtip.js', function() {
            $("#question_type_child a").each(function(index,element){

                $(element).qtip({
                       style: {
                                    'margin' : '15px',
                                    'width': '450px',
                                    'height':'auto',
                                    'border':{
                                            width: 4,
                                            radius: 2
                                    }
                            },
                       content: getToolTip($(element).text()),
                       position: {
                                    corner:{
                                            target: 'leftMiddle',
                                            tooltip:'rightMiddle'
                                    }
                            },
                       show: 'mouseover',
                       hide: 'mouseout'
                });

            });
        });

    }
    
    
    
});

var aToolTipData = {

};

var qDescToCode;
var qCodeToInfo;

function getToolTip(type){
    var code = qDescToCode[''+type];
    var multiple = 0;
    if (code=='S') multiple = 2;
    
    if (code == ":") code = "COLON";
    else if(code == "|") code = "PIPE";

    if (multiple > 0){
        returnval = '';
        for(i=1;i<=multiple;i++){
            returnval = returnval + "<img src='../images/screenshots/"+code+i+".png' /><br /><br />";
        }
        return returnval;
    }
    return "<img src='../images/screenshots/"+code+".png' />";
}

//We have form validation and other stuff..

function updatequestionattributes()
{
        $('.loader').show();
        $('#advancedquestionsettings').html('');
        var selected_value = qDescToCode[''+$("#question_type_child .selected").text()];
        if (selected_value==undefined) selected_value = $("#question_type").val();
        $('#advancedquestionsettings').load('admin.php?action=ajaxquestionattributes',{qid:$('#qid').val(),
                                                                                   question_type:selected_value,
                                                                                   sid:$('#sid').val()
                                                                                  }, function(){
            // Loads the tooltips for the toolbars
            
            // Loads the tooltips for the toolbars
           $('.loader').hide();
            $('label[title]').qtip({
               style: {name: 'cream', 
                         tip: true, 
                       color:'#111111', 
                      border: {
                             width: 1,
                             radius: 5,
                             color: '#EADF95'}
                       },  
               position: {adjust: { 
                        screen: true, scroll:true},
                        corner: {
                                target: 'bottomRight'}
                        },
               show: {effect: {length:50}}
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
	}}
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
		if (found == 0) {return false;}
	}
	return true;
}

function isset( variable )
{
      return( typeof( variable ) != 'undefined' );
}

String.prototype.splitCSV = function(sep) {
  for (var foo = this.split(sep = sep || ","), x = foo.length - 1, tl; x >= 0; x--) {
    if (foo[x].replace(/"\s+$/, '"').charAt(foo[x].length - 1) == '"') {
      if ((tl = foo[x].replace(/^\s+"/, '"')).length > 1 && tl.charAt(0) == '"') {
        foo[x] = foo[x].replace(/^\s*"|"\s*$/g, '').replace(/""/g, '"');
      } else if (x) {
        foo.splice(x - 1, 2, [foo[x - 1], foo[x]].join(sep));
      } else foo = foo.shift().split(sep).concat(foo);
    } else foo[x].replace(/""/g, '"');
  }return foo;
};

// This is a helper function to extract the question ID from a DOM ID element 
function removechars(strtoconvert){
  return strtoconvert.replace(/[-a-zA-Z_]/g,"");
}


function htmlspecialchars(str) {
 if (typeof(str) == "string") {
  str = str.replace(/&/g, "&amp;"); /* must do &amp; first */
  str = str.replace(/"/g, "&quot;");
  str = str.replace(/'/g, "&#039;");
  str = str.replace(/</g, "&lt;");
  str = str.replace(/>/g, "&gt;");
  }
 return str;
}


function saveaslabelset()
{
    var lang = langs.split(";");
    

    dataToSend = {};
    dataToSend['langs'] = lang;
    dataToSend['codelist'] = [];
    $(".answertable:first tbody tr").each(function(i,e){
        code = $(".code",e).attr('id');
        code = code.split("_");
        code = code[1];

        dataToSend['codelist'].push(code);
        var assessment_val = '0';
        if ($("#assessment_"+code+"_0").length != 0 ){
            assessment_val = $("#assessment_"+code+"_0").val();
        }
        dataToSend[code] =  {
            code: $("#code_"+code+"_0").val(),
            assessmentvalue: assessment_val
        };
        $(lang).each(function(index,element){
            dataToSend[code]['text_'+element] = $("#answer_"+element+"_"+code+"_0").val();
            
        });
    });

    var label_name = prompt("Enter new label name", "");
    
    var data = {
        action: 'ajaxmodlabelsetanswers',
        lid:'1',
        dataToSend:js2php(dataToSend),
        ajax:'1',
        label_name:label_name,
        languageids: dataToSend['langs'].join(" "),
        checksessionbypost: $("[name=checksessionbypost]").val()
    }

    $.ajax({
      type: 'POST',
      url: 'admin.php',
      data: data,
      success: function(){
          alert("Label successfully created");
      }
    });
}


function js2php(object){
    var json = "{";
    for (property in object){
        var value = object[property];
        if (typeof(value)=="string"){
            json += '"'+property+'":"'+value+'",'
        }
        else{
            if (!value[0]){
                json += '"'+property + '":'+js2php(value)+',';
            }
            else{
                json += '"' + property + '":[';
                for (prop in value) json += '"'+value[prop]+'",';
                json = json.substr(0,json.length-1)+"],";
            }
        }
    }
    return json.substr(0,json.length-1)+ "}";
}

