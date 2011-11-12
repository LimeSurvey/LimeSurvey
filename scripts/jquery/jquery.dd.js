// MSDropDown - jquery.dd.js
// author: Marghoob Suleman - Search me on google
// Date: 12th Aug, 2009
// Version: 2.35 {date: 28 Nov, 2010}
// Revision: 30
// web: www.giftlelo.com | www.marghoobsuleman.com
/*
// msDropDown is free jQuery Plugin: you can redistribute it and/or modify
// it under the terms of the either the MIT License or the Gnu General Public License (GPL) Version 2
*/
(function($) {
   var msOldDiv = ""; 
   var dd = function(element, options)
   {
		var sElement = element;
		var $this =  this; //parent this
		var options = $.extend({
			height:120,
			visibleRows:7,
			rowHeight:23,
			showIcon:true,
			zIndex:9999,
			mainCSS:'dd',
			useSprite:false,
			animStyle:'slideDown',
			onInit:'',
			style:''
		}, options);
		this.ddProp = new Object();//storing propeties;
		var oldSelectedValue = "";
		var actionSettings ={};
		actionSettings.insideWindow = true;
		actionSettings.keyboardAction = false;
		actionSettings.currentKey = null;
		var ddList = false;
		var config = {postElementHolder:'_msddHolder', postID:'_msdd', postTitleID:'_title',postTitleTextID:'_titletext',postChildID:'_child',postAID:'_msa',postOPTAID:'_msopta',postInputID:'_msinput', postArrowID:'_arrow', postInputhidden:'_inp'};
		var styles = {dd:options.mainCSS, ddTitle:'ddTitle', arrow:'arrow', ddChild:'ddChild', ddTitleText:'ddTitleText', disabled:.30, ddOutOfVision:'ddOutOfVision', borderTop:'borderTop', noBorderTop:'noBorderTop'};
		var attributes = {actions:"focus,blur,change,click,dblclick,mousedown,mouseup,mouseover,mousemove,mouseout,keypress,keydown,keyup", prop:"size,multiple,disabled,tabindex"};
		this.onActions = new Object();
		var elementid = $(sElement).attr("id");
		var inlineCSS = $(sElement).attr("style");
		options.style += (inlineCSS==undefined) ? "" : inlineCSS;
		var allOptions = $(sElement).children();
		ddList = ($(sElement).attr("size")>1 || $(sElement).attr("multiple")==true) ? true : false;
		if(ddList) {options.visibleRows = $(sElement).attr("size");};
		var a_array = {};//stores id, html & value etc
		
	var getPostID = function (id) {
		return elementid+config[id];
	};
	var getOptionsProperties = function (option) {
		var currentOption = option;
		var styles = $(currentOption).attr("style");
		return styles;
	};
	var matchIndex = function (index) {
		var selectedIndex = $("#"+elementid+" option:selected");
		if(selectedIndex.length>1) {
			for(var i=0;i<selectedIndex.length;i++) {
				if(index == selectedIndex[i].index) {
					return true;
				};
			};
		} else if(selectedIndex.length==1) {
			if(selectedIndex[0].index==index) {
				return true;
			};
		};
		return false;
	};
	var createA = function(currentOptOption, current, currentopt, tp) {
		var aTag = "";
		//var aidfix = getPostID("postAID");
		var aidoptfix = (tp=="opt") ? getPostID("postOPTAID") : getPostID("postAID");		
		var aid = (tp=="opt") ? aidoptfix+"_"+(current)+"_"+(currentopt) : aidoptfix+"_"+(current);
		var arrow = "";
		var clsName = "";
		if(options.useSprite!=false) {
		 clsName = ' '+options.useSprite+' '+currentOptOption.className;
		} else {
		 arrow = $(currentOptOption).attr("title");
		 arrow = (arrow.length==0) ? "" : '<img src="'+arrow+'" align="absmiddle" /> ';																 
		};
		//console.debug("clsName "+clsName);
		var sText = $(currentOptOption).text();
		var sValue = $(currentOptOption).val();
		var sEnabledClass = ($(currentOptOption).attr("disabled")==true) ? "disabled" : "enabled";
		a_array[aid] = {html:arrow + sText, value:sValue, text:sText, index:currentOptOption.index, id:aid};
		var innerStyle = getOptionsProperties(currentOptOption);
		if(matchIndex(currentOptOption.index)==true) {
		 aTag += '<a href="javascript:void(0);" class="selected '+sEnabledClass+clsName+'"';
		} else {
		aTag += '<a  href="javascript:void(0);" class="'+sEnabledClass+clsName+'"';
		};
		if(innerStyle!==false && innerStyle!==undefined) {
		aTag +=  " style='"+innerStyle+"'";
		};
		aTag +=  ' id="'+aid+'">';
		aTag += arrow + '<span class="'+styles.ddTitleText+'">' +sText+'</span></a>';
		return aTag;
	};
	var createATags = function () {
		var childnodes = allOptions;
		if(childnodes.length==0) return "";
		var aTag = "";
		var aidfix = getPostID("postAID");
		var aidoptfix = getPostID("postOPTAID");
		childnodes.each(function(current){
								 var currentOption = childnodes[current];
								 //OPTGROUP
								 if(currentOption.nodeName == "OPTGROUP") {
								  	aTag += "<div class='opta'>";
									 aTag += "<span style='font-weight:bold;font-style:italic; clear:both;'>"+$(currentOption).attr("label")+"</span>";
									 var optChild = $(currentOption).children();
									 optChild.each(function(currentopt){
															var currentOptOption = optChild[currentopt];
															 aTag += createA(currentOptOption, current, currentopt, "opt");
															});
									 aTag += "</div>";
									 
								 } else {
									 aTag += createA(currentOption, current, "", "");
								 };
								 });
		return aTag;
	};
	var createChildDiv = function () {
		var id = getPostID("postID");
		var childid = getPostID("postChildID");
		var sStyle = options.style;
		sDiv = "";
		sDiv += '<div id="'+childid+'" class="'+styles.ddChild+'"';
		if(!ddList) {
			sDiv += (sStyle!="") ? ' style="'+sStyle+'"' : ''; 
		} else {
			sDiv += (sStyle!="") ? ' style="border-top:1px solid #c3c3c3;display:block;position:relative;'+sStyle+'"' : ''; 
		};
		sDiv += '>';		
		return sDiv;
	};

	var createTitleDiv = function () {
		var titleid = getPostID("postTitleID");
		var arrowid = getPostID("postArrowID");
		var titletextid = getPostID("postTitleTextID");
		var inputhidden = getPostID("postInputhidden");
		var sText = "";
		var arrow = "";
		if(document.getElementById(elementid).options.length>0) {
			sText = $("#"+elementid+" option:selected").text();
			arrow = $("#"+elementid+" option:selected").attr("title");
		};
		//console.debug("sObj	 "+sObj.length);
		arrow = (arrow.length==0 || arrow==undefined || options.showIcon==false || options.useSprite!=false) ? "" : '<img src="'+arrow+'" align="absmiddle" /> ';
		var sDiv = '<div id="'+titleid+'" class="'+styles.ddTitle+'"';
		sDiv += '>';
		sDiv += '<span id="'+arrowid+'" class="'+styles.arrow+'"></span><span class="'+styles.ddTitleText+'" id="'+titletextid+'">'+arrow + '<span class="'+styles.ddTitleText+'">'+sText+'</span></span></div>';
		return sDiv;
	};
	var applyEventsOnA = function() {
		var childid = getPostID("postChildID");
		$("#"+childid+ " a.enabled").unbind("click"); //remove old one
			$("#"+childid+ " a.enabled").bind("click", function(event) {
														 event.preventDefault();
														 manageSelection(this);
														 if(!ddList) {
															 $("#"+childid).unbind("mouseover");
															 setInsideWindow(false);															 
															 var sText = (options.showIcon==false) ? $(this).text() : $(this).html();
															 //alert("sText "+sText);
															  setTitleText(sText);
															  //$this.data("dd").close();
															  $this.close();
														 };
														 setValue();
														 //actionSettings.oldIndex = a_array[$($this).attr("id")].index;
														 });		
	};
	var createDropDown = function () {
		var changeInsertionPoint = false;
		var id = getPostID("postID");
		var titleid = getPostID("postTitleID");
		var titletextid = getPostID("postTitleTextID");
		var childid = getPostID("postChildID");
		var arrowid = getPostID("postArrowID");
		var iWidth = $("#"+elementid).width();
		iWidth = iWidth+2;//it always give -2 width; i dont know why
		var sStyle = options.style;
		if($("#"+id).length>0) {
			$("#"+id).remove();
			changeInsertionPoint = true;
		};
		var sDiv = '<div id="'+id+'" class="'+styles.dd+'"';
		sDiv += (sStyle!="") ? ' style="'+sStyle+'"' : '';
		sDiv += '>';
		//create title bar
		sDiv += createTitleDiv();
		//create child
		sDiv += createChildDiv();
		sDiv += createATags();
		sDiv += "</div>";
		sDiv += "</div>";
		if(changeInsertionPoint==true) {
			var sid =getPostID("postElementHolder");
			$("#"+sid).after(sDiv);
		} else {
			$("#"+elementid).after(sDiv);
		};
		if(ddList) {
			var titleid = getPostID("postTitleID");	
			$("#"+titleid).hide();
		};
		
		$("#"+id).css("width", iWidth+"px");
		$("#"+childid).css("width", (iWidth-2)+"px");
		if(allOptions.length>options.visibleRows) {
			var margin = parseInt($("#"+childid+" a:first").css("padding-bottom")) + parseInt($("#"+childid+" a:first").css("padding-top"));
			var iHeight = ((options.rowHeight)*options.visibleRows) - margin;
			$("#"+childid).css("height", iHeight+"px");
		} else if(ddList) {
			var iHeight = $("#"+elementid).height();
			$("#"+childid).css("height", iHeight+"px");
		};
		//set out of vision
		if(changeInsertionPoint==false) {
			setOutOfVision();
			addRefreshMethods(elementid);
		};
		if($("#"+elementid).attr("disabled")==true) {
			$("#"+id).css("opacity", styles.disabled);
		};
		applyEvents();
		//add events
		//arrow hightlight
		$("#"+titleid).bind("mouseover", function(event) {
												  hightlightArrow(1);
												  });
		$("#"+titleid).bind("mouseout", function(event) {
												  hightlightArrow(0);
												  });
			//open close events
		applyEventsOnA();
		$("#"+childid+ " a.disabled").css("opacity", styles.disabled);
		//alert("ddList "+ddList)
		if(ddList) {
			$("#"+childid).bind("mouseover", function(event) {if(!actionSettings.keyboardAction) {
																 actionSettings.keyboardAction = true;
																 $(document).bind("keydown", function(event) {
																									var keyCode = event.keyCode;	
																									actionSettings.currentKey = keyCode;
																									if(keyCode==39 || keyCode==40) {
																										//move to next
																										event.preventDefault(); event.stopPropagation();
																										next();
																										setValue();
																									};
																									if(keyCode==37 || keyCode==38) {
																										event.preventDefault(); event.stopPropagation();
																										//move to previous
																										previous();
																										setValue();
																									};
																									  });
																 
																 }});
		};
		$("#"+childid).bind("mouseout", function(event) {setInsideWindow(false);$(document).unbind("keydown");actionSettings.keyboardAction = false;actionSettings.currentKey=null;});
		$("#"+titleid).bind("click", function(event) {
											  setInsideWindow(false);
												if($("#"+childid+":visible").length==1) {
													$("#"+childid).unbind("mouseover");
												} else {
													$("#"+childid).bind("mouseover", function(event) {setInsideWindow(true);});
													//alert("open "+elementid + $this);
													//$this.data("dd").openMe();
													$this.open();
												};
											  });
		$("#"+titleid).bind("mouseout", function(evt) {
												 setInsideWindow(false);
												 });
		if(options.showIcon && options.useSprite!=false) {
			setTitleImageSprite();
		};
	};
	var getByIndex = function (index) {
		for(var i in a_array) {
			if(a_array[i].index==index) {
				return a_array[i];
			};
		};
		return -1;
	};
	var manageSelection = function (obj) {
		var childid = getPostID("postChildID");
		if($("#"+childid+ " a.selected").length==1) { //check if there is any selected
			oldSelectedValue = $("#"+childid+ " a.selected").text(); //i should have value here. but sometime value is missing
			//alert("oldSelectedValue "+oldSelectedValue);
		};
		if(!ddList) {
			$("#"+childid+ " a.selected").removeClass("selected");
		}; 
		var selectedA = $("#"+childid + " a.selected").attr("id");
		if(selectedA!=undefined) {
			var oldIndex = (actionSettings.oldIndex==undefined || actionSettings.oldIndex==null) ? a_array[selectedA].index : actionSettings.oldIndex;
		};
		if(obj && !ddList) {
			$(obj).addClass("selected");
		};	
		if(ddList) {
			var keyCode = actionSettings.currentKey;
			if($("#"+elementid).attr("multiple")==true) {
				if(keyCode == 17) {
					//control
						actionSettings.oldIndex = a_array[$(obj).attr("id")].index;
						$(obj).toggleClass("selected");
					//multiple
				} else if(keyCode==16) {
					$("#"+childid+ " a.selected").removeClass("selected");
					$(obj).addClass("selected");
					//shift
					var currentSelected = $(obj).attr("id");
					var currentIndex = a_array[currentSelected].index;
					for(var i=Math.min(oldIndex, currentIndex);i<=Math.max(oldIndex, currentIndex);i++) {
						$("#"+getByIndex(i).id).addClass("selected");
					};
				} else {
					$("#"+childid+ " a.selected").removeClass("selected");
					$(obj).addClass("selected");
					actionSettings.oldIndex = a_array[$(obj).attr("id")].index;
				};
			} else {
					$("#"+childid+ " a.selected").removeClass("selected");
					$(obj).addClass("selected");
					actionSettings.oldIndex = a_array[$(obj).attr("id")].index;				
			};
			//isSingle
		};		
	};
	var addRefreshMethods = function (id) {
		//deprecated
		var objid = id;
		document.getElementById(objid).refresh = function(e) {
			 $("#"+objid).msDropDown(options);
		};
	};
	var setInsideWindow = function (val) {
		actionSettings.insideWindow = val;
	};
	var getInsideWindow = function () {
		return actionSettings.insideWindow;
	};
	var applyEvents = function () {
		var mainid = getPostID("postID");
		var actions_array = attributes.actions.split(",");
		for(var iCount=0;iCount<actions_array.length;iCount++) {
			var action = actions_array[iCount];
			//var actionFound = $("#"+elementid).attr(action);
			var actionFound = has_handler(action);//$("#"+elementid).attr(action);
			//console.debug(elementid +" action " + action , "actionFound "+actionFound);
			if(actionFound==true) {
				switch(action) {
					case "focus": 
					$("#"+mainid).bind("mouseenter", function(event) {
													   document.getElementById(elementid).focus();
													   //$("#"+elementid).focus();
													   });
					break;
					case "click": 
					$("#"+mainid).bind("click", function(event) {
													   //document.getElementById(elementid).onclick();
													   $("#"+elementid).trigger("click");
													   });
					break;
					case "dblclick": 
					$("#"+mainid).bind("dblclick", function(event) {
													   //document.getElementById(elementid).ondblclick();
													   $("#"+elementid).trigger("dblclick");
													   });
					break;
					case "mousedown": 
					$("#"+mainid).bind("mousedown", function(event) {
													   //document.getElementById(elementid).onmousedown();
													   $("#"+elementid).trigger("mousedown");
													   });
					break;
					case "mouseup": 
					//has in close mthod
					$("#"+mainid).bind("mouseup", function(event) {
													   //document.getElementById(elementid).onmouseup();
													   $("#"+elementid).trigger("mouseup");
													   //setValue();
													   });
					break;
					case "mouseover": 
					$("#"+mainid).bind("mouseover", function(event) {
													   //document.getElementById(elementid).onmouseover();													   
													   $("#"+elementid).trigger("mouseover");
													   });
					break;
					case "mousemove": 
					$("#"+mainid).bind("mousemove", function(event) {
													   //document.getElementById(elementid).onmousemove();
													   $("#"+elementid).trigger("mousemove");
													   });
					break;
					case "mouseout": 
					$("#"+mainid).bind("mouseout", function(event) {
													   //document.getElementById(elementid).onmouseout();
													   $("#"+elementid).trigger("mouseout");
													   });
					break;					
				};
			};
		};
		
	};
	var setOutOfVision = function () {
		var sId = getPostID("postElementHolder");
		$("#"+elementid).after("<div class='"+styles.ddOutOfVision+"' style='height:0px;overflow:hidden;position:absolute;' id='"+sId+"'></div>");
		$("#"+elementid).appendTo($("#"+sId));
	};
	var setTitleText = function (sText) {
		var titletextid = getPostID("postTitleTextID");
		$("#"+titletextid).html(sText);		
	};
	var next = function () {
		var titletextid = getPostID("postTitleTextID");
		var childid = getPostID("postChildID");
		var allAs = $("#"+childid + " a.enabled");
		for(var current=0;current<allAs.length;current++) {
			var currentA = allAs[current];
			var id = $(currentA).attr("id");
			if($(currentA).hasClass("selected") && current<allAs.length-1) {
				$("#"+childid + " a.selected").removeClass("selected");
				$(allAs[current+1]).addClass("selected");
				//manageSelection(allAs[current+1]);
				var selectedA = $("#"+childid + " a.selected").attr("id");
				if(!ddList) {
					var sText = (options.showIcon==false) ? a_array[selectedA].text : a_array[selectedA].html;
					setTitleText(sText);
				};
				if(parseInt(($("#"+selectedA).position().top+$("#"+selectedA).height()))>=parseInt($("#"+childid).height())) {
					$("#"+childid).scrollTop(($("#"+childid).scrollTop())+$("#"+selectedA).height()+$("#"+selectedA).height());
				};
				break;
			};
		};
	};
	var previous = function () {
		var titletextid = getPostID("postTitleTextID");
		var childid = getPostID("postChildID");
		var allAs = $("#"+childid + " a.enabled");
		for(var current=0;current<allAs.length;current++) {
			var currentA = allAs[current];
			var id = $(currentA).attr("id");
			if($(currentA).hasClass("selected") && current!=0) {
				$("#"+childid + " a.selected").removeClass("selected");
				$(allAs[current-1]).addClass("selected");				
				//manageSelection(allAs[current-1]);
				var selectedA = $("#"+childid + " a.selected").attr("id");
				if(!ddList) {
					var sText = (options.showIcon==false) ? a_array[selectedA].text : a_array[selectedA].html;
					setTitleText(sText);
				};
				if(parseInt(($("#"+selectedA).position().top+$("#"+selectedA).height())) <=0) {
					$("#"+childid).scrollTop(($("#"+childid).scrollTop()-$("#"+childid).height())-$("#"+selectedA).height());
				};
				break;
			};
		};
	};
	var setTitleImageSprite = function() {
		if(options.useSprite!=false) {
			var titletextid = getPostID("postTitleTextID");
			var sClassName = document.getElementById(elementid).options[document.getElementById(elementid).selectedIndex].className;
			if(sClassName.length>0) {
				var childid = getPostID("postChildID");
				var id = $("#"+childid + " a."+sClassName).attr("id");
				var backgroundImg = $("#"+id).css("background-image");
				var backgroundPosition = $("#"+id).css("background-position");
				var paddingLeft = $("#"+id).css("padding-left");
				if(backgroundImg!=undefined) {
					$("#"+titletextid).find("."+styles.ddTitleText).attr('style', "background:"+backgroundImg);
				};
				if(backgroundPosition!=undefined) {
					$("#"+titletextid).find("."+styles.ddTitleText).css('background-position', backgroundPosition);
				};
				if(paddingLeft!=undefined) {
					$("#"+titletextid).find("."+styles.ddTitleText).css('padding-left', paddingLeft);	
				};
				$("#"+titletextid).find("."+styles.ddTitleText).css('background-repeat', 'no-repeat');				
				$("#"+titletextid).find("."+styles.ddTitleText).css('padding-bottom', '2px');
			};
		};		
	};
	var setValue = function () {
		//alert("setValue "+elementid);
		var childid = getPostID("postChildID");
		var allSelected = $("#"+childid + " a.selected");
		if(allSelected.length==1) {
			var sText = $("#"+childid + " a.selected").text();
			var selectedA = $("#"+childid + " a.selected").attr("id");
			if(selectedA!=undefined) {
				var sValue = a_array[selectedA].value;
				document.getElementById(elementid).selectedIndex = a_array[selectedA].index;
			};
			//set image on title if using sprite
			if(options.showIcon && options.useSprite!=false)
				setTitleImageSprite();
		} else if(allSelected.length>1) { 
			var alls = $("#"+elementid +" > option:selected").removeAttr("selected");
			for(var i=0;i<allSelected.length;i++) {
				var selectedA = $(allSelected[i]).attr("id");
				var index = a_array[selectedA].index;
				document.getElementById(elementid).options[index].selected = "selected";
			};
		};
		//alert(document.getElementById(elementid).selectedIndex);
		var sIndex = document.getElementById(elementid).selectedIndex;
		$this.ddProp["selectedIndex"]= sIndex;
		//alert("selectedIndex "+ $this.ddProp["selectedIndex"] + " sIndex "+sIndex);
	};
	var has_handler = function (name) {
		// True if a handler has been added in the html.
		if ($("#"+elementid).attr("on" + name) != undefined) {
			return true;
		};
		// True if a handler has been added using jQuery.
		var evs = $("#"+elementid).data("events");
		if (evs && evs[name]) {
			return true;
		};
		return false;
	};
	var checkMethodAndApply = function () {
		var childid = getPostID("postChildID");
		if(has_handler('change')==true) {
			//alert(1);
			var currentSelectedValue = a_array[$("#"+childid +" a.selected").attr("id")].text;
			//alert("oldSelectedValue "+oldSelectedValue + " currentSelectedValue "+currentSelectedValue);
			if($.trim(oldSelectedValue) !== $.trim(currentSelectedValue)){
				$("#"+elementid).trigger("change");
			};
		};
		if(has_handler('mouseup')==true) {
			$("#"+elementid).trigger("mouseup");
		};
		if(has_handler('blur')==true) { 
			$(document).bind("mouseup", function(evt) {
												   $("#"+elementid).focus();
												   $("#"+elementid)[0].blur();
												   setValue();
												   $(document).unbind("mouseup");
												});
		};
	};
	var hightlightArrow = function(ison) {
		var arrowid = getPostID("postArrowID");
		if(ison==1)
			$("#"+arrowid).css({backgroundPosition:'0 100%'});
		else 
			$("#"+arrowid).css({backgroundPosition:'0 0'});
	};
	var setOriginalProperties = function() {
		//properties = {};
		//alert($this.data("dd"));
		for(var i in document.getElementById(elementid)) {
			if(typeof(document.getElementById(elementid)[i])!='function' && document.getElementById(elementid)[i]!==undefined && document.getElementById(elementid)[i]!==null) {
				$this.set(i, document.getElementById(elementid)[i], true);//true = setting local properties
			};
		};
	};
	var setValueByIndex = function(prop, val) {
			if(getByIndex(val) != -1) {
				document.getElementById(elementid)[prop] = val;
				var childid = getPostID("postChildID");
				$("#"+childid+ " a.selected").removeClass("selected");
				$("#"+getByIndex(val).id).addClass("selected");
				var sText = getByIndex(document.getElementById(elementid).selectedIndex).html;
				setTitleText(sText);				
			};
	};
	var addRemoveFromIndex = function(i, action) {
		if(action=='d') {
			for(var key in a_array) {
				if(a_array[key].index == i) {
					delete a_array[key];
					break;
				};
			};
		};
		//update index
		var count = 0;
		for(var key in a_array) {
			a_array[key].index = count;
			count++;
		};
	};
	var shouldOpenOpposite = function() {
		var childid = getPostID("postChildID");
		var main = getPostID("postID");
		var pos = $("#"+main).position();
		var mH = $("#"+main).height();
		var wH = $(window).height();
		var st = $(window).scrollTop();
		var cH = $("#"+childid).height();
		var css = {zIndex:options.zIndex, top:(pos.top+mH)+"px", display:"none"};
		var ani = options.animStyle;
		var opp = false;
		var borderTop = styles.noBorderTop;
		$("#"+childid).removeClass(styles.noBorderTop);
		$("#"+childid).removeClass(styles.borderTop);
		if( (wH+st) < Math.floor(cH+mH+pos.top) ) {
			var tp = pos.top-cH;
			if((pos.top-cH)<0) {
				tp = 10;
			};
			css = {zIndex:options.zIndex, top:tp+"px", display:"none"};
			ani = "show";
			opp = true;
			borderTop = styles.borderTop;
		};
		return {opp:opp, ani:ani, css:css, border:borderTop};
	};	
	/************* public methods *********************/
	this.open = function() {
		if(($this.get("disabled", true) == true) || ($this.get("options", true).length==0)) return;
		var childid = getPostID("postChildID");
		if(msOldDiv!="" && childid!=msOldDiv) { 
			$("#"+msOldDiv).slideUp("fast");
			$("#"+msOldDiv).css({zIndex:'0'});
		};
		if($("#"+childid).css("display")=="none") {
			//oldSelectedValue = a_array[$("#"+childid +" a.selected").attr("id")].text;
			//keyboard action
			$(document).bind("keydown", function(event) {
													var keyCode = event.keyCode;											
													if(keyCode==39 || keyCode==40) {
														//move to next
														event.preventDefault(); event.stopPropagation();
														next();
													};
													if(keyCode==37 || keyCode==38) {
														event.preventDefault(); event.stopPropagation();
														//move to previous
														previous();
													};
													if(keyCode==27 || keyCode==13) {
														//$this.data("dd").close();
														$this.close();
														setValue();
													};
													if($("#"+elementid).attr("onkeydown")!=undefined) {
															document.getElementById(elementid).onkeydown();
														};														
													   });
					
			$(document).bind("keyup", function(event) {
				if($("#"+elementid).attr("onkeyup")!=undefined) {
				//$("#"+elementid).keyup();
				document.getElementById(elementid).onkeyup();
				};												 
			});
			//end keyboard action
			
			//close onmouseup
			$(document).bind("mouseup", function(evt){
													if(getInsideWindow()==false) {
													//alert("evt.target: "+evt.target);
													 //$this.data("dd").close();
													 $this.close();
													};
												 });													  
			
			//check open
			var wf = shouldOpenOpposite();
			$("#"+childid).css(wf.css);
			if(wf.opp==true) {
				$("#"+childid).css({display:'block'});
				$("#"+childid).addClass(wf.border);
				  if($this.onActions["onOpen"]!=null) {
					  eval($this.onActions["onOpen"])($this);
				  };				
			} else {
				$("#"+childid)[wf.ani]("fast", function() {
														  $("#"+childid).addClass(wf.border);
														  if($this.onActions["onOpen"]!=null) {
															  eval($this.onActions["onOpen"])($this);
														  };
														  });
			};
			if(childid != msOldDiv) {
				msOldDiv = childid;
			};
		};
	};
	this.close = function() {
				var childid = getPostID("postChildID");
				$(document).unbind("keydown");
				$(document).unbind("keyup");
				$(document).unbind("mouseup");
				var wf = shouldOpenOpposite();
				if(wf.opp==true) {
					$("#"+childid).css("display", "none");
				};
				$("#"+childid).slideUp("fast", function(event) {
															checkMethodAndApply();
															$("#"+childid).css({zIndex:'0'});
															if($this.onActions["onClose"]!=null) {
														  		eval($this.onActions["onClose"])($this);
													  		};
															});
		
	};
	this.selectedIndex = function(i) {
		$this.set("selectedIndex", i);
	};
	//update properties
	this.set = function(prop, val, isLocal) {
		//alert("- set " + prop + " : "+val);
		if(prop==undefined || val==undefined) throw {message:"set to what?"}; 
		$this.ddProp[prop] = val;
		if(isLocal!=true) { 
			switch(prop) {
				case "selectedIndex":
					setValueByIndex(prop, val);
				break;
				case "disabled":
					$this.disabled(val, true);
				break;
				case "multiple":
					document.getElementById(elementid)[prop] = val;
					ddList = ($(sElement).attr("size")>0 || $(sElement).attr("multiple")==true) ? true : false;	
					if(ddList) {
						//do something
						var iHeight = $("#"+elementid).height();
						var childid = getPostID("postChildID");
						$("#"+childid).css("height", iHeight+"px");					
						//hide titlebar
						var titleid = getPostID("postTitleID");
						$("#"+titleid).hide();
						var childid = getPostID("postChildID");
						$("#"+childid).css({display:'block',position:'relative'});
						applyEventsOnA();
					};
				break;
				case "size":
					document.getElementById(elementid)[prop] = val;
					if(val==0) {
						document.getElementById(elementid).multiple = false;
					};
					ddList = ($(sElement).attr("size")>0 || $(sElement).attr("multiple")==true) ? true : false;	
					if(val==0) {
						//show titlebar
						var titleid = getPostID("postTitleID");
						$("#"+titleid).show();
						var childid = getPostID("postChildID");
						$("#"+childid).css({display:'none',position:'absolute'});
						var sText = "";
						if(document.getElementById(elementid).selectedIndex>=0) {
							var aObj = getByIndex(document.getElementById(elementid).selectedIndex);
							sText = aObj.html;
							manageSelection($("#"+aObj.id));
						}; 
						setTitleText(sText);
					} else {
						//hide titlebar
						var titleid = getPostID("postTitleID");
						$("#"+titleid).hide();
						var childid = getPostID("postChildID");
						$("#"+childid).css({display:'block',position:'relative'});						
					};
				break;
				default:
				try{
					//check if this is not a readonly properties
					document.getElementById(elementid)[prop] = val;
				} catch(e) {
					//silent
				};				
				break;
			};
		};
		//alert("get " + prop + " : "+$this.ddProp[prop]);
		//$this.set("selectedIndex", 0);
	};
	this.get = function(prop, forceRefresh) {
		if(prop==undefined && forceRefresh==undefined) {
			//alert("c1 : " +$this.ddProp);
		 	return $this.ddProp;
		};
		if(prop!=undefined && forceRefresh==undefined) {
			//alert("c2 : " +$this.ddProp[prop]);
			return ($this.ddProp[prop]!=undefined) ? $this.ddProp[prop] : null;
		};
		if(prop!=undefined && forceRefresh!=undefined) {
			//alert("c3 : " +document.getElementById(elementid)[prop]);
			return document.getElementById(elementid)[prop];
		};
	};
	this.visible = function(val) {
		var id = getPostID("postID");
		if(val==true) {
			$("#"+id).show();
		} else if(val==false) {
			$("#"+id).hide();
		} else {
			return $("#"+id).css("display");
		};
	};
	this.add = function(opt, index) {
		var objOpt = opt;
		var sText = objOpt.text;
		var sValue = (objOpt.value==undefined || objOpt.value==null) ? sText : objOpt.value;
		var img = (objOpt.title==undefined || objOpt.title==null) ? '' : objOpt.title;
		var i = (index==undefined || index==null) ? document.getElementById(elementid).options.length : index;
		document.getElementById(elementid).options[i] = new Option(sText, sValue);
		if(img!='') document.getElementById(elementid).options[i].title = img;
		//check if exist
		var ifA = getByIndex(i);
		if(ifA != -1) {
			//replace
			var aTag = createA(document.getElementById(elementid).options[i], i, "", "");
			$("#"+ifA.id).html(aTag);
			//a_array[key]
		} else {
			var aTag = createA(document.getElementById(elementid).options[i], i, "", "");
			//add
			var childid = getPostID("postChildID");
			$("#"+childid).append(aTag);
			applyEventsOnA();
		};
	};	
	this.remove = function(i) {
		document.getElementById(elementid).remove(i);
		if((getByIndex(i))!= -1) { $("#"+getByIndex(i).id).remove();addRemoveFromIndex(i, 'd');};
		//alert("a" +a);
		if(document.getElementById(elementid).length==0) {
			setTitleText("");
		} else {
			var sText = getByIndex(document.getElementById(elementid).selectedIndex).html;
			setTitleText(sText);
		};
		$this.set("selectedIndex", document.getElementById(elementid).selectedIndex);
	};
	this.disabled = function(dis, isLocal) {
		document.getElementById(elementid).disabled = dis;
		//alert(document.getElementById(elementid).disabled);
		var id = getPostID("postID");
		if(dis==true) {
			$("#"+id).css("opacity", styles.disabled);
			$this.close();
		} else if(dis==false) {
			$("#"+id).css("opacity", 1);
		};
		if(isLocal!=true) {
			$this.set("disabled", dis);
		};
	};
	//return form element
	this.form = function() {
		return (document.getElementById(elementid).form == undefined) ? null : document.getElementById(elementid).form;
	};
	this.item = function() {
		//index, subindex - use arguments.length
		if(arguments.length==1) {
			return document.getElementById(elementid).item(arguments[0]);
		} else if(arguments.length==2) {
			return document.getElementById(elementid).item(arguments[0], arguments[1]);
		} else {
			throw {message:"An index is required!"};
		};
	};
	this.namedItem = function(nm) {
		return document.getElementById(elementid).namedItem(nm);
	};
	this.multiple = function(is) {
		if(is==undefined) {
			return $this.get("multiple");
		} else {
			$this.set("multiple", is);
		};
		
	};
	this.size = function(sz) {
		if(sz==undefined) {
			return $this.get("size");
		} else {
			$this.set("size", sz);
		};		
	};	
	this.addMyEvent = function(nm, fn) {
		$this.onActions[nm] = fn;
	};
	this.fireEvent = function(nm) {
		eval($this.onActions[nm])($this);
	};
	//end 
	var updateCommonVars = function() {
		$this.set("version", $.msDropDown.version);
		$this.set("author", $.msDropDown.author);
	};
	var init = function() {
		//create wrapper
		createDropDown();
		//update propties
		//alert("init");
		setOriginalProperties();
		updateCommonVars();
		if(options.onInit!='') {
			eval(options.onInit)($this);
		};
		
	};
	init();
	};
	//static
	$.msDropDown = {
		version: 2.35,
		author: "Marghoob Suleman",
		create: function(id, opt) {
			return $(id).msDropDown(opt).data("dd");
		}
	};
	$.fn.extend({
	        msDropDown: function(options)
	        {
	            return this.each(function()
	            {
	               //if ($(this).data('dd')) return; // need to comment when using refresh method - will remove in next version
	               var mydropdown = new dd(this, options);
	               $(this).data('dd', mydropdown);
	            });
	        }
	    });
		   
})(jQuery);