// MSDropDown - jquery.dd.js
// author: Marghoob Suleman - http://www.marghoobsuleman.com/
// Date: 10 Nov, 2012
// Version: 3.3
// Revision: 22
// web: www.marghoobsuleman.com
/*
// msDropDown is free jQuery Plugin: you can redistribute it and/or modify
// it under the terms of the either the MIT License or the Gnu General Public License (GPL) Version 2
*/
var msBeautify = msBeautify || {};
(function ($) {
msBeautify = {
	version: {msDropdown:'3.3'},
	author: "Marghoob Suleman",
	counter: 20,
	debug: function (v) {
		if (v !== false) {
			$(".ddOutOfVision").css({height: 'auto', position: 'relative'});
		} else {
			$(".ddOutOfVision").css({height: '0px', position: 'absolute'});
		}
	},
	oldDiv: '',
	create: function (id, settings, type) {
		type = type || "dropdown";
		var data;
		switch (type.toLowerCase()) {
		case "dropdown":
		case "select":
			data = $(id).msDropdown(settings).data("dd");
			break;
		}
		return data;
	}
};

$.msDropDown = {};
$.msDropdown = {};
$.extend(true, $.msDropDown, msBeautify);
$.extend(true, $.msDropdown, msBeautify);
// make compatibiliy with old and new jquery
if ($.fn.prop === undefined) {$.fn.prop = $.fn.attr;}
if ($.fn.on === undefined) {$.fn.on = $.fn.bind;$.fn.off = $.fn.unbind;}
if (typeof $.expr.createPseudo === 'function') {
	//jQuery 1.8  or greater
	$.expr[':'].Contains = $.expr.createPseudo(function (arg) {return function (elem) { return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0; }; });
} else {
	//lower version
	$.expr[':'].Contains = function (a, i, m) {return $(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0; };
}
//dropdown class
function dd(element, settings) {
	var _settings = $.extend(true,{byJson: {data: null, selectedIndex: 0, name: null, size: 0, multiple: false, width: 250},
		mainCSS: 'dd',
		height: 120, //not using currently
		visibleRows: 7,
		rowHeight: 0,
		showIcon: true,
		zIndex: 9999,
		useSprite: false,
		animStyle: 'slideDown',
		event:'click',
		openDirection: 'auto', //auto || alwaysUp
		jsonTitle: true,
		style: '',
		disabledOpacity: 0.7,
		disabledOptionEvents: true,
		childWidth:0,
		enableCheckbox:false, //this needs to multiple or it will set element to multiple
		checkboxNameSuffix:'_mscheck',
		append:'',
		prepend:'',
		on: {create: null,open: null,close: null,add: null,remove: null,change: null,blur: null,click: null,dblclick: null,mousemove: null,mouseover: null,mouseout: null,focus: null,mousedown: null,mouseup: null}
		}, settings);								  
	var _this = this; //this class	 
	var _holderId = {postElementHolder: '_msddHolder', postID: '_msdd', postTitleID: '_title',postTitleTextID: '_titleText', postChildID: '_child'};
	var _styles = {dd:_settings.mainCSS, ddTitle: 'ddTitle', arrow: 'arrow arrowoff', ddChild: 'ddChild', ddTitleText: 'ddTitleText',disabled: 'disabled', enabled: 'enabled', ddOutOfVision: 'ddOutOfVision', borderTop: 'borderTop', noBorderTop: 'noBorderTop', selected: 'selected', divider: 'divider', optgroup: "optgroup", optgroupTitle: "optgroupTitle", description: "description", label: "ddlabel",hover: 'hover',disabledAll: 'disabledAll'};
	var _styles_i = {li: '_msddli_',borderRadiusTp: 'borderRadiusTp',ddChildMore: 'border shadow',fnone: "fnone"};
	var _isList = false, _isMultiple=false,_isDisabled=false, _cacheElement = {}, _element, _orginial = {}, _isOpen=false;
	var DOWN_ARROW = 40, UP_ARROW = 38, LEFT_ARROW=37, RIGHT_ARROW=39, ESCAPE = 27, ENTER = 13, ALPHABETS_START = 47, SHIFT=16 , CONTROL = 17;
	var _shiftHolded=false, _controlHolded=false,_lastTarget=null,_forcedTrigger=false, _oldSelected, _isCreated = false;
	var _doc = document, _ua = window.navigator.userAgent, _isIE = _ua.match(/msie/i);
	var msieversion = function()
   	{      
      var msie = _ua.indexOf("MSIE");
      if ( msie > 0 ) {      // If Internet Explorer, return version number
         return parseInt (_ua.substring (msie+5, _ua.indexOf (".", msie)));
	  } else {                // If another browser, return 0
         return 0;
	  };
   	};
	var _checkDataSetting = function() {
		_settings.mainCSS = $("#"+_element).data("maincss") || _settings.mainCSS;
		_settings.visibleRows = $("#"+_element).data("visiblerows") || _settings.visibleRows;
		if($("#"+_element).data("showicon")==false) {_settings.showIcon = $("#"+_element).data("showicon");};
		_settings.useSprite = $("#"+_element).data("usesprite") || _settings.useSprite;
		_settings.animStyle = $("#"+_element).data("animstyle") || _settings.animStyle;
		_settings.event = $("#"+_element).data("event") || _settings.event;
		_settings.openDirection = $("#"+_element).data("opendirection") || _settings.openDirection;
		_settings.jsonTitle = $("#"+_element).data("jsontitle") || _settings.jsonTitle;
		_settings.disabledOpacity = $("#"+_element).data("disabledopacity") || _settings.disabledOpacity;
		_settings.childWidth = $("#"+_element).data("childwidth") || _settings.childWidth;
		_settings.enableCheckbox = $("#"+_element).data("enablecheckbox") || _settings.enableCheckbox;
		_settings.checkboxNameSuffix = $("#"+_element).data("checkboxnamesuffix") || _settings.checkboxNameSuffix;
		_settings.append = $("#"+_element).data("append") || _settings.append;
		_settings.prepend = $("#"+_element).data("prepend") || _settings.prepend;		
	};	
	var getElement = function(ele) {
		if (_cacheElement[ele] === undefined) {
			_cacheElement[ele] = _doc.getElementById(ele);
		}
		return _cacheElement[ele];
	}; 	
	var _getIndex = function(opt) {
		var childid = _getPostID("postChildID"); 
		return $("#"+childid + " li."+_styles_i.li).index(opt);
	};
	var _createByJson = function() {
		if (_settings.byJson.data) {
				var validData = ["description","image",  "title"];
				try {
					if (!element.id) {
						element.id = "dropdown"+msBeautify.counter;
					};
					_settings.byJson.data = eval(_settings.byJson.data);
					//change element
					var id = "msdropdown"+(msBeautify.counter++);
					var obj = {};
					obj.id = id;
					obj.name = _settings.byJson.name || element.id; //its name
					if (_settings.byJson.size>0) {
						obj.size = _settings.byJson.size;
					};
					obj.multiple = _settings.byJson.multiple;
					var oSelect = _createElement("select", obj);
					for(var i=0;i<_settings.byJson.data.length;i++) {
						var current = _settings.byJson.data[i];
						var opt = new Option(current.text, current.value);
						for(var p in current) { 
							if (p.toLowerCase() != 'text') { 
								var key = ($.inArray(p.toLowerCase(), validData)!=-1) ? "data-" : "";
								opt.setAttribute(key+p, current[p]);
							};
						};
						oSelect.options[i] = opt;
					};
					getElement(element.id).appendChild(oSelect);
					oSelect.selectedIndex = _settings.byJson.selectedIndex;
					$(oSelect).css({width: _settings.byJson.width+'px'});
					//now change element for access other things
					element = oSelect;
				} catch(e) {
					throw "There is an error in json data.";
				};
		};			
	};
	var _construct = function() {		
		 //set properties
		 _createByJson();
		if (!element.id) {
			element.id = "msdrpdd"+(msBeautify.counter++);
		};						
		_element = element.id;
		_this._element = _element;
		_checkDataSetting();		
		_isDisabled = getElement(_element).disabled;
		var useCheckbox = _settings.enableCheckbox;
		if(Boolean(useCheckbox)===true) {
			getElement(_element).multiple = true;
			_settings.enableCheckbox = true;
		};
		_isList = (getElement(_element).size>1 || getElement(_element).multiple==true) ? true : false;
		//trace("_isList "+_isList);
		if (_isList) {_isMultiple = getElement(_element).multiple;};			
		_mergeAllProp();		
		//create layout
		_createLayout();		
		//set ui prop
		_updateProp("uiData", _getDataAndUI());
		_updateProp("selectedOptions", $("#"+_element +" option:selected"));
		var childid = _getPostID("postChildID");
		_oldSelected = $("#" + childid + " li." + _styles.selected);
		
	 };	
	 /********************************************************************************************/	
	var _getPostID = function (id) {
		return _element+_holderId[id];
	};
	var _getInternalStyle = function(ele) {		 
		 var s = (ele.style === undefined) ? "" : ele.style.cssText;
		 return s;
	};
	var _parseOption = function(opt) {
		var imagePath = '', title ='', description='', value=-1, text='', className='', imagecss = '';
		if (opt !== undefined) {
			var attrTitle = opt.title || "";
			//data-title
			if (attrTitle!="") {
				var reg = /^\{.*\}$/;
				var isJson = reg.test(attrTitle);
				if (isJson && _settings.jsonTitle) {
					var obj =  eval("["+attrTitle+"]");	
				};				 
				title = (isJson && _settings.jsonTitle) ? obj[0].title : title;
				description = (isJson && _settings.jsonTitle) ? obj[0].description : description;
				imagePath = (isJson && _settings.jsonTitle) ? obj[0].image : attrTitle;
				imagecss = (isJson && _settings.jsonTitle) ? obj[0].imagecss : imagecss;
			};

			text = opt.text || '';
			value = opt.value || '';
			className = opt.className || "";
			//ignore title attribute if playing with data tags			
			title = $(opt).prop("data-title") || $(opt).data("title") || (title || "");
			description = $(opt).prop("data-description") || $(opt).data("description") || (description || "");
			imagePath = $(opt).prop("data-image") || $(opt).data("image") || (imagePath || "");
			imagecss = $(opt).prop("data-imagecss") || $(opt).data("imagecss") || (imagecss || "");
			
		};
		var o = {image: imagePath, title: title, description: description, value: value, text: text, className: className, imagecss:imagecss};
		return o;
	};	 
	var _createElement = function(nm, attr, html) {
		var tag = _doc.createElement(nm);
		if (attr) {
		 for(var i in attr) {
			 switch(i) {
				 case "style":
					tag.style.cssText  = attr[i];
				 break;
				 default:
					tag[i]  = attr[i];
				 break;
			 };	
		 };
		};
		if (html) {
		 tag.innerHTML = html;
		};
		return tag;
	};
	 /********************************************************************************************/
	  /*********************** <layout> *************************************/
	var _hideOriginal = function() {
		var hidid = _getPostID("postElementHolder");
		if ($("#"+hidid).length==0) {			 
			var obj = {style: 'height: 0px;overflow: hidden;position: absolute;',className: _styles.ddOutOfVision};	
			obj.id = hidid;
			var oDiv = _createElement("div", obj);	
			$("#"+_element).after(oDiv);
			$("#"+_element).appendTo($("#"+hidid));
		} else {
			$("#"+hidid).css({height: 0,overflow: 'hidden',position: 'absolute'});
		};
	};
	var _createWrapper = function () {
		var obj = {
			className: _styles.dd + " ddcommon borderRadius"
		};
		var styles = _getInternalStyle(getElement(_element));
		var w = $("#" + _element).outerWidth();
		obj.style = "width: " + w + "px;";
		if (styles.length > 0) {
			obj.style = obj.style + "" + styles;
		};
		obj.id = _getPostID("postID");
		var oDiv = _createElement("div", obj);
		return oDiv;
	};
	var _createTitle = function () {
		var selectedOption;
		if(getElement(_element).selectedIndex>=0) {
			selectedOption = getElement(_element).options[getElement(_element).selectedIndex];
		} else {
			selectedOption = {value:'', text:''};
		}
		var spriteClass = "", selectedClass = "";
		//check sprite
		var useSprite = $("#"+_element).data("usesprite");
		if(useSprite) { _settings.useSprite = useSprite; };
		if (_settings.useSprite != false) {
			spriteClass = " " + _settings.useSprite;
			selectedClass = " " + selectedOption.className;
		};
		var oTitle = _createElement("div", {className: _styles.ddTitle + spriteClass + " " + _styles_i.borderRadiusTp});
		//divider
		var oDivider = _createElement("span", {className: _styles.divider});
		//arrow
		var oArrow = _createElement("span", {className: _styles.arrow});
		//title Text
		var titleid = _getPostID("postTitleID");
		var oTitleText = _createElement("span", {className: _styles.ddTitleText + selectedClass, id: titleid});
	
		var parsed = _parseOption(selectedOption);
		var arrowPath = parsed.image;
		var sText = parsed.text || "";		
		if (arrowPath != "" && _settings.showIcon) {
			var oIcon = _createElement("img");
			oIcon.src = arrowPath;
			if(parsed.imagecss!="") {
				oIcon.className = parsed.imagecss+" ";
			};
		};
		var oTitleText_in = _createElement("span", {className: _styles.label}, sText);
		oTitle.appendChild(oDivider);
		oTitle.appendChild(oArrow);
		if (oIcon) {
			oTitleText.appendChild(oIcon);
		};
		oTitleText.appendChild(oTitleText_in);
		oTitle.appendChild(oTitleText);
		var oDescription = _createElement("span", {className: _styles.description}, parsed.description);
		oTitleText.appendChild(oDescription);
		return oTitle;
	};
	var _createFilterBox = function () {
		var tid = _getPostID("postTitleTextID");
		var sText = _createElement("input", {id: tid, type: 'text', value: '', autocomplete: 'off', className: 'text shadow borderRadius', style: 'display: none'});
		return sText;
	};
	var _createChild = function (opt) {
		var obj = {};
		var styles = _getInternalStyle(opt);
		if (styles.length > 0) {obj.style = styles; };
		var css = (opt.disabled) ? _styles.disabled : _styles.enabled;
		css = (opt.selected) ? (css + " " + _styles.selected) : css;
		css = css + " " + _styles_i.li;
		obj.className = css;
		if (_settings.useSprite != false) {
			obj.className = css + " " + opt.className;
		};
		var li = _createElement("li", obj);
		var parsed = _parseOption(opt);
		if (parsed.title != "") {
			li.title = parsed.title;
		};
		var arrowPath = parsed.image;
		if (arrowPath != "" && _settings.showIcon) {
			var oIcon = _createElement("img");
			oIcon.src = arrowPath;
			if(parsed.imagecss!="") {
				oIcon.className = parsed.imagecss+" ";
			};
		};
		if (parsed.description != "") {
			var oDescription = _createElement("span", {
				className: _styles.description
			}, parsed.description);
		};
		var sText = opt.text || "";
		var oTitleText = _createElement("span", {
			className: _styles.label
		}, sText);
		//checkbox
		if(_settings.enableCheckbox===true) {
			var chkbox = _createElement("input", {
			type: 'checkbox', name:_element+_settings.checkboxNameSuffix+'[]', value:opt.value||""}); //this can be used for future
			li.appendChild(chkbox);
			if(_settings.enableCheckbox===true) {
				chkbox.checked = (opt.selected) ? true : false;
			};
		};
		if (oIcon) {
			li.appendChild(oIcon);
		};
		li.appendChild(oTitleText);
		if (oDescription) {
			li.appendChild(oDescription);
		} else {
			if (oIcon) {
				oIcon.className = oIcon.className+_styles_i.fnone;
			};
		};
		var oClear = _createElement("div", {className: 'clear'});
		li.appendChild(oClear);
		return li;
	};
	var _createChildren = function () {
		var childid = _getPostID("postChildID");
		var obj = {className: _styles.ddChild + " ddchild_ " + _styles_i.ddChildMore, id: childid};
		if (_isList == false) {
			obj.style = "z-index: " + _settings.zIndex;
		} else {
			obj.style = "z-index:1";
		};
		var childWidth = $("#"+_element).data("childwidth") || _settings.childWidth;
		if(childWidth) {
			obj.style =  (obj.style || "") + ";width:"+childWidth;
		};		
		var oDiv = _createElement("div", obj);
		var ul = _createElement("ul");
		if (_settings.useSprite != false) {
			ul.className = _settings.useSprite;
		};
		var allOptions = getElement(_element).children;
		for (var i = 0; i < allOptions.length; i++) {
			var current = allOptions[i];
			var li;
			if (current.nodeName.toLowerCase() == "optgroup") {
				//create ul
				li = _createElement("li", {className: _styles.optgroup});
				var span = _createElement("span", {className: _styles.optgroupTitle}, current.label);
				li.appendChild(span);
				var optChildren = current.children;
				var optul = _createElement("ul");
				for (var j = 0; j < optChildren.length; j++) {
					var opt_li = _createChild(optChildren[j]);
					optul.appendChild(opt_li);
				};
				li.appendChild(optul);
			} else {
				li = _createChild(current);
			};
			ul.appendChild(li);
		};
		oDiv.appendChild(ul);		
		return oDiv;
	};
	var _childHeight = function (val) {
		var childid = _getPostID("postChildID");
		if (val) {
			if (val == -1) { //auto
				$("#"+childid).css({height: "auto", overflow: "auto"});
			} else {				
				$("#"+childid).css("height", val+"px");
			};
			return false;
		};
		//else return height
		var iHeight;
		if (getElement(_element).options.length > _settings.visibleRows) {
			var margin = parseInt($("#" + childid + " li:first").css("padding-bottom")) + parseInt($("#" + childid + " li:first").css("padding-top"));
			if(_settings.rowHeight===0) {
				$("#" + childid).css({visibility:'hidden',display:'block'}); //hack for first child
				_settings.rowHeight = Math.round($("#" + childid + " li:first").height());
				$("#" + childid).css({visibility:'visible'});
				if(!_isList || _settings.enableCheckbox===true) {
					$("#" + childid).css({display:'none'});
				};
			};
			iHeight = ((_settings.rowHeight + margin) * _settings.visibleRows);
		} else if (_isList) {
			iHeight = $("#" + _element).height(); //get height from original element
		};		
		return iHeight;
	};
	var _applyChildEvents = function () {
		var childid = _getPostID("postChildID");
		$("#" + childid).on("click", function (e) {
			if (_isDisabled === true) return false;
			//prevent body click
			e.preventDefault();
			e.stopPropagation();
			if (_isList) {
				_bind_on_events();
			};
		});
		$("#" + childid + " li." + _styles.enabled).on("click", function (e) {
			if(e.target.nodeName.toLowerCase() !== "input") {
				_close(this);
			};
		});
		$("#" + childid + " li." + _styles.enabled).on("mousedown", function (e) {
			if (_isDisabled === true) return false;
			_oldSelected = $("#" + childid + " li." + _styles.selected);
			_lastTarget = this;
			e.preventDefault();
			e.stopPropagation();
			//select current input
			if(_settings.enableCheckbox===true) {
				if(e.target.nodeName.toLowerCase() === "input") {
					_controlHolded = true;
				};	
			};
			if (_isList === true) {
				if (_isMultiple) {					
					if (_shiftHolded === true) {
						$(this).addClass(_styles.selected);
						var selected = $("#" + childid + " li." + _styles.selected);
						var lastIndex = _getIndex(this);
						if (selected.length > 1) {
							var items = $("#" + childid + " li." + _styles_i.li);
							var ind1 = _getIndex(selected[0]);
							var ind2 = _getIndex(selected[1]);
							if (lastIndex > ind2) {
								ind1 = (lastIndex);
								ind2 = ind2 + 1;
							};
							for (var i = Math.min(ind1, ind2); i <= Math.max(ind1, ind2); i++) {
								var current = items[i];
								if ($(current).hasClass(_styles.enabled)) {
									$(current).addClass(_styles.selected);
								};
							};
						};
					} else if (_controlHolded === true) {
						$(this).toggleClass(_styles.selected); //toggle
						if(_settings.enableCheckbox===true) {
							var checkbox = this.childNodes[0];
							checkbox.checked = !checkbox.checked; //toggle
						};
					} else {
						$("#" + childid + " li." + _styles.selected).removeClass(_styles.selected);
						$("#" + childid + " input:checkbox").prop("checked", false);
						$(this).addClass(_styles.selected);
						if(_settings.enableCheckbox===true) {
							this.childNodes[0].checked = true;
						};
					};					
				} else {
					$("#" + childid + " li." + _styles.selected).removeClass(_styles.selected);
					$(this).addClass(_styles.selected);
				};
				//fire event on mouseup
			} else {
				$("#" + childid + " li." + _styles.selected).removeClass(_styles.selected);
				$(this).addClass(_styles.selected);
			};		
		});
		$("#" + childid + " li." + _styles.enabled).on("mouseenter", function (e) {
			if (_isDisabled === true) return false;
			e.preventDefault();
			e.stopPropagation();
			if (_lastTarget != null) {
				if (_isMultiple) {
					$(this).addClass(_styles.selected);
					if(_settings.enableCheckbox===true) {
						this.childNodes[0].checked = true;
					};
				};
			};
		});
	
		$("#" + childid + " li." + _styles.enabled).on("mouseover", function (e) {
			if (_isDisabled === true) return false;
			$(this).addClass(_styles.hover);
		});
		$("#" + childid + " li." + _styles.enabled).on("mouseout", function (e) {
			if (_isDisabled === true) return false;
			$("#" + childid + " li." + _styles.hover).removeClass(_styles.hover);
		});
	
		$("#" + childid + " li." + _styles.enabled).on("mouseup", function (e) {
			if (_isDisabled === true) return false;
			e.preventDefault();
			e.stopPropagation();
			if(_settings.enableCheckbox===true) {
				_controlHolded = false;
			};
			var selected = $("#" + childid + " li." + _styles.selected).length;			
			_forcedTrigger = (_oldSelected.length != selected || selected == 0) ? true : false;	
			_fireAfterItemClicked();
			_unbind_on_events(); //remove old one
			_bind_on_events();
			_lastTarget = null;
		});
	
		/* options events */
		if (_settings.disabledOptionEvents == false) {
			$("#" + childid + " li." + _styles_i.li).on("click", function (e) {
				if (_isDisabled === true) return false;
				fireOptionEventIfExist(this, "click");
			});
			$("#" + childid + " li." + _styles_i.li).on("mouseenter", function (e) {
				if (_isDisabled === true) return false;
				fireOptionEventIfExist(this, "mouseenter");
			});
			$("#" + childid + " li." + _styles_i.li).on("mouseover", function (e) {
				if (_isDisabled === true) return false;
				fireOptionEventIfExist(this, "mouseover");
			});
			$("#" + childid + " li." + _styles_i.li).on("mouseout", function (e) {
				if (_isDisabled === true) return false;
				fireOptionEventIfExist(this, "mouseout");
			});
			$("#" + childid + " li." + _styles_i.li).on("mousedown", function (e) {
				if (_isDisabled === true) return false;
				fireOptionEventIfExist(this, "mousedown");
			});
			$("#" + childid + " li." + _styles_i.li).on("mouseup", function (e) {
				if (_isDisabled === true) return false;
				fireOptionEventIfExist(this, "mouseup");
			});
		};
	};
	var _removeChildEvents = function () {
		var childid = _getPostID("postChildID");
		$("#" + childid).off("click");
		$("#" + childid + " li." + _styles.enabled).off("mouseenter");
		$("#" + childid + " li." + _styles.enabled).off("click");
		$("#" + childid + " li." + _styles.enabled).off("mouseover");
		$("#" + childid + " li." + _styles.enabled).off("mouseout");
		$("#" + childid + " li." + _styles.enabled).off("mousedown");
		$("#" + childid + " li." + _styles.enabled).off("mouseup");
	};
	var _applyEvents = function () {
		var id = _getPostID("postID");
		var childid = _getPostID("postChildID");		
		$("#" + id).on(_settings.event, function (e) {			
			if (_isDisabled === true) return false;
			fireEventIfExist("click");
			//prevent body click
			e.preventDefault();
			e.stopPropagation();
			_open(e);
		});
		_applyChildEvents();		
		$("#" + id).on("dblclick", on_dblclick);
		$("#" + id).on("mousemove", on_mousemove);
		$("#" + id).on("mouseenter", on_mouseover);
		$("#" + id).on("mouseleave", on_mouseout);
		$("#" + id).on("mousedown", on_mousedown);
		$("#" + id).on("mouseup", on_mouseup);
	};
	//after create
	var _fixedForList = function () {
		var id = _getPostID("postID");
		var childid = _getPostID("postChildID");		
		if (_isList === true && _settings.enableCheckbox===false) {
			$("#" + id + " ." + _styles.ddTitle).hide();
			$("#" + childid).css({display: 'block', position: 'relative'});	
			//_open();
		} else {
			if(_settings.enableCheckbox===false) {
				_isMultiple = false; //set multiple off if this is not a list
			};
			$("#" + id + " ." + _styles.ddTitle).show();
			$("#" + childid).css({display: 'none', position: 'absolute'});
			//set value
			var first = $("#" + childid + " li." + _styles.selected)[0];
			$("#" + childid + " li." + _styles.selected).removeClass(_styles.selected);
			var index = _getIndex($(first).addClass(_styles.selected));
			_setValue(index);
		};
		_childHeight(_childHeight()); //get and set height 
	};
	var _fixedForDisabled = function () {
		var id = _getPostID("postID");
		var opc = (_isDisabled == true) ? _settings.disabledOpacity : 1;
		if (_isDisabled === true) {
			$("#" + id).addClass(_styles.disabledAll);
		} else {
			$("#" + id).removeClass(_styles.disabledAll);
		};
	};
	var _fixedSomeUI = function () {
		//auto filter
		var tid = _getPostID("postTitleTextID");
		$("#" + tid).on("keyup", _applyFilters);
		//if is list
		_fixedForList();
		_fixedForDisabled();
	};
	var _createLayout = function () {		
		var oDiv = _createWrapper();
		var oTitle = _createTitle();
		oDiv.appendChild(oTitle);
		//auto filter box
		var oFilterBox = _createFilterBox();
		oDiv.appendChild(oFilterBox);
	
		var oChildren = _createChildren();
		oDiv.appendChild(oChildren);
		$("#" + _element).after(oDiv);
		_hideOriginal(); //hideOriginal
		_fixedSomeUI();
		_applyEvents();
		
		var childid = _getPostID("postChildID");
		//append
		if(_settings.append!='') {
			$("#" + childid).append(_settings.append);
		};
		//prepend
		if(_settings.prepend!='') {
			$("#" + childid).prepend(_settings.prepend);
		};		
		if (typeof _settings.on.create == "function") {
			_settings.on.create.apply(_this, arguments);
		};
	};
	var _selectMutipleOptions = function (bySelected) {
		var childid = _getPostID("postChildID");
		var selected = bySelected || $("#" + childid + " li." + _styles.selected); //bySelected or by argument
		for (var i = 0; i < selected.length; i++) {
			var ind = _getIndex(selected[i]);
			getElement(_element).options[ind].selected = "selected";
		};
		_setValue(selected);
	};
	var _fireAfterItemClicked = function () {
		//console.log("_fireAfterItemClicked")
		var childid = _getPostID("postChildID");
		var selected = $("#" + childid + " li." + _styles.selected);		
		if (_isMultiple && (_shiftHolded || _controlHolded) || _forcedTrigger) {
			getElement(_element).selectedIndex = -1; //reset old
		};
		var index;
		if (selected.length == 0) {
			index = -1;
		} else if (selected.length > 1) {
			//selected multiple
			_selectMutipleOptions(selected);
			//index = $("#" + childid + " li." + _styles.selected);
			
		} else {
			//if one selected
			index = _getIndex($("#" + childid + " li." + _styles.selected));
		};		
		if ((getElement(_element).selectedIndex != index || _forcedTrigger) && selected.length<=1) {			
			_forcedTrigger = false;			
			var evt = has_handler("change");
			getElement(_element).selectedIndex = index;	
			_setValue(index);
			//local
			if (typeof _settings.on.change == "function") {
				var d = _getDataAndUI();
				_settings.on.change(d.data, d.ui);
			};			
			$("#" + _element).trigger("change");			
		};
	};
	var _setValue = function (index, byvalue) {
		if (index !== undefined) {
			var selectedIndex, value, selectedText;
			if (index == -1) {
				selectedIndex = -1;
				value = "";
				selectedText = "";
				_updateTitleUI(-1);
			} else {
				//by index or byvalue
				if (typeof index != "object") {
					var opt = getElement(_element).options[index];
					getElement(_element).selectedIndex = index;
					selectedIndex = index;
					value = _parseOption(opt);
					selectedText = (index >= 0) ? getElement(_element).options[index].text : "";
					_updateTitleUI(undefined, value);
					value = value.value; //for bottom
				} else {
					//this is multiple or by option					
					selectedIndex = (byvalue && byvalue.index) || getElement(_element).selectedIndex;
					value = (byvalue && byvalue.value) || getElement(_element).value;
					selectedText = (byvalue && byvalue.text) || getElement(_element).options[getElement(_element).selectedIndex].text || "";
					_updateTitleUI(selectedIndex);
				};
			};
			_updateProp("selectedIndex", selectedIndex);
			_updateProp("value", value);
			_updateProp("selectedText", selectedText);
			_updateProp("children", getElement(_element).children);
			_updateProp("uiData", _getDataAndUI());
			_updateProp("selectedOptions", $("#" + _element + " option:selected"));
		};
	};
	var has_handler = function (name) {
		//True if a handler has been added in the html.
		var evt = {byElement: false, byJQuery: false, hasEvent: false};
		var obj = $("#" + _element);
		//console.log(name)
		try {
			//console.log(obj.prop("on" + name) + " "+name);
			if (obj.prop("on" + name) !== null) {
				evt.hasEvent = true;
				evt.byElement = true;
			};
		} catch(e) {
			//console.log(e.message);
		}
		// True if a handler has been added using jQuery.
		var evs;
		if (typeof $._data == "function") { //1.8
			evs = $._data(obj[0], "events");
		} else {
			evs = obj.data("events");
		};
		if (evs && evs[name]) {
			evt.hasEvent = true;
			evt.byJQuery = true;
		};
		return evt;
	};
	var _bind_on_events = function () {
		_unbind_on_events();
		$("body").on("click", _close);
		//bind more events		 
		$(document).on("keydown", on_keydown);
		$(document).on("keyup", on_keyup);
		//focus will work on this	 		 
	};
	var _unbind_on_events = function () {
		$("body").off("click", _close);
		//bind more events
		$(document).off("keydown", on_keydown);
		$(document).off("keyup", on_keyup);
	};
	var _applyFilters = function () {
		var childid = _getPostID("postChildID");
		var tid = _getPostID("postTitleTextID");
		var sText = getElement(tid).value;
		if (sText.length == 0) {
			$("#" + childid + " li:hidden").show(); //show if hidden
			_childHeight(_childHeight());
		} else {
			$("#" + childid + " li").hide();
			$("#" + childid + " li:Contains('" + sText + "')").show();	
			if ($("#" + childid + " li:visible").length <= _settings.visibleRows) {
				_childHeight(-1); //set autoheight
			};
		};		
	};
	var _showFilterBox = function () {
		var tid = _getPostID("postTitleTextID");
		if ($("#" + tid + ":hidden").length > 0 && _controlHolded == false) {
			$("#" + tid + ":hidden").show().val("");
			getElement(tid).focus();
		};
	};
	var _hideFilterBox = function () {
		var tid = _getPostID("postTitleTextID");
		if ($("#" + tid + ":visible").length > 0) {
			$("#" + tid + ":visible").hide();
			getElement(tid).blur();
		};
	};
	var on_keydown = function (evt) {
		var tid = _getPostID("postTitleTextID");
		var childid = _getPostID("postChildID");
		switch (evt.keyCode) {
			case DOWN_ARROW:
			case RIGHT_ARROW:
				evt.preventDefault();
				evt.stopPropagation();
				//_hideFilterBox();
				_next();				
				break;
			case UP_ARROW:
			case LEFT_ARROW:
				evt.preventDefault();
				evt.stopPropagation();
				//_hideFilterBox();
				_previous();
				break;
			case ESCAPE:
			case ENTER:
				evt.preventDefault();
				evt.stopPropagation();
				_close();
				var selected = $("#" + childid + " li." + _styles.selected).length;	
				_forcedTrigger = (_oldSelected.length != selected || selected == 0) ? true : false;				
				_fireAfterItemClicked();
				_unbind_on_events(); //remove old one				
				_lastTarget = null;			
				break;
			case SHIFT:
				_shiftHolded = true;
				break;
			case CONTROL:
				_controlHolded = true;
				break;
			default:
				if (evt.keyCode >= ALPHABETS_START && _isList === false) {
					_showFilterBox();
				};
				break;
		};
		if (_isDisabled === true) return false;
		fireEventIfExist("keydown");
	};
	var on_keyup = function (evt) {
		switch (evt.keyCode) {
			case SHIFT:
				_shiftHolded = false;
				break;
			case CONTROL:
				_controlHolded = false;
				break;
		};
		if (_isDisabled === true) return false;
		fireEventIfExist("keyup");
	};
	var on_dblclick = function (evt) {
		if (_isDisabled === true) return false;
		fireEventIfExist("dblclick");
	};
	var on_mousemove = function (evt) {
		if (_isDisabled === true) return false;
		fireEventIfExist("mousemove");
	};
	
	var on_mouseover = function (evt) {
		if (_isDisabled === true) return false;
		evt.preventDefault();
		fireEventIfExist("mouseover");
	};
	var on_mouseout = function (evt) {
		if (_isDisabled === true) return false;
		evt.preventDefault();
		fireEventIfExist("mouseout");
	};
	var on_mousedown = function (evt) {
		if (_isDisabled === true) return false;
		fireEventIfExist("mousedown");
	};
	var on_mouseup = function (evt) {
		if (_isDisabled === true) return false;
		fireEventIfExist("mouseup");
	};
	var option_has_handler = function (opt, name) {
		//True if a handler has been added in the html.
		var evt = {byElement: false, byJQuery: false, hasEvent: false};
		if ($(opt).prop("on" + name) != undefined) {
			evt.hasEvent = true;
			evt.byElement = true;
		};
		// True if a handler has been added using jQuery.
		var evs = $(opt).data("events");
		if (evs && evs[name]) {
			evt.hasEvent = true;
			evt.byJQuery = true;
		};
		return evt;
	};
	var fireOptionEventIfExist = function (li, evt_n) {
		if (_settings.disabledOptionEvents == false) {
			var opt = getElement(_element).options[_getIndex(li)];
			//check if original has some
			if (option_has_handler(opt, evt_n).hasEvent === true) {
				if (option_has_handler(opt, evt_n).byElement === true) {
					opt["on" + evt_n]();
				};
				if (option_has_handler(opt, evt_n).byJQuery === true) {
					switch (evt_n) {
						case "keydown":
						case "keyup":
							//key down/up will check later
							break;
						default:
							$(opt).trigger(evt_n);
							break;
					};
				};
				return false;
			};
		};
	};
	var fireEventIfExist = function (evt_n) {
		//local
		if (typeof _settings.on[evt_n] == "function") {
			_settings.on[evt_n].apply(this, arguments);
		};
		//check if original has some
		if (has_handler(evt_n).hasEvent === true) {
			if (has_handler(evt_n).byElement === true) {
				getElement(_element)["on" + evt_n]();
			};
			if (has_handler(evt_n).byJQuery === true) {
				switch (evt_n) {
					case "keydown":
					case "keyup":
						//key down/up will check later
						break;
					default:
						$("#" + _element).trigger(evt_n);
						break;
				};
			};
			return false;
		};
	};
	/******************************* navigation **********************************************/
	var _scrollToIfNeeded = function (opt) {
		var childid = _getPostID("postChildID");
		//if scroll is needed
		opt = (opt !== undefined) ? opt : $("#" + childid + " li." + _styles.selected);
		if (opt.length > 0) {
			var pos = parseInt(($(opt).position().top));
			var ch = parseInt($("#" + childid).height());
			if (pos > ch) {
				var top = pos + $("#" + childid).scrollTop() - (ch/2);
				$("#" + childid).animate({scrollTop:top}, 500);
			};
		};
	};
	var _next = function () {
		var childid = _getPostID("postChildID");
		var items = $("#" + childid + " li:visible." + _styles_i.li);
		var selected = $("#" + childid + " li:visible." + _styles.selected);
		selected = (selected.length==0) ? items[0] : selected;
		var index = $("#" + childid + " li:visible." + _styles_i.li).index(selected);
		if ((index < items.length - 1)) {
			index = getNext(index);
			if (index < items.length) { //check again - hack for last disabled 
				if (!_shiftHolded || !_isList || !_isMultiple) {
					$("#" + childid + " ." + _styles.selected).removeClass(_styles.selected);
				};
				$(items[index]).addClass(_styles.selected);
				_updateTitleUI(index);
				if (_isList == true) {
					_fireAfterItemClicked();
				};
				_scrollToIfNeeded($(items[index]));
			};
			if (!_isList) {
				_adjustOpen();
			};
		};	
		function getNext(ind) {
			ind = ind + 1;
			if (ind > items.length) {
				return ind;
			};
			if ($(items[ind]).hasClass(_styles.enabled) === true) {
				return ind;
			};
			return ind = getNext(ind);
		};
	};
	var _previous = function () {
		var childid = _getPostID("postChildID");
		var selected = $("#" + childid + " li:visible." + _styles.selected);
		var items = $("#" + childid + " li:visible." + _styles_i.li);
		var index = $("#" + childid + " li:visible." + _styles_i.li).index(selected[0]);
		if (index >= 0) {
			index = getPrev(index);
			if (index >= 0) { //check again - hack for disabled 
				if (!_shiftHolded || !_isList || !_isMultiple) {
					$("#" + childid + " ." + _styles.selected).removeClass(_styles.selected);
				};
				$(items[index]).addClass(_styles.selected);
				_updateTitleUI(index);
				if (_isList == true) {
					_fireAfterItemClicked();
				};
				if (parseInt(($(items[index]).position().top + $(items[index]).height())) <= 0) {
					var top = ($("#" + childid).scrollTop() - $("#" + childid).height()) - $(items[index]).height();
					$("#" + childid).animate({scrollTop: top}, 500);
				};
			};
			if (!_isList) {
				_adjustOpen();
			};
		};
	
		function getPrev(ind) {
			ind = ind - 1;
			if (ind < 0) {
				return ind;
			};
			if ($(items[ind]).hasClass(_styles.enabled) === true) {
				return ind;
			};
			return ind = getPrev(ind);
		};
	};
	var _adjustOpen = function () {
		var id = _getPostID("postID");
		var childid = _getPostID("postChildID");
		var pos = $("#" + id).offset();
		var mH = $("#" + id).height();
		var wH = $(window).height();
		var st = $(window).scrollTop();
		var cH = $("#" + childid).height();
		var top = $("#" + id).height(); //this close so its title height
		if ((wH + st) < Math.floor(cH + mH + pos.top) || _settings.openDirection.toLowerCase() == 'alwaysup') {
			top = cH;
			$("#" + childid).css({top: "-" + top + "px", display: 'block', zIndex: _settings.zIndex});
			$("#" + id).removeClass("borderRadius borderRadiusTp").addClass("borderRadiusBtm");
			var top = $("#" + childid).offset().top;
			if (top < -10) {
				$("#" + childid).css({top: (parseInt($("#" + childid).css("top")) - top + 20 + st) + "px", zIndex: _settings.zIndex});
				$("#" + id).removeClass("borderRadiusBtm borderRadiusTp").addClass("borderRadius");
			};
		} else {
			$("#" + childid).css({top: top + "px", zIndex: _settings.zIndex});
			$("#" + id).removeClass("borderRadius borderRadiusBtm").addClass("borderRadiusTp");
		};
		//hack for ie zindex
		//i hate ie :D
		if(_isIE) {
			if(msieversion()<=7) {
				$('div.ddcommon').css("zIndex", _settings.zIndex-10);
				$("#" + id).css("zIndex", _settings.zIndex+5);
			};
		};		
	};
	var _open = function (e) {
		if (_isDisabled === true) return false;
		var id = _getPostID("postID");
		var childid = _getPostID("postChildID");
		if (!_isOpen) {
			_isOpen = true;
			if (msBeautify.oldDiv != '') {
				$("#" + msBeautify.oldDiv).css({display: "none"}); //hide all 
			};
			msBeautify.oldDiv = childid;
			$("#" + childid + " li:hidden").show(); //show if hidden
			_adjustOpen();
			var animStyle = _settings.animStyle;
			if(animStyle=="" || animStyle=="none") {
				$("#" + childid).css({display:"block"});
				_scrollToIfNeeded();
				if (typeof _settings.on.open == "function") {
					var d = _getDataAndUI();
					_settings.on.open(d.data, d.ui);
				};
			} else {				
				$("#" + childid)[animStyle]("fast", function () {
					_scrollToIfNeeded();
					if (typeof _settings.on.open == "function") {
						var d = _getDataAndUI();
						_settings.on.open(d.data, d.ui);
					};
				});
			};
			_bind_on_events();
		} else {
			if(_settings.event!=='mouseover') {
				_close();
			};
		};
	};
	var _close = function (e) {
		_isOpen = false;
		var id = _getPostID("postID");
		var childid = _getPostID("postChildID");
		if (_isList === false || _settings.enableCheckbox===true) {
			$("#" + childid).css({display: "none"});
			$("#" + id).removeClass("borderRadiusTp borderRadiusBtm").addClass("borderRadius");			
		};
		_unbind_on_events();
		if (typeof _settings.on.close == "function") {
			var d = _getDataAndUI();
			_settings.on.close(d.data, d.ui);
		};
		//rest some old stuff
		_hideFilterBox();
		_childHeight(_childHeight()); //its needed after filter applied
		$("#" + childid).css({zIndex:1})		
	};
	/*********************** </layout> *************************************/	
	var _mergeAllProp = function () {
		try {
			_orginial = $.extend(true, {}, getElement(_element));
			for (var i in _orginial) {
				if (typeof _orginial[i] != "function") {				
					_this[i] = _orginial[i]; //properties
				};
			};
		} catch(e) {
			//silent
		};
		_this.selectedText = (getElement(_element).selectedIndex >= 0) ? getElement(_element).options[getElement(_element).selectedIndex].text : "";		
		_this.version = msBeautify.version.msDropdown;
		_this.author = msBeautify.author;
	};
	var _getDataAndUIByOption = function (opt) {
		if (opt != null && typeof opt != "undefined") {
			var childid = _getPostID("postChildID");
			var data = _parseOption(opt);
			var ui = $("#" + childid + " li." + _styles_i.li + ":eq(" + (opt.index) + ")");
			return {data: data, ui: ui, option: opt, index: opt.index};
		};
		return null;
	};
	var _getDataAndUI = function () {
		var childid = _getPostID("postChildID");
		var ele = getElement(_element);
		var data, ui, option, index;
		if (ele.selectedIndex == -1) {
			data = null;
			ui = null;
			option = null;
			index = -1;
		} else {
			ui = $("#" + childid + " li." + _styles.selected);
			if (ui.length > 1) {
				var d = [], op = [], ind = [];
				for (var i = 0; i < ui.length; i++) {
					var pd = _getIndex(ui[i]);
					d.push(pd);
					op.push(ele.options[pd]);
				};
				data = d;
				option = op;
				index = d;
			} else {
				option = ele.options[ele.selectedIndex];
				data = _parseOption(option);
				index = ele.selectedIndex;
			};
		};
		return {data: data, ui: ui, index: index, option: option};
	};
	var _updateTitleUI = function (index, byvalue) {
		var titleid = _getPostID("postTitleID");
		var value = {};
		if (index == -1) {
			value.text = "&nbsp;";
			value.className = "";
			value.description = "";
			value.image = "";
		} else if (typeof index != "undefined") {
			var opt = getElement(_element).options[index];
			value = _parseOption(opt);
		} else {
			value = byvalue;
		};
		//update title and current
		$("#" + titleid).find("." + _styles.label).html(value.text);
		getElement(titleid).className = _styles.ddTitleText + " " + value.className;
		//update desction		 
		if (value.description != "") {
			$("#" + titleid).find("." + _styles.description).html(value.description).show();
		} else {
			$("#" + titleid).find("." + _styles.description).html("").hide();
		};
		//update icon
		var img = $("#" + titleid).find("img");
		if (img.length > 0) {
			$(img).remove();
		};
		if (value.image != "" && _settings.showIcon) {
			img = _createElement("img", {src: value.image});
			$("#" + titleid).prepend(img);
			if(value.imagecss!="") {
				img.className = value.imagecss+" ";
			};
			if (value.description == "") {
				img.className = img.className+_styles_i.fnone;
			};
		};
	};
	var _updateProp = function (p, v) {
		_this[p] = v;
	};
	var _updateUI = function (a, opt, i) { //action, index, opt
		var childid = _getPostID("postChildID");
		var wasSelected = false;
		switch (a) {
			case "add":
				var li = _createChild(opt || getElement(_element).options[i]);				
				var index;
				if (arguments.length == 3) {
					index = i;
				} else {
					index = $("#" + childid + " li." + _styles_i.li).length - 1;
				};				
				if (index < 0 || !index) {
					$("#" + childid + " ul").append(li);
				} else {
					var at = $("#" + childid + " li." + _styles_i.li)[index];
					$(at).before(li);
				};
				_removeChildEvents();
				_applyChildEvents();
				if (_settings.on.add != null) {
					_settings.on.add.apply(this, arguments);
				};
				break;
			case "remove":
				wasSelected = $($("#" + childid + " li." + _styles_i.li)[i]).hasClass(_styles.selected);
				$("#" + childid + " li." + _styles_i.li + ":eq(" + i + ")").remove();
				var items = $("#" + childid + " li." + _styles.enabled);
				if (wasSelected == true) {
					if (items.length > 0) {
						$(items[0]).addClass(_styles.selected);
						var ind = $("#" + childid + " li." + _styles_i.li).index(items[0]);
						_setValue(ind);
					};
				};
				if (items.length == 0) {
					_setValue(-1);
				};
				if ($("#" + childid + " li." + _styles_i.li).length < _settings.visibleRows && !_isList) {
					_childHeight(-1); //set autoheight
				};
				if (_settings.on.remove != null) {
					_settings.on.remove.apply(this, arguments);
				};
				break;
		};	
	};
	/************************** public methods/events **********************/
	this.act = function () {
		var action = arguments[0];
		Array.prototype.shift.call(arguments);
		switch (action) {
			case "add":
				_this.add.apply(this, arguments);
				break;
			case "remove":
				_this.remove.apply(this, arguments);
				break;
			default:
				try {
					getElement(_element)[action].apply(getElement(_element), arguments);
				} catch (e) {
					//there is some error.
				};
				break;
		};
	};
	
	this.add = function () {
		var text, value, title, image, description;
		var obj = arguments[0];		
		if (typeof obj == "string") {
			text = obj;
			value = text;
			opt = new Option(text, value);
		} else {
			text = obj.text || '';
			value = obj.value || text;
			title = obj.title || '';
			image = obj.image || '';
			description = obj.description || '';
			//image:imagePath, title:title, description:description, value:opt.value, text:opt.text, className:opt.className||""
			opt = new Option(text, value);
			$(opt).data("description", description);
			$(opt).data("image", image);
			$(opt).data("title", title);
		};
		arguments[0] = opt; //this option
		getElement(_element).add.apply(getElement(_element), arguments);
		_updateProp("children", getElement(_element)["children"]);
		_updateProp("length", getElement(_element).length);
		_updateUI("add", opt, arguments[1]);
	};
	this.remove = function (i) {
		getElement(_element).remove(i);
		_updateProp("children", getElement(_element)["children"]);
		_updateProp("length", getElement(_element).length);
		_updateUI("remove", undefined, i);
	};
	this.set = function (prop, val) {
		if (typeof prop == "undefined" || typeof val == "undefined") return false;
		prop = prop.toString();
		try {
			_updateProp(prop, val);
		} catch (e) {/*this is ready only */};
		
		switch (prop) {
			case "size":
				getElement(_element)[prop] = val;
				if (val == 0) {
					getElement(_element).multiple = false; //if size is zero multiple should be false
				};
				_isList = (getElement(_element).size > 1 || getElement(_element).multiple == true) ? true : false;
				_fixedForList();
				break;
			case "multiple":
				getElement(_element)[prop] = val;
				_isList = (getElement(_element).size > 1 || getElement(_element).multiple == true) ? true : false;
				_isMultiple = getElement(_element).multiple;
				_fixedForList();
				_updateProp(prop, val);
				break;
			case "disabled":
				getElement(_element)[prop] = val;
				_isDisabled = val;
				_fixedForDisabled();
				break;
			case "selectedIndex":
			case "value":
				getElement(_element)[prop] = val;
				var childid = _getPostID("postChildID");
				$("#" + childid + " li." + _styles_i.li).removeClass(_styles.selected);
				$($("#" + childid + " li." + _styles_i.li)[getElement(_element).selectedIndex]).addClass(_styles.selected);
				_setValue(getElement(_element).selectedIndex);
				break;
			case "length":
				var childid = _getPostID("postChildID");
				if (val < getElement(_element).length) {
					getElement(_element)[prop] = val;
					if (val == 0) {
						$("#" + childid + " li." + _styles_i.li).remove();
						_setValue(-1);
					} else {
						$("#" + childid + " li." + _styles_i.li + ":gt(" + (val - 1) + ")").remove();
						if ($("#" + childid + " li." + _styles.selected).length == 0) {
							$("#" + childid + " li." + _styles.enabled + ":eq(0)").addClass(_styles.selected);
						};
					};
					_updateProp(prop, val);
					_updateProp("children", getElement(_element)["children"]);
				};
				break;
			case "id":
				//please i need this. so preventing to change it. will work on this later
				break;
			default:
				//check if this is not a readonly properties
				try {
					getElement(_element)[prop] = val;
					_updateProp(prop, val);
				} catch (e) {
					//silent
				};
				break;
		}
	};
	this.get = function (prop) {
		return _this[prop] || getElement(_element)[prop]; //return if local else from original
	};
	this.visible = function (val) {
		var id = _getPostID("postID");		
		if (val === true) {
			$("#" + id).show();
		} else if (val === false) {
			$("#" + id).hide();
		} else {
			return ($("#" + id).css("display")=="none") ? false : true;
		};
	};
	this.debug = function (v) {
		msBeautify.debug(v);
	};
	this.close = function () {
		_close();
	};
	this.open = function () {		
		_open();
	};
	this.showRows = function (r) {
		if (typeof r == "undefined" || r == 0) {
			return false;
		};
		_settings.visibleRows = r;
		_childHeight(_childHeight());
	};
	this.visibleRows = this.showRows;
	this.on = function (type, fn) {
		$("#" + _element).on(type, fn);
	};
	this.off = function (type, fn) {
		$("#" + _element).off(type, fn);
	};
	this.addMyEvent = this.on;
	this.getData = function () {
		return _getDataAndUI()
	};
	this.namedItem = function () {
		var opt = getElement(_element).namedItem.apply(getElement(_element), arguments);
		return _getDataAndUIByOption(opt);
	};
	this.item = function () {
		var opt = getElement(_element).item.apply(getElement(_element), arguments);
		return _getDataAndUIByOption(opt);
	};	
	//v 3.2
	this.setIndexByValue = function(val) {
		this.set("value", val);
	};
	this.destroy = function () {
		var hidid = _getPostID("postElementHolder");
		var id = _getPostID("postID");
		$("#" + id + ", #" + id + " *").off();
		$("#" + id).remove();
		$("#" + _element).parent().replaceWith($("#" + _element));		
		$("#" + _element).data("dd", null);
	};
	//Create msDropDown	
	_construct();
};
//bind in jquery
$.fn.extend({
			msDropDown: function(settings)
			{
				return this.each(function()
				{
					if (!$(this).data('dd')){
						var mydropdown = new dd(this, settings);
						$(this).data('dd', mydropdown);
					};
				});
			}
});
$.fn.msDropdown = $.fn.msDropDown; //make a copy
})(jQuery);