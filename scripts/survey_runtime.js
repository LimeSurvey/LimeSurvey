/*
 * JavaScript functions in survey taking
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

limesurveySubmitHandler();
needConfirmHandler();

$(document).ready(function()
{
    navbuttonsJqueryUi();
    addClassEmpty();
    if (typeof LEMsetTabIndexes === 'function') { LEMsetTabIndexes(); }
	if (typeof checkconditions!='undefined') checkconditions();
	if (typeof template_onload!='undefined') template_onload();
	tableCellAdapters();
    if (typeof(focus_element) != 'undefined')
    {
        $(focus_element).focus();
    }
    $(".question").find("select").each(function () {
        hookEvent($(this).attr('id'),'mousewheel',noScroll);
    });

    // Keypad functions
    var kp = $("input.num-keypad");
    if(kp.length)
	{ 
		kp.keypad({
			showAnim: 'fadeIn', keypadOnly: false,
			onKeypress: function(key, value, inst) { 
				$(this).trigger('keyup');
			}
		});
	}
    kp = $(".text-keypad");
    if(kp.length)
    {
        var spacer = $.keypad.HALF_SPACE;
        for(var i = 0; i != 8; ++i) spacer += $.keypad.SPACE;
	    kp.keypad({
		    showAnim: 'fadeIn',
		    keypadOnly: false,
		    layout: [
                spacer + $.keypad.CLEAR + $.keypad.CLOSE, $.keypad.SPACE,
			    '!@#$%^&*()_=' + $.keypad.HALF_SPACE + $.keypad.BACK,
			    $.keypad.HALF_SPACE + '`~[]{}<>\\|/' + $.keypad.SPACE + $.keypad.SPACE + '789',
			    'qwertyuiop\'"' + $.keypad.HALF_SPACE + $.keypad.SPACE + '456',
			    $.keypad.HALF_SPACE + 'asdfghjkl;:' + $.keypad.SPACE + $.keypad.SPACE + '123',
			    $.keypad.SPACE + 'zxcvbnm,.?' + $.keypad.SPACE + $.keypad.SPACE + $.keypad.HALF_SPACE + '-0+',
			    $.keypad.SHIFT + $.keypad.SPACE_BAR + $.keypad.ENTER],
				onKeypress: function(key, value, inst) { 
					$(this).trigger('keyup');
				}
			});
    }

    // Maxlength for textareas TODO limit to not CSS3 compatible browser
    maxlengthtextarea();

});

// Deactivate all other button on submit
function limesurveySubmitHandler(){
    $("#limesurvey").on("click",".disabled",function(){return false;});
    $(document).on('click',"button[type='submit'],a.button", function(event){
        $("button[type='submit']").not($(this)).prop('disabled',true);
        $("a.button").not($(this)).addClass('disabled');
    });
    if('v'=='\v'){ // Quick hack for IE6/7/ Alternative ? http://tanalin.com/en/articles/ie-version-js/ ?
        $(function() { 
            $("#defaultbtn").css('display','inline').css('width','0').css('height','0').css('padding','0').css('margin','0').css('overflow','hidden');
            $("#limesurvey [type='submit']").not("#defaultbtn").first().before($("#defaultbtn"));
        });
    }
}


// Ask confirmation on click on .needconfirm
function needConfirmHandler(){
    $("body").on('click',".confirm-needed", function(event){
        text=$(this).attr('title');
        if (confirm(text)) {
            return true;
        }
        return false;
    });
    /* 130712 IE7 need this */
    $(function() {
    $("a.confirm-needed").click(function(e){
        text=$(this).attr('title');
        if (confirm(text)) {
            return true;
        }
        return false;
        });
    });
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
    // TODO trigger handler activate/deactivate to update ui-button class
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
 * Adapt cell to have a click on cell do a click on input:radio or input:checkbox (if unique)
 * Using delegate the can be outside document.ready
 * @author Denis Chenu / Shnoulle
 */
function tableCellAdapters()
{
	$('table.question').delegate('tbody td input:checkbox,tbody td input:radio,tbody td label',"click", function(e) {
		e.stopPropagation();
	});
	$('table.question').delegate('tbody td',"click", function() {
		if($(this).find("input:radio,input:checkbox").length==1)
		{
			$(this).find("input:radio").click();
			$(this).find("input:radio").triggerHandler("click");
			$(this).find("input:checkbox").click();
			$(this).find("input:checkbox").triggerHandler("click");
		}
	});
}

Array.prototype.push = function()
{
	var n = this.length >>> 0;
	for (var i = 0; i < arguments.length; i++)
	{
		this[n] = arguments[i];
		n = n + 1 >>> 0;
	}
	this.length = n;
	return n;
};

Array.prototype.pop = function() {
	var n = this.length >>> 0, value;
	if (n) {
		value = this[--n];
		delete this[n];
	}
	this.length = n;
	return value;
};


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

function cancelBubbleThis(eventObject)
{
	if (!eventObject) var eventObject = window.event;
	eventObject.cancelBubble = true;
	if (eventObject && eventObject.stopPropagation) {
		eventObject.stopPropagation();
	}
}

function cancelEvent(e)
{
  e = e ? e : window.event;
  if(e.stopPropagation)
    e.stopPropagation();
  if(e.preventDefault)
    e.preventDefault();
  e.cancelBubble = true;
  e.cancel = true;
  e.returnValue = false;
  return false;
}

function hookEvent(element, eventName, callback)
{
  if(typeof(element) == "string")
    element = document.getElementById(element);
  if(element == null)
    return;
  if(element.addEventListener)
  {
    if(eventName == 'mousewheel')
      element.addEventListener('DOMMouseScroll', callback, false);
    element.addEventListener(eventName, callback, false);
  }
  else if(element.attachEvent)
    element.attachEvent("on" + eventName, callback);
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

function show_hide_group(group_id)
{
	var questionCount;

	// First let's show the group description, otherwise, all its childs would have the hidden status
	$("#group-" + group_id).show();
	// If all questions in this group are conditionnal
	// Count visible questions in this group
		questionCount=$("div#group-" + group_id).find("div[id^='question']:visible").size();

		if( questionCount == 0 )
		{
			$("#group-" + group_id).hide();
		}
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

// ==========================================================
// totals

function multi_set(ids,_radix)
{
	//quick ie check
	var ie=(navigator.userAgent.indexOf("MSIE")>=0)?true:false;
	//match for grand
	var _match_grand = new RegExp('grand');
	//match for total
	var _match_total = new RegExp('total');
    var radix = _radix; // comma, period, X (for not using numbers only)
    var numRegex = new RegExp('[^-' + radix + '0-9]','g');
	//main function (obj)
	//id = wrapper id
	function multi_total(id)
	{
		if(!document.getElementById(id)){return;};
		//alert('multi total called value ' + id);
		//generic vars
		//grand total 0 = none, 1 = horo, 2 = vert set if grand found
		var _grand = 0;
		//multi array holder
		var _bits = new Array();

		//var _obj = document.getElementById(id);
		//grab the tr's
		var _obj = document.getElementById(id);//.getElementsByTagName('table');

		//alert(_obj.length);
		var _tr = _obj.getElementsByTagName('tr');
		//counter used in top level of _bits array
		var _counter = 0;
		//generic for vars
		var _i = 0;
		var _l = _tr.length;
		for(_i=0; _i<_l; _i++)
		{
			//check we really have inputs to deal with
			if(_tr[_i].getElementsByTagName('input'))
			{
				var _td = _tr[_i].getElementsByTagName('td');
				//start building some nice arrays
				_bits.push(new Array());
				//clear the vert var set when total found in tr
				var vert =false;
				if(_tr[_i].className && _tr[_i].className.match(_match_total,'ig'))
				{
					//will need to set it up vertical
					vert = true;
				};
				//generic for vars for second level _bits[_i]
				var _a=0;
				var _al = _td.length;
				//alert(_al + ' ' + _i);
				if(_al > 0)
				{
				//	//counter for inner array
					var _tcounter=0;
					for(_a=0; _a < _al; _a++)
					{
						//only bother if we have inputs
						if(_td[_a].getElementsByTagName('input'))
						{
							//grab the first text input
							var _tdin = first_text(_td[_a].getElementsByTagName('input'));
							//check we got a text input
							if(_tdin)
							{
								//add it to the array @ counter
								_bits[_counter].push(_tdin);
								//set key board actions
								_tdin.onkeyup = calc;
								//check for total and grand total
								if(_td[_a].className && _td[_a].className.match(_match_total,'ig'))
								{
									//clear the key events with false returns
									_tdin.onkeydown = dummy;
									_tdin.onkeyup = dummy;
									//need to check for grand
									if(_td[_a].className.match(_match_grand,'ig'))
									{
										//set up a grand total
										if(vert && _bits[_counter].length > 1)
										{
											_grand=1;
                                            //run calc across last row
                                            calc_horo(_bits.length - 1);
										}
										else
										{
											_grand=2;
											_bits[_counter][_bits[0].length - 1]=_bits[_counter][0];
                                            //run calc on last col
                                            calc_vert(_bits[0].length - 1);
										}
									}
									else
									{
										//set up horo
										horo_set_up(_counter);
									};

								};
								if(vert && _grand == 0)
								{
									//deal with vert calc and clear the keyboard action
									_tdin.onkeydown = dummy;
									_tdin.onkeyup = dummy;
									vert_set_up(_tcounter);

								};
								_tcounter++;
							};
						};

					};
					//check we got some thing that time
					if(_bits[_counter].length == 0)
					{
						_bits.pop();
					}
					else
					{
						_counter++;
					}
				}
				else
				{
					//remove blanks
					_bits.pop();
				}

			};
		};
		//returns the first text input or false
		function first_text(arr)
		{
			var i=0;
			var l=arr.length;
			for(i=0; i<l; i++)
			{
				if(arr[i].getAttribute('type') && arr[i].getAttribute('type') == 'text')
				{
					return(arr[i]);
				}
			}
			return(false);
		}
		//sets up the horizontal calc
		function horo_set_up(id)
		{
			//make all in the row update the final
			//alert('set horo called for row ' + id);

			var i=0;
			var l=_bits[id].length;
			var qt=0;
			for(i=0; i<l; i++)
			{
				var addaclass=!_bits[id][i].getAttribute(ie ? 'className' : 'class') ? '' : _bits[id][i].getAttribute(ie ? 'className' : 'class') + ' ';
				_bits[id][i].setAttribute((ie ? 'className' : 'class'), addaclass + 'horo_' + id);
				_bits[id][i].onkeyup = calc;
				if(i == (l - 1))
				{
					_bits[id][i].value = round(qt,12)
				}
				else if(_bits[id][i].value)
				{
                    _aval=_bits[id][i].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                        _bits[id][i].value = _aval.split('.').join(',');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};

		}
		//sets up the vertical calc
		function vert_set_up(id)
		{
			//alert('set vert called for col ' + id + ' ' + _bits.join('-'));
			id *= 1;
			var i=0;
			var l=_bits.length;
			var qt = 0;
			for(i=0; i<l; i++)
			{
				var addaclass=!_bits[i][id].getAttribute(ie ? 'className' : 'class') ? '' : _bits[i][id].getAttribute(ie ? 'className' : 'class') + ' ';
				_bits[i][id].setAttribute((ie ? 'className' : 'class'), addaclass + 'vert_' + id);
				_bits[i][id].onkeyup = calc;
				if(i == (l - 1))
				{
					_bits[i][id].value = round(qt,12);
				}
				else if(_bits[i][id].value)
				{
                    _aval=_bits[i][id].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                        _bits[i][id].value = _aval.split('.').join(',');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};
		};
		//calculates a row or col or both
		//runs the grand totals if required
		function calc(e)
		{
			//alert('calc called ');
			e=(e)?e:event;
			var el=e.target||e.srcElement;
			var _id=el.getAttribute(ie ? 'className' : 'class');

            // eliminate bad numbers
            _aval=new String(el.value);
            if (radix!=='X') {
                _aval=_aval.replace(numRegex,'');
            }
            if (radix===',') {
                _aval = _aval.split(',').join('.');
            }
            if (radix!=='X' && _aval != '-' && _aval != '.' && _aval != '-.' && _aval != parseFloat(_aval)) {
                _aval = "";
            }
            if (radix===',') {
                el.value = _aval.split('.').join(',');
            }
            else if (radix!=='X') {
                el.value = _aval;
            }

			//vert_[id] horo_[id] in class trigger vert or horo calc on row[id]
			if(_id.match('vert_','ig'))
			{
				var vid = get_an_id(_id,'vert_');
				calc_vert(vid);
			};
			if(_id.match('horo_','ig'))
			{
				var hid = get_an_id(_id,'horo_');
				calc_horo(hid);
			};
			//check for grand total
			switch(_grand)
			{
				case 1:
				//run calc across last row
					calc_horo(_bits.length - 1);
				 	break;
				case 2:
				//run calc on last col
					calc_vert(_bits[0].length - 1);
					break;
			}
            checkconditions($(el).val(), $(el).attr('name'), $(el).attr('type'));
			return(true);
		};
		//retuns the id from end of string like 'vert_[id] horo_[id] other class'
		//_id = string
		//_break = string to break @
		function get_an_id(_id,_break)
		{
			var id = _id.split(_break);
			id[1] = id[1].split(' ');
			return(id[1][0] * 1);
		};
		//run vert calc on col[vid]
		function calc_vert(vid)
		{
			var i=0;
			var l=_bits.length;
			var qt = 0;
			//get or set the last ones id
			for(i=0; i<l; i++)
			{
				if(i == (l - 1))
				{
					//check if sum is a number
                    if(isNaN(qt))
                    {
                        _bits[i][vid].value = "Not a number";
                    }
                    else
                    {
                        _bits[i][vid].value = round(qt,12);
                    }
				}
				else if(_bits[i][vid].value)
				{
                    _aval=_bits[i][vid].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};

		};
		//run horo calc on row[hid]
		function calc_horo(hid)
		{
			var i=0;
			var l=_bits[hid].length;
			var qt=0;
			for(i=0; i<l; i++)
			{
				if(i == (l - 1))
				{
					if (isNaN(qt))
                    {
                        _bits[hid][i].value = "Not a number"
                    }
                    else
                    {
                        _bits[hid][i].value = round(qt,12);
                    }
				}
				else if(_bits[hid][i].value)
				{
                    _aval=_bits[hid][i].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};
		};
		//clear key input
		function dummy(e)
		{
			return(false);
		};
	};
	//set up the dom
	//alert('multi called called value ' + ids);
	ids = ids.split(',');
	//generic for vars
	var ii = 0;
	var ll=ids.length;
	//object place holder
	var _collection=new Array();

	for(ii=0; ii<ll; ii++)
	{
		//run main function per id
		_collection.push(new multi_total(ids[ii]));
	}
}

//Special function for array dual scale in drop down layout to check conditions
/* 
Deactivated 20130221
Why do this: a user can answer one select and not another one
Never change default behaviour if it's not good for respondant
*/
//function array_dual_dd_checkconditions(value, name, type, rank, condfunction)
//{
//   if (value == '') {
//        //If value is set to empty, reset both drop downs and check conditions
//        if (rank == 0) { dualname = name.replace(/#0/g,"#1"); }
//        else if (rank == 1) { dualname = name.replace(/#1/g,"#0"); }
//        document.getElementsByName(dualname)[0].value=value;
//        condfunction(value, dualname, type);
//   }
//    condfunction(value, name, type);
//}

/* Maxlengt on textarea */
function maxlengthtextarea(){
    // Calling this function at document.ready : use maxlength attribute on textarea
    // Can be replaced by inline javascript
    $("textarea[maxlength]").change(function(){ // global solution
        var maxlen=$(this).attr("maxlength");
        if ($(this).val().length > maxlen) {
            $(this).val($(this).val().substring(0, maxlen));
        }
    });
    $("textarea[maxlength]").keyup(function(){ // For copy/paste (not for all browser)
        var maxlen=$(this).attr("maxlength");
        if ($(this).val().length > maxlen) {
            $(this).val($(this).val().substring(0, maxlen));
        }
    });
    $("textarea[maxlength]").keydown(function(event){ // No new key after maxlength
        var maxlen=$(this).attr("maxlength");
        var k =event.keyCode;
        if (($(this).val().length >= maxlen) &&
         !(k == null ||k==0||k==8||k==9||k==13||k==27||k==37||k==38||k==39||k==40||k==46)) {
            // Don't accept new key except NULL,Backspace,Tab,Enter,Esc,arrows,Delete
            return false;
        }
    });
}
