var DOM1;
$(document).ready(function()
{
	DOM1 = (typeof document.getElementsByTagName!='undefined');
	if (typeof checkconditions!='undefined') checkconditions();
	if (typeof template_onload!='undefined') template_onload();
	prepareCellAdapters();
    if (typeof(focus_element) != 'undefined') 
    {
        $(focus_element).focus();
    }
    $(".question").find("select").each(function () {
        hookEvent($(this).attr('id'),'mousewheel',noScroll);
    });
        var kp = $("input.num-keypad");
        if(kp.length) kp.keypad({showAnim: 'fadeIn', keypadOnly: false});
        kp = $("input.text-keypad");
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
			        $.keypad.SHIFT + $.keypad.SPACE_BAR + $.keypad.ENTER]});
        }
        
        $(".location").each(function(index,element){
            var question = $(element).attr('name');
            var coordinates = $(element).val();
            var latLng = coordinates.split(" ");
            var question_id = question.substr(0,question.length-2);
            if ($("#mapservice_"+question_id).val()==1){
                // Google Maps
				if (gmaps[''+question]==undefined)
					gmaps[''+question] = GMapsInitialize(question,latLng[0],latLng[1]);
					}
            else if ($("#mapservice_"+question_id).val()==2){
                // Open Street Map
				if (osmaps[''+question]==undefined)
					osmaps[''+question] = OSMapInitialize(question,latLng[0],latLng[1]);
            }
        });
        $(".location").live('focusout',function(event){
            var question = $(event.target).attr('name');
            var name = question.substr(0,question.length - 2);
            var coordinates = $(event.target).attr('value');
            var xy = coordinates.split(" ");
            var currentMap = gmaps[question];
            var marker = gmaps["marker__"+question];
            var markerLatLng = new GLatLng(xy[0],xy[1]);
        	marker.setLatLng(markerLatLng);
        	var geocoder = new GClientGeocoder();
            geocoder.getLocations(markerLatLng,function(response){
            	
                parseGeocodeAddress(response,name);
            });
            currentMap.panTo(markerLatLng);
        });
        if ((typeof(autoArray) != "undefined")){
            if ((autoArray.list != 'undefined') && (autoArray.list.length > 0)){
                var aListOfQuestions = autoArray.list;

                $(aListOfQuestions).each(function(index,element){

                    var elementInfo = autoArray[element];
                    var strJSelector = "#answer" + (elementInfo.children.join(", #answer"));

                    var aJSelectors = strJSelector.split(", ");
                    var strCheckedSelector = (aJSelectors.join(":checked ,"))+":checked";

                    $(strJSelector).live('change',function(event){

                        if ($(strCheckedSelector).length == $(strJSelector).length){

                            $("#answer"+elementInfo.focus).trigger('click');

                            eval("excludeAllOthers"+elementInfo.parent + "('answer"+elementInfo.focus + "', 'yes')");

                            checkconditions($("#answer"+elementInfo.focus).val(),
                                            $("#answer"+elementInfo.focus).attr("name"),
                                            $("#answer"+elementInfo.focus).attr('type')
                                        );

                        }
                    });

                });
            }
        }
});

gmaps = new Object;
osmaps = new Object;
zoom = [];

// OSMap functions
function OSMapInitialize(question,lat,lng){
	 
    map = new OpenLayers.Map("gmap_canvas_" + question);
    map.addLayer(new OpenLayers.Layer.OSM());
    var lonLat = new OpenLayers.LonLat(lat,lng)
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
    var zoom=11;
    var markers = new OpenLayers.Layer.Markers( "Markers" );
    map.addLayer(markers);
    markers.addMarker(new OpenLayers.Marker(lonLat));
    map.setCenter (lonLat, zoom);
    return map;
    
}

// Google Maps Functions
function GMapsInitialize(question,lat,lng) {
    if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("gmap_canvas_" + question));
        var center = new GLatLng(lat,lng);
        map.addControl(new GSmallMapControl());

        var name = question.substr(0,question.length - 2);
        map.setCenter(center, zoom[name]);
        var marker = new GMarker(center, {draggable: true});
        GEvent.addListener(map, "singlerightclick", function(GP) {
        	var markerLatLng = map.fromContainerPixelToLatLng(GP);
        	marker.setLatLng(markerLatLng);
        	var geocoder = new GClientGeocoder();
            geocoder.getLocations(markerLatLng,function(response){
            	
                parseGeocodeAddress(response,name);
            });
            $("#answer"+question).val(Math.round(markerLatLng.lat()*10000)/10000 + " " + Math.round(markerLatLng.lng()*10000)/10000);
         
        });
        gmaps['marker__'+question] = marker;
        GEvent.addListener(marker, "dragend", function() {
           var markerLatLng = marker.getLatLng();
           var geocoder = new GClientGeocoder();
           geocoder.getLocations(markerLatLng,function(response){
               parseGeocodeAddress(response,name);
           });
           $("#answer"+question).val(Math.round(markerLatLng.lat()*10000)/10000 + " " + Math.round(markerLatLng.lng()*10000)/10000);
        });
        map.addOverlay(marker);
    }
    return map;
}

function parseGeocodeAddress(response, name){
	var city  = '';
	var state = '';
	var country = '';
	var postal = '';
	
  if (!(!response || response.Status.code != 200)) {
        place = response.Placemark[0];
        point = new GLatLng(place.Point.coordinates[1],
                            place.Point.coordinates[0]);
        var lat = place.Point.coordinates[1];
        var lng = place.Point.coordinates[0];
		
		
		
		try{
			city = place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName;
		}
		catch(e){
			city  = '';
		}
		
		try{
			state = place.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName;
		}
		catch(e){
			state  = '';
		}
		
		try{
			country = place.AddressDetails.Country.CountryNameCode;
		}
		catch(e){
			country  = '';
		}
		
        try{
        	postal = place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.PostalCode.PostalCodeNumber;
        }
		catch(e){
			postal  = '';
		}
		getInfoToStore(name,lat,lng,city,state,country,postal);
    }
	  else{
		  var latlong = (response.name).split(",");
		  
		  
		  getInfoToStore(name,latlong[0],latlong[1],city,state,country,postal);
	  }
}



// General Function
function getInfoToStore(name, lat,lng,city,state,country,postal){
    var boycott = $("#boycott_"+name).val();
    // 2 - city; 3 - state; 4 - country; 5 - postal
    if (boycott.indexOf("2")!=-1)
        city = '';
    if (boycott.indexOf("3")!=-1)
        state = '';
    if (boycott.indexOf("4")!=-1)
        country = '';
    if (boycott.indexOf("5")!=-1)
        postal = '';
    
    $("#answer"+name).val(lat + ';' + lng + ';' + city + ';' + state + ';' + country + ';' + postal);
    
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

function cellAdapter(evt,src)
{
	var eChild = null, eChildren = src.getElementsByTagName('INPUT');
	var curCount = eChildren.length;
	//This cell contains multiple controls, don't know which to set.
	if (eChildren.length > 1)
	{
		//Some cells contain hidden fields
		for (i = 0; i < eChildren.length; i++)
		{
			if ( ( eChildren[i].type == 'radio' || eChildren[i].type == 'checkbox' ) && eChild == null)
				eChild = eChildren[i];
			else if ( ( eChildren[i].type == 'radio' || eChildren[i].type == 'checkbox' ) && eChild != null)
			{
				//A cell with multiple radio buttons -- unhandled
				return;
			}
            
		}
	}
	else eChild = eChildren[0];

	if (eChild && eChild.type == 'radio')
	{
		eChild.checked = true;
		//Make sure the change propagates to the conditions handling mechanism
		if(eChild.onclick) eChild.onclick(evt);
		if(eChild.onchange) eChild.onchange(evt);
	}
	else if (eChild && eChild.type == 'checkbox')
	{
		eChild.checked = !eChild.checked;
		//Make sure the change propagates to the conditions handling mechanism
		if(eChild.onclick) eChild.onclick(evt);
		if(eChild.onchange) eChild.onchange(evt);
	}
}

function prepareCellAdapters()
	{
	if (!DOM1) return;
	var formCtls = document.getElementsByTagName('INPUT');
	var ptr = null;
	var foundTD = false;
	for (var i = 0; i < formCtls.length; i++)
	{
		ptr = formCtls[i];
		if (ptr.type == 'radio' || ptr.type == 'checkbox')
{
			foundTD = false;
			while (ptr && !foundTD)
	{
				if(ptr.nodeName == 'TD')
		{
					foundTD = true;
					ptr.onclick = 
						function(evt){
							return cellAdapter(evt,this);
						};
					continue;
				}
				ptr = ptr.parentNode;	
			}	
		}
	}
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

function disable_navigator()
{
	$('#navigator input').attr('disabled', 'disabled');
}

function navigator_countdown_btn()
{
	return $('#movenextbtn, #moveprevbtn, #movesubmitbtn');
}

function navigator_countdown_end()
{
	navigator_countdown_btn().each(function(i, e)
	{
		e.value = $(e).data('text');
		$(e).attr('disabled', '');
	});
	$(window).data('countdown', null);
}

function navigator_countdown_int()
{
	var n = $(window).data('countdown');
	if(n)
	{
		navigator_countdown_btn().each(function(i, e)
		{
			e.value = $(e).data('text');

                        // just count-down for delays longer than 1 second
                        if(n > 1) e.value += " (" + n + ")";
		});

		$(window).data('countdown', --n);
	}
	window.setTimeout((n > 0? navigator_countdown_int: navigator_countdown_end), 1000);
}

function navigator_countdown(n)
{
	$(document).ready(function()
	{
		$(window).data('countdown', n);

		navigator_countdown_btn().each(function(i, e)
		{
			$(e).data('text', e.value);
		});

		navigator_countdown_int();
	});
}

// ==========================================================
// totals

function multi_set(ids)
{
	//quick ie check
	var ie=(navigator.userAgent.indexOf("MSIE")>=0)?true:false;
	//match for grand
	var _match_grand = new RegExp('grand');
	//match for total
	var _match_total = new RegExp('total');
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
								_tdin.onkeydown = _in_key;
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
				_bits[id][i].onChange = calc;
				if(i == (l - 1))
				{
					_bits[id][i].value = qt;
				}
				else if(_bits[id][i].value)
				{
					qt += (_bits[id][i].value * 1);
//				}
//				else
//				{
//					_bits[id][i].value = '0';
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
				_bits[i][id].onchange = calc;
				if(i == (l - 1))
				{
					_bits[i][id].value = qt;
				}
				else if(_bits[i][id].value)
				{
					qt += (_bits[i][id].value * 1);
//				}
//				else
//				{
//					_bits[i][id].value = '0';
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
                        _bits[i][vid].value = qt;
                    }
				}
				else if(_bits[i][vid].value)
				{
					if(_bits[i][vid].value.match('-','ig'))
					{
						var _iklebit = _bits[i][vid].value.replace('-','','ig');
						//alert(iklebit);
						if(_iklebit)
						{
							qt -=(_iklebit * 1);
						}
					}
					else
					{
						qt += (_bits[i][vid].value * 1);
					}
					
//				}
//				else
//				{
//					_bits[i][vid].value = '0';
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
                        _bits[hid][i].value = qt;
                    }
				}
				else if(_bits[hid][i].value)
				{
					if(_bits[hid][i].value.match('-','ig'))
					{
						var _iklebit = _bits[hid][i].value.replace('-','','ig');
						//alert(_iklebit);
						if(_iklebit)
						{
							qt -= (_iklebit * 1);
						}
					}
					else
					{
						qt += (_bits[hid][i].value * 1);
					}
//				}
//				else
//				{
//					_bits[hid][i].value = '0';
				};	
			};
		};
		//clear key input
		function dummy(e)
		{
			return(false);
		};
		//limit to numbers and .
		function _in_key(e)
		{
			e = e || window.event;
			//alert(e.keyCode);
			switch(e.keyCode)
			{
				case 8:
				case 9:
				case 48:
				case 49:
				case 50:
				case 51:
				case 52:
				case 53:
				case 54:
				case 55:
				case 56:
				case 57:
				case 190:
				case 45:
				case 35:
				case 40:
				case 34:
				case 37:
				case 12:
				case 39:
				case 36:
				case 38:
				case 33:
				case 46:
				case 96:
				case 97:
				case 98:
				case 99:
				case 100:
				case 101:
				case 102:
				case 103:
				case 104:
				case 105:
				case 110:
				case 109:
                case 189:
					return(e.keyCode);
				default:
				//alert(e.keyCode);
					return(false);
					break;
			}
		}
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
function array_dual_dd_checkconditions(value, name, type, rank, condfunction)
{
   if (value == '') {
        //If value is set to empty, reset both drop downs and check conditions
        if (rank == 0) { dualname = name.replace(/#0/g,"#1"); }
        else if (rank == 1) { dualname = name.replace(/#1/g,"#0"); }
        document.getElementsByName(dualname)[0].value=value;
        condfunction(value, dualname, type);
   }
    condfunction(value, name, type);
}
