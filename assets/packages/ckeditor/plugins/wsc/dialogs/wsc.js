/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */
 (function() {
  // Create support tools
 var appTools = (function(){
 	var inited = {};

 	var _init = function(handler) {
		if (window.addEventListener) {
			window.addEventListener('message', handler, false);
		} else {
			window.attachEvent("onmessage", handler);
		}
	};

	var unbindHandler = function(handler) {
		if (window.removeEventListener) {
			window.removeEventListener('message', handler, false);
		} else {
			window.detachEvent('onmessage', handler);
		}
	};

	var _sendCmd = function(o) {
		var str,
			type = Object.prototype.toString,
			objObject = "[object Object]",
			fn = o.fn || null,
			id = o.id || '',
			target = o.target || window,
			message = o.message || {
				'id': id
			};

		if (o.message && type.call(o.message) == objObject) {
			(o.message.id) ? o.message.id : o.message.id = id;
			message = o.message;
		}

		str = window.JSON.stringify(message, fn);
		target.postMessage(str, '*');
	};

	var _hashCreate = function(o, fn) {
		fn = fn || null;
		var str = window.JSON.stringify(o, fn);
		return str;
	};

	var _hashParse = function(str, fn) {
		fn = fn || null;
		return window.JSON.parse(str, fn);
	};

	var setCookie = function(name, value, options) {
	  options = options || {};

	  var expires = options.expires;

	  if (typeof expires == "number" && expires) {
	    var d = new Date();
	    d.setTime(d.getTime() + expires*1000);
	    expires = options.expires = d;
	  }
	  if (expires && expires.toUTCString) {
	  	options.expires = expires.toUTCString();
	  }

	  value = encodeURIComponent(value);
	  var updatedCookie = name + "=" + value;

	  for(var propName in options) {
	  	var propValue = options[propName];
	    	updatedCookie += "; " + propName;
	    if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	  }
	  document.cookie = updatedCookie;
	};

	var getCookie = function(name) {
	  var matches = document.cookie.match(new RegExp(
	    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	  ));
	  return matches ? decodeURIComponent(matches[1]) : undefined;
	};

	var deleteCookie = function(name) {
	  setCookie(name, "", { expires: -1 });
	};

	var findFocusable = function(ckEl) {
		var result = null,
			focusableSelectors = 'a[href], area[href], input, select, textarea, button, *[tabindex], *[contenteditable]';

		if(ckEl) {
			result = ckEl.find(focusableSelectors);
		}

		return result;
	};

	var getStyle = function(el, prop) {
		if(document.defaultView && document.defaultView.getComputedStyle) {
			return document.defaultView.getComputedStyle(el, null)[prop];
		} else if(el.currentStyle) {
			return el.currentStyle[prop];
		} else {
			return el.style[prop];
		}
	};

	var isHidden = function(el) {
		return el.offsetWidth === 0 || el.offsetHeight == 0 || getStyle(el, 'display') === 'none';
	};

	var isVisible = function(el) {
		return !isHidden(el);
	};

	var hasClass = function (obj, cname) {
		return !!(obj.className ? obj.className.match(new RegExp('(\\s|^)'+cname+'(\\s|$)')) : false);
	};

	return {
		postMessage: {
			init: _init,
			send: _sendCmd,
			unbindHandler: unbindHandler
		},
		hash: {
			create: function() {

			},

			parse: function() {

			}
		},
		cookie: {
			set: setCookie,
			get: getCookie,
			remove: deleteCookie
		},
		misc: {
			findFocusable: findFocusable,
			isVisible: isVisible,
			hasClass: hasClass
		}
	};
 })();

	var NS = NS || {};
		NS.TextAreaNumber = null;
		NS.load = true;
		NS.cmd = {
			"SpellTab": 'spell',
			"Thesaurus": 'thes',
			"GrammTab": 'grammar'
		};
		NS.dialog = null;
		NS.optionNode = null;
		NS.selectNode = null;
		NS.grammerSuggest = null;
		NS.textNode = {};
		NS.iframeMain = null;
		NS.dataTemp = '';
		NS.div_overlay = null;
		NS.textNodeInfo = {};
		NS.selectNode = {};
		NS.selectNodeResponce = {};
		NS.langList = null;
		NS.langSelectbox = null;
		NS.banner = '';
		NS.show_grammar = null;
		NS.div_overlay_no_check = null;
		NS.targetFromFrame = {};
		NS.onLoadOverlay = null;
		NS.LocalizationComing = {};
		NS.OverlayPlace = null;
		NS.sessionid = '';
		NS.LocalizationButton = {
			'ChangeTo_button': {
				'instance' : null,
				'text' : 'Change to',
				'localizationID': 'ChangeTo'
			},

			'ChangeAll': {
				'instance' : null,
				'text' : 'Change All'
			},

			'IgnoreWord': {
				'instance' : null,
				'text' : 'Ignore word'
			},

			'IgnoreAllWords': {
				'instance' : null,
				'text' : 'Ignore all words'
			},

			'Options': {
				'instance' : null,
				'text' : 'Options',
				'optionsDialog': {
					'instance' : null
				}
			},

			'AddWord': {
				'instance' : null,
				'text' : 'Add word'
			},

			'FinishChecking_button': {
				'instance' : null,
				'text' : 'Finish Checking',
				'localizationID': 'FinishChecking'
			},

			'Option_button': {
				'instance' : null,
				'text' : 'Options',
				'localizationID': 'Options'
			},

			'FinishChecking_button_block': {
				'instance' : null,
				'text' : 'Finish Checking',
				'localizationID': 'FinishChecking'
			}
		};

		NS.LocalizationLabel = {
			'ChangeTo_label': {
				'instance' : null,
				'text' : 'Change to',
				'localizationID': 'ChangeTo'
			},

			'Suggestions': {
				'instance' : null,
				'text' : 'Suggestions'
			},

			'Categories': {
				'instance' : null,
				'text' : 'Categories'
			},

			'Synonyms': {
				'instance' : null,
				'text' : 'Synonyms'
			}
		};

	var SetLocalizationButton = function(obj) {
		var el, localizationID;

		for(var i in obj) {
			el = NS.dialog.getContentElement(NS.dialog._.currentTabId, i);

			if(el) {
				el = el.getElement();
			} else if(obj[i].instance){
				el = obj[i].instance.getElement().getFirst() || obj[i].instance.getElement();
			} else{
				continue;
			}

			localizationID = obj[i].localizationID || i;
			el.setText(NS.LocalizationComing[localizationID]);
		}
	};

	var SetLocalizationLabel = function(obj) {
		var el, localizationID;

		for(var i in obj) {
			el = NS.dialog.getContentElement(NS.dialog._.currentTabId, i);

			if(!el) {
				el = obj[i].instance;
			}

			if(el.setLabel) {
				localizationID = obj[i].localizationID || i;
				el.setLabel(NS.LocalizationComing[localizationID] + ':');
			}
		}
	};
	var OptionsConfirm = function(state) {
		if (state) {
			nameNode.setValue('');
		}
	};

	var iframeOnload = false;
	var nameNode, selectNode, frameId;

	NS.framesetHtml = function(tab) {
		var str = '<iframe id=' + NS.iframeNumber + '_' + tab + ' frameborder="0" allowtransparency="1" style="width:100%;border: 1px solid #AEB3B9;overflow: auto;background:#fff; border-radius: 3px;"></iframe>';
		return str;
	};

	NS.setIframe = function(that, nameTab) {
		var iframe,
			str = NS.framesetHtml(nameTab),
			iframeId = NS.iframeNumber + '_' + nameTab,
			// tmp.html from wsc/dialogs
			iframeInnerHtml =
				'<!DOCTYPE html>' +
				'<html>' +
					'<head>' +
						'<meta charset="UTF-8">' +
						'<title>iframe</title>' +

						'<style>' +
							'html,body{' +
								'margin: 0;' +
								'height: 100%;' +
								'font: 13px/1.555 "Trebuchet MS", sans-serif;' +
							'}' +
							'a{' +
							    'color: #888;' +
							    'font-weight: bold;' +
							    'text-decoration: none;' +
							    'border-bottom: 1px solid #888;' +
							'}' +
							'.main-box {' +
								'color:#252525;' +
								'padding: 3px 5px;' +
								'text-align: justify;' +
							'}' +
							'.main-box p{margin: 0 0 14px;}' +
							'.main-box .cerr{' +
							    'color: #f00000;' +
							    'border-bottom-color: #f00000;' +
							'}' +
						'</style>' +
					'</head>' +
					'<body>' +
						'<div id="content" class="main-box"></div>' +
						'<iframe src="" frameborder="0" id="spelltext" name="spelltext" style="display:none; width: 100%" ></iframe>' +
						'<iframe src="" frameborder="0" id="loadsuggestfirst" name="loadsuggestfirst" style="display:none; width: 100%" ></iframe>' +
						'<iframe src="" frameborder="0" id="loadspellsuggestall" name="loadspellsuggestall" style="display:none; width: 100%" ></iframe>' +
						'<iframe src="" frameborder="0" id="loadOptionsForm" name="loadOptionsForm" style="display:none; width: 100%" ></iframe>' +
						'<script>' +
							'(function(window) {' +
								// Constructor Manager PostMessage

								'var ManagerPostMessage = function() {' +
									'var _init = function(handler) {' +
										'if (document.addEventListener) {' +
											'window.addEventListener("message", handler, false);' +
										'} else {' +
											'window.attachEvent("onmessage", handler);' +
										'};' +
									'};' +
									'var _sendCmd = function(o) {' +
										'var str,' +
											'type = Object.prototype.toString,' +
											'fn = o.fn || null,' +
											'id = o.id || "",' +
											'target = o.target || window,' +
											'message = o.message || { "id": id };' +

										'if (o.message && type.call(o.message) == "[object Object]") {' +
											'(o.message["id"]) ? o.message["id"] : o.message["id"] = id;' +
											'message = o.message;' +
										'};' +

										'str = JSON.stringify(message, fn);' +
										'target.postMessage(str, "*");' +
									'};' +

									'return {' +
										'init: _init,' +
										'send: _sendCmd' +
									'};' +
								'};' +

								'var manageMessageTmp = new ManagerPostMessage;' +


								'var appString = (function(){' +
									'var spell = parent.CKEDITOR.config.wsc.DefaultParams.scriptPath;' +
									'var serverUrl = parent.CKEDITOR.config.wsc.DefaultParams.serviceHost;' +
									'return serverUrl + spell;' +
								'})();' +

								'function loadScript(src, callback) {' +
								    'var scriptTag = document.createElement("script");' +
								   		'scriptTag.type = "text/javascript";' +
								   	'callback ? callback : callback = function() {};' +
								    'if(scriptTag.readyState) {' +
								        //IE
								        'scriptTag.onreadystatechange = function() {' +
								            'if (scriptTag.readyState == "loaded" ||' +
								            'scriptTag.readyState == "complete") {' +
								                'scriptTag.onreadystatechange = null;' +
								                'setTimeout(function(){scriptTag.parentNode.removeChild(scriptTag)},1);' +
								                'callback();' +
								            '}' +
								        '};' +
								    '}else{' +
								        //Others
								        'scriptTag.onload = function() {' +
								           'setTimeout(function(){scriptTag.parentNode.removeChild(scriptTag)},1);' +
								           'callback();' +
								        '};' +
								    '};' +
								    'scriptTag.src = src;' +
								    'document.getElementsByTagName("head")[0].appendChild(scriptTag);' +
								'};' +


								'window.onload = function(){' +
									 'loadScript(appString, function(){' +
										'manageMessageTmp.send({' +
											'"id": "iframeOnload",' +
											'"target": window.parent' +
										'});' +
									'});' +
								'}' +
							'})(this);' +
						'</script>' +
					'</body>' +
				'</html>';

		that.getElement().setHtml(str);
		iframe = document.getElementById(iframeId);
		iframe = (iframe.contentWindow) ? iframe.contentWindow : (iframe.contentDocument.document) ? iframe.contentDocument.document : iframe.contentDocument;
		iframe.document.open();
		iframe.document.write(iframeInnerHtml);
		iframe.document.close();
		NS.div_overlay.setEnable();
		iframeOnload = true;
	};

	NS.setCurrentIframe = function(currentTab) {
		var that = NS.dialog._.contents[currentTab].Content,
			tabID, iframe;

		NS.setIframe(that, currentTab);

	};

	NS.setHeightBannerFrame = function() {
		var height = "90px",
			bannerPlaceSpellTab = NS.dialog.getContentElement('SpellTab', 'banner').getElement(),
			bannerPlaceGrammTab = NS.dialog.getContentElement('GrammTab', 'banner').getElement(),
			bannerPlaceThesaurus = NS.dialog.getContentElement('Thesaurus', 'banner').getElement();

		bannerPlaceSpellTab.setStyle('height', height);
		bannerPlaceGrammTab.setStyle('height', height);
		bannerPlaceThesaurus.setStyle('height', height);
	};

	NS.setHeightFrame = function() {
		var currentTab = NS.dialog._.currentTabId,
			tabID = NS.iframeNumber + '_' + currentTab,
			iframe = document.getElementById(tabID);

		iframe.style.height = '240px';
	};

	NS.sendData = function(scope) {
		var currentTab = scope._.currentTabId,
			that = scope._.contents[currentTab].Content,
			tabID, iframe;

		NS.previousTab = currentTab;
		NS.setIframe(that, currentTab);

		var loadNewTab = function(event) {
			currentTab = scope._.currentTabId;
			event = event || window.event;

			if (!event.data.getTarget().is('a')) {
				return;
			}

			if(currentTab === NS.previousTab) return;
			NS.previousTab = currentTab;

			that = scope._.contents[currentTab].Content;
			tabID = NS.iframeNumber + '_' + currentTab;
			NS.div_overlay.setEnable();

			if (!that.getElement().getChildCount()) {
				NS.setIframe(that, currentTab);
				iframe = document.getElementById(tabID);
				NS.targetFromFrame[tabID] = iframe.contentWindow;
			} else {
				sendData(NS.targetFromFrame[tabID], NS.cmd[currentTab]);
			}
		};

		scope.parts.tabs.removeListener('click', loadNewTab);
		scope.parts.tabs.on('click', loadNewTab);
	};

	NS.buildSelectLang = function(aId) {
		var divContainer = new CKEDITOR.dom.element('div'),
			selectContainer = new CKEDITOR.dom.element('select'),
			id = "wscLang" + aId;

		divContainer.addClass("cke_dialog_ui_input_select");
		divContainer.setAttribute("role", "presentation");
		divContainer.setStyles({
			'height': 'auto',
			'position': 'absolute',
			'right': '0',
			'top': '-1px',
			'width': '160px',
			'white-space': 'normal'
		});

		selectContainer.setAttribute('id', id);
		selectContainer.addClass("cke_dialog_ui_input_select");
		selectContainer.setStyles({
			'width': '160px'
		});
		var currentTabId = NS.dialog._.currentTabId,
				frameId = NS.iframeNumber + '_' + currentTabId;

		divContainer.append(selectContainer);

		return  divContainer;

	};

	NS.buildOptionLang = function(key, aId) {
		var id = "wscLang" + aId;
		var select = document.getElementById(id),
			fragment = document.createDocumentFragment(),
			create_option, txt_option,
			sort = [];

		if(select.options.length === 0) {
			for (var lang in key) {
				sort.push([lang, key[lang]]);
			}
			sort.sort();

			for (var i = 0; i < sort.length; i++) {
				create_option=document.createElement("option");
				create_option.setAttribute("value", sort[i][1]);
				txt_option = document.createTextNode(sort[i][0]);
				create_option.appendChild(txt_option);

				fragment.appendChild(create_option);
			}
			select.appendChild(fragment);
		}

		// make appropriate option selected according to current selected language
		for (var j = 0; j < select.options.length; j++) {
			if (select.options[j].value == NS.selectingLang) {
				select.options[j].selected = "selected";
			}
		}
	};

	NS.buildOptionSynonyms = function(key) {
		var syn = NS.selectNodeResponce[key];

		var select = getSelect( NS.selectNode['Synonyms'] );

		NS.selectNode['Synonyms'].clear();

		for (var i = 0; i < syn.length; i++) {
			var option = document.createElement('option');
				option.text = syn[i];
				option.value = syn[i];

			select.$.add(option, i);
		}

		NS.selectNode['Synonyms'].getInputElement().$.firstChild.selected = true;
		NS.textNode['Thesaurus'].setValue(NS.selectNode['Synonyms'].getInputElement().getValue());
	};

	var setBannerInPlace = function(htmlBanner) {
		var findBannerPlace = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'banner').getElement();
		findBannerPlace.setHtml(htmlBanner);

	};

	var overlayBlock = function overlayBlock(opt) {
		var progress = opt.progress || "",
			doc = document,
			target = opt.target || doc.body,
			overlayId = opt.id || "overlayBlock",
			opacity = opt.opacity || "0.9",
			background = opt.background || "#f1f1f1",
			getOverlay = doc.getElementById(overlayId),
			thisOverlay = getOverlay || doc.createElement("div");

		thisOverlay.style.cssText = "position: absolute;" +
			"top:30px;" +
			"bottom:41px;" +
			"left:1px;" +
			"right:1px;" +
			"z-index: 10020;" +
			"padding:0;" +
			"margin:0;" +
			"background:" + background + ";" +
			"opacity: " + opacity + ";" +
			"filter: alpha(opacity=" + opacity * 100 + ");" +
			"display: none;";
		thisOverlay.id = overlayId;

		if (!getOverlay) {
		target.appendChild(thisOverlay);
		}

		return {
			setDisable: function() {
				thisOverlay.style.display = "none";
			},
			setEnable: function() {
				thisOverlay.style.display = "block";
			}
		};
	};

	var buildRadioInputs = function(key, value, check) {
		var divContainer = new CKEDITOR.dom.element('div'),
			radioButton = new CKEDITOR.dom.element('input'),
			radioLabel = new CKEDITOR.dom.element('label'),
			id = "wscGrammerSuggest" + key + "_" + value;

		divContainer.addClass("cke_dialog_ui_input_radio");
		divContainer.setAttribute("role", "presentation");
		divContainer.setStyles({
			width: "97%",
			padding: "5px",
			'white-space': 'normal'
		});

		radioButton.setAttributes({
			type: "radio",
			value: value,
			name: 'wscGrammerSuggest',
			id: id
		});
		radioButton.setStyles({
			"float":"left"
		});

		radioButton.on("click", function(data) {
			NS.textNode['GrammTab'].setValue(data.sender.getValue());
		});

		(check) ? radioButton.setAttribute("checked", true) : false;

		radioButton.addClass("cke_dialog_ui_radio_input");

		radioLabel.appendText(key);
		radioLabel.setAttribute("for", id);
		radioLabel.setStyles({
			'display': "block",
			'line-height': '16px',
			'margin-left': '18px',
			'white-space': 'normal'
		});

		divContainer.append(radioButton);
		divContainer.append(radioLabel);

		return divContainer;
	};

	var statusGrammarTab = function(aState) {  //#19221
		aState = aState || 'true';
		if(aState !== null && aState == 'false'){
			hideGrammTab();
		}
	};

	var langConstructor = function(lang) {
		var langSelectBox = new __constructLangSelectbox(lang),
			selectId = "wscLang" + NS.dialog.getParentEditor().name,
			selectContainer = document.getElementById(selectId),
			currentTabId = NS.dialog._.currentTabId,
			langGroup,
			frameId = NS.iframeNumber + '_' + currentTabId;

		NS.buildOptionLang(langSelectBox.setLangList, NS.dialog.getParentEditor().name);

		langGroup = langSelectBox.getCurrentLangGroup(NS.selectingLang);
		if(langGroup) {
			tabView[langGroup].onShow();
		}
		statusGrammarTab(NS.show_grammar);

		selectContainer.onchange = function(e) {
			var langGroup = langSelectBox.getCurrentLangGroup(this.value),
				currentTabId = NS.dialog._.currentTabId,
				cmd;

			e = e || window.event;

			tabView[langGroup].onShow();
			statusGrammarTab(NS.show_grammar);
			NS.div_overlay.setEnable();
			NS.selectingLang = this.value;

			// get command for current opened tan
			cmd = NS.cmd[currentTabId];
			// check whether current tab can be opened after language switching
			if(!langGroup || !tabView[langGroup] || !tabView[langGroup].allowedTabCommands[cmd]) {
				// if not so - set default tab to open after reload
				cmd = tabView[langGroup].defaultTabCommand;
			}

			for(var key in NS.cmd) {
				if(NS.cmd[key] == cmd) {
					NS.previousTab = key;
					break;
				}
			}

			appTools.postMessage.send({
			 	'message': {
			 		'changeLang': NS.selectingLang,
			 		'interfaceLang' : NS.interfaceLang,
			 		'text': NS.dataTemp,
			 		'cmd': cmd
			 	},
				'target': NS.targetFromFrame[frameId],
				'id': 'selectionLang_outer__page'
			});
		};

	};

	var disableButtonSuggest = function(word) {
		var changeToButton, changeAllButton,
			styleDisable = function(instanceButton) {
				var button = NS.dialog.getContentElement(NS.dialog._.currentTabId, instanceButton) || NS.LocalizationButton[instanceButton].instance;
				button.getElement().hasClass('cke_disabled') ? button.getElement().setStyle('color', '#a0a0a0') : button.disable();
			},
			styleEnable = function(instanceButton) {
				var button = NS.dialog.getContentElement(NS.dialog._.currentTabId, instanceButton) || NS.LocalizationButton[instanceButton].instance;
				button.enable();
				button.getElement().setStyle('color', '#333');
			};

		if (word == 'no_any_suggestions') {
			word = 'No suggestions';

			changeToButton = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'ChangeTo_button') || NS.LocalizationButton['ChangeTo_button'].instance;
			changeToButton.disable();
			changeAllButton = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'ChangeAll') || NS.LocalizationButton['ChangeAll'].instance;
			changeAllButton.disable();

			styleDisable('ChangeTo_button');
			styleDisable('ChangeAll');

			return word;
		} else {
			styleEnable('ChangeTo_button');
			styleEnable('ChangeAll');

			return word;
		}
	};

	function getSelect( obj ) {
		if ( obj && obj.domId && obj.getInputElement().$ )
		return obj.getInputElement();
		else if ( obj && obj.$ )
			return obj;
		return false;
	}

	var handlerId = {
		iframeOnload: function(response) {
			var currentTab = NS.dialog._.currentTabId,
				tabId = NS.iframeNumber + '_' + currentTab;
			sendData(NS.targetFromFrame[tabId], NS.cmd[currentTab]);
		},

		suggestlist: function(response) {
			delete response.id;
			NS.div_overlay_no_check.setDisable();
			hideCurrentFinishChecking();
			langConstructor(NS.langList);

			var word =  disableButtonSuggest(response.word),
				suggestionsList = '';

			if (word instanceof Array) {
				word = response.word[0];
			}

			word = word.split(',');
			suggestionsList = word;

			NS.textNode['SpellTab'].setValue(suggestionsList[0]);

			var select = getSelect( selectNode );

			selectNode.clear();

			for (var i = 0; i < suggestionsList.length; i++) {
				var option = document.createElement('option');
					option.text = suggestionsList[i];
					option.value = suggestionsList[i];

				select.$.add(option, i);
			}

			showCurrentTabs();
			NS.div_overlay.setDisable();

		},

		grammerSuggest: function(response) {
			delete response.id;
			delete response.mocklangs;

			hideCurrentFinishChecking();
			langConstructor(NS.langList);	// Show select language for this command CKEDITOR.config.wsc_cmd
			var firstSuggestValue = response.grammSuggest[0];// ? firstSuggestValue = response.grammSuggest[0] : firstSuggestValue = 'No suggestion for this words';
			NS.grammerSuggest.getElement().setHtml('');

			NS.textNode['GrammTab'].reset();
			NS.textNode['GrammTab'].setValue(firstSuggestValue);

			NS.textNodeInfo['GrammTab'].getElement().setHtml('');
			NS.textNodeInfo['GrammTab'].getElement().setText(response.info);

			var arr = response.grammSuggest,
				len = arr.length,
				check = true;

				for (var i = 0; i < len; i++) {
					NS.grammerSuggest.getElement().append(buildRadioInputs(arr[i], arr[i], check));
					check = false;
				}

			showCurrentTabs();
			NS.div_overlay.setDisable();
		},

		thesaurusSuggest: function(response) {
			delete response.id;
			delete response.mocklangs;

			hideCurrentFinishChecking();
			langConstructor(NS.langList);	// Show select language for this command CKEDITOR.config.wsc_cmd
			NS.selectNodeResponce = response;

			NS.textNode['Thesaurus'].reset();

			var select = getSelect( NS.selectNode['Categories'] ),
				count = 0;

			NS.selectNode['Categories'].clear();

			for (var i in response) {

				var option = document.createElement('option');
					option.text = i;
					option.value = i;

				select.$.add(option, count);
				count++
			}

			var synKey = NS.selectNode['Categories'].getInputElement().getChildren().$[0].value;
			NS.selectNode['Categories'].getInputElement().getChildren().$[0].selected = true;
			NS.buildOptionSynonyms(synKey);

			showCurrentTabs();
			NS.div_overlay.setDisable();
			count = 0;
		},
		finish: function(response) {
			delete response.id;

			hideCurrentTabs();
			showCurrentFinishChecking();
			NS.div_overlay.setDisable();
		},
		settext: function(response) {
			delete response.id;

			var command = NS.dialog.getParentEditor().getCommand( 'checkspell' ),
				editor = NS.dialog.getParentEditor();

			//set local storage for synchronization before scayt reinit
			if (editor.scayt && editor.wsc.isSsrvSame) {
				var	wscUDN = editor.wsc.udn;

					if (!wscUDN) {
						editor.wsc.DataStorage.setData('scayt_user_dictionary_name', '');
					} else {
						editor.wsc.DataStorage.setData('scayt_user_dictionary_name', wscUDN);
					}
			}


			try {
				editor.focus();
			} catch(e) {}

			editor.setData(response.text, function(){
				NS.dataTemp = '';
				editor.unlockSelection();
				editor.fire('saveSnapshot');
				NS.dialog.hide();
			});

		},
		ReplaceText: function(response) {

			delete response.id;
			NS.div_overlay.setEnable();

			NS.dataTemp = response.text;
			NS.selectingLang = response.currentLang;

			if (response.cmd = 'spell' && response.len !== '0' && response.len) {
				NS.div_overlay.setDisable();
			} else {
				window.setTimeout(function() {
					try {
						NS.div_overlay.setDisable();
					} catch(e) {}
				}, 500);
			}

			SetLocalizationButton(NS.LocalizationButton);
			SetLocalizationLabel(NS.LocalizationLabel);

		},
		options_checkbox_send: function(response) {
			delete response.id;

			var obj = {
				'osp': appTools.cookie.get('osp'),
				'udn': appTools.cookie.get('udn'),
				'cust_dic_ids': NS.cust_dic_ids
			};

			var currentTabId = NS.dialog._.currentTabId,
				frameId = NS.iframeNumber + '_' + currentTabId;

			appTools.postMessage.send({
				'message': obj,
				'target': NS.targetFromFrame[frameId],
				'id': 'options_outer__page'
			});
		},

		getOptions: function(response) {
			var udn = response.DefOptions.udn;
			NS.LocalizationComing = response.DefOptions.localizationButtonsAndText;
			NS.show_grammar = response.show_grammar;
			NS.langList = response.lang;
			NS.bnr = response.bannerId;
			NS.sessionid = response.sessionid;
			if (response.bannerId) {
				NS.setHeightBannerFrame();
				setBannerInPlace(response.banner);
			} else {
				NS.setHeightFrame();
			}

			if (udn == 'undefined') {
				if (NS.userDictionaryName) {
					udn = NS.userDictionaryName;

					var obj = {
						'osp': appTools.cookie.get('osp'),
						'udn': NS.userDictionaryName,
						'cust_dic_ids': NS.cust_dic_ids,
						'id': 'options_dic_send',
						'udnCmd': 'create'
					};

					appTools.postMessage.send({
						'message': obj,
						'target': NS.targetFromFrame[frameId]
					});

				} else{
					udn = '';
				}
			}

			appTools.cookie.set('osp', response.DefOptions.osp);
			appTools.cookie.set('udn', udn);
			appTools.cookie.set('cust_dic_ids', response.DefOptions.cust_dic_ids);

			appTools.postMessage.send({
				'id': 'giveOptions'
			});
		},

		options_dic_send: function(response) {

			var obj = {
				'osp': appTools.cookie.get('osp'),
				'udn': appTools.cookie.get('udn'),
				'cust_dic_ids': NS.cust_dic_ids,
				'id': 'options_dic_send',
				'udnCmd': appTools.cookie.get('udnCmd')
			};

			var currentTabId = NS.dialog._.currentTabId,
				frameId = NS.iframeNumber + '_' + currentTabId;

			appTools.postMessage.send({
				'message': obj,
				'target': NS.targetFromFrame[frameId]
			});
		},
		data: function(response) {
			delete response.id;
		},

		giveOptions: function() {

		},

		setOptionsConfirmF:function() {
			 OptionsConfirm(false);
		},

		setOptionsConfirmT:function() {
			OptionsConfirm(true);
		},

		clickBusy: function() {
			NS.div_overlay.setEnable();
		},

		suggestAllCame: function() {
			NS.div_overlay.setDisable();
			NS.div_overlay_no_check.setDisable();
		},

		TextCorrect: function() {
			langConstructor(NS.langList);
		}

	};

	var handlerIncomingData = function(event) {
		event = event || window.event;
		var response = window.JSON.parse(event.data);

		if(response && response.id) {
			handlerId[response.id](response);
		}
	};

	var handlerButtonOptions = function(event) {
		event = event || window.event;

		var currentTabId = NS.dialog._.currentTabId,
			frameId = NS.iframeNumber + '_' + currentTabId;

		appTools.postMessage.send({
			'message': {
				'cmd': 'Options'
			},
			'target': NS.targetFromFrame[frameId],
			'id': 'cmd'
		});

	};

	var sendData = function(frameTarget, cmd, sendText, reset_suggest) {
		cmd = cmd || CKEDITOR.config.wsc_cmd;
		reset_suggest = reset_suggest || false;
		sendText = sendText || NS.dataTemp;
		appTools.postMessage.send({
			'message': {
				'customerId': NS.wsc_customerId,
				'text': sendText,
				'txt_ctrl': NS.TextAreaNumber,
				'cmd': cmd,
				'cust_dic_ids': NS.cust_dic_ids,
				'udn': NS.userDictionaryName,
				'slang': NS.selectingLang,
				'interfaceLang' : NS.interfaceLang,
				'reset_suggest': reset_suggest,
				'sessionid': NS.sessionid
			},
			'target': frameTarget,
			'id': 'data_outer__page'
		});

		NS.div_overlay.setEnable();
	};

	var tabView = {
		"superset": {
			onShow: function() {
				showThesaurusTab();
				showGrammTab();
				showSpellTab();
			},
			allowedTabCommands: {
				"spell": true,
				"grammar": true,
				"thes": true
			},
			defaultTabCommand: "spell"
		},
		"usual": {
			onShow: function() {
				hideThesaurusTab();
				hideGrammTab();
				showSpellTab();
			},
			allowedTabCommands: {
				"spell": true
			},
			defaultTabCommand: "spell"
		},
		"rtl": {
			onShow: function() {
				hideThesaurusTab();
				hideGrammTab();
				showSpellTab();
			},
			allowedTabCommands: {
				"spell": true
			},
			defaultTabCommand: "spell"
		},
		"spellgrammar": {
			onShow: function() {
				hideThesaurusTab();
				showGrammTab();
				showSpellTab();
			},
			allowedTabCommands: {
				"spell": true,
				"grammar": true
			},
			defaultTabCommand: "spell"
		},
		"spellthes": {
			onShow: function() {
				showThesaurusTab();
				hideGrammTab();
				showSpellTab();
			},
			allowedTabCommands: {
				"spell": true,
				"thes": true
			},
			defaultTabCommand: "spell"
		}
	};

	var showFirstTab = function(scope) {
		var cmdManger = function(cmdView) {
			var obj = {};
			var _getCmd = function(cmd) {
				for (var tabId in cmdView) {
					obj[cmdView[tabId]] = tabId;
				}
			return obj[cmd];
			};
			return {
				getCmdByTab: _getCmd
			};
		};

		var cmdM = new cmdManger(NS.cmd),
			tabToOpen = cmdM.getCmdByTab(CKEDITOR.config.wsc_cmd);

		showCurrentTabs();
		scope.selectPage(tabToOpen);
		NS.sendData(scope);
	};

	var showThesaurusTab = function() {
		NS.dialog.showPage('Thesaurus');
	};

	var hideThesaurusTab = function() {
		NS.dialog.hidePage('Thesaurus');
	};

	var showGrammTab = function() {
		NS.dialog.showPage('GrammTab');
	};

	var hideGrammTab = function() {
		NS.dialog.hidePage('GrammTab');
	};

	var showSpellTab = function() {
		NS.dialog.showPage('SpellTab');
	};

	var hideSpellTab = function() {
		NS.dialog.hidePage('SpellTab');
	};

	var showCurrentTabs = function() {
		var target = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'bottomGroup').getElement();

		target.removeStyle('display');
		target.removeStyle('position');
		target.removeStyle('left');

		target.show();
	};

	var hideCurrentTabs = function() {
		var target = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'bottomGroup').getElement(),
			activeElement = document.activeElement,
			focusableElements;

		target.setStyles({
			display: 'block',
			position: 'absolute',
			left: '-9999px'
		});

		setTimeout(function() {
			target.removeStyle('display');
			target.removeStyle('position');
			target.removeStyle('left');

			target.hide();

			NS.dialog._.editor.focusManager.currentActive.focusNext();

			focusableElements = appTools.misc.findFocusable(NS.dialog.parts.contents);
			if(!appTools.misc.hasClass(activeElement, 'cke_dialog_tab') && !appTools.misc.hasClass(activeElement, 'cke_dialog_contents_body') && appTools.misc.isVisible(activeElement)) {
				try {
					activeElement.focus();
				} catch(e) {}
			} else {
				for(var i = 0, tmpCkEl; i < focusableElements.count(); i++) {
					tmpCkEl = focusableElements.getItem(i);
					if(appTools.misc.isVisible(tmpCkEl.$)) {
						try {
							tmpCkEl.$.focus();
						} catch(e) {}

						break;
					}
				}
			}
		}, 0);
	};

	var showCurrentFinishChecking = function() {
		var target = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'BlockFinishChecking').getElement();

		target.removeStyle('display');
		target.removeStyle('position');
		target.removeStyle('left');

		target.show();
	};

	var hideCurrentFinishChecking = function() {
		var target = NS.dialog.getContentElement(NS.dialog._.currentTabId, 'BlockFinishChecking').getElement(),
			activeElement = document.activeElement,
			focusableElements;

		target.setStyles({
			display: 'block',
			position: 'absolute',
			left: '-9999px'
		});

		setTimeout(function() {
			target.removeStyle('display');
			target.removeStyle('position');
			target.removeStyle('left');

			target.hide();

			NS.dialog._.editor.focusManager.currentActive.focusNext();

			focusableElements = appTools.misc.findFocusable(NS.dialog.parts.contents);
			if(!appTools.misc.hasClass(activeElement, 'cke_dialog_tab') && !appTools.misc.hasClass(activeElement, 'cke_dialog_contents_body') && appTools.misc.isVisible(activeElement)) {
				try {
					activeElement.focus();
				} catch(e) {}
			} else {
				for(var i = 0, tmpCkEl; i < focusableElements.count(); i++) {
					tmpCkEl = focusableElements.getItem(i);
					if(appTools.misc.isVisible(tmpCkEl.$)) {
						try {
							tmpCkEl.$.focus();
						} catch(e) {}

						break;
					}
				}
			}
		}, 0);
	};



function __constructLangSelectbox(languageGroup) {
	if( !languageGroup ) { throw "Languages-by-groups list are required for construct selectbox"; }

	var that = this,
		o_arr = [],
		priorLang ="en_US",
		priorLangTitle = "",
		currLang = NS.selectingLang;

	for ( var group in languageGroup){
		for ( var langCode in languageGroup[group]){
			var langName = languageGroup[group][langCode];
			if ( langName == priorLang ) {
				priorLangTitle = langName;
			} else {
				o_arr.push( langName );
			}
		}
	}

	o_arr.sort();
	if(priorLangTitle) {
		o_arr.unshift( priorLangTitle );
	}

	var searchGroup = function ( code ){
		for ( var group in languageGroup){
			for ( var langCode in languageGroup[group]){
				if ( langCode.toUpperCase() === code.toUpperCase() ) {
					return group;
				}
			}
		}
		return "";
	};

	var _setLangList = function() {
		var langList = {},
			langArray = [];
		for (var group in languageGroup) {
			for ( var langCode in languageGroup[group]){
				langList[languageGroup[group][langCode]] = langCode;
			}
		}
		return langList;
	};

	var _return = {
		getCurrentLangGroup: function(code) {
			return searchGroup(code);
		},
		setLangList: _setLangList()
	};

	return _return;
}

CKEDITOR.dialog.add('checkspell', function(editor) {
	var handlerButtons = function(event) {
			event = event || window.event;

			// because in chrome and safary document.activeElement returns <body> tag. We need to signal that clicked element is active
			this.getElement().focus();

			NS.div_overlay.setEnable();
			var currentTabId = NS.dialog._.currentTabId,
				frameId = NS.iframeNumber + '_' + currentTabId,
				new_word = NS.textNode[currentTabId].getValue(),
				cmd = this.getElement().getAttribute("title-cmd");

			appTools.postMessage.send({
				'message': {
					'cmd': cmd,
					'tabId': currentTabId,
					'new_word': new_word
				},
				'target': NS.targetFromFrame[frameId],
				'id': 'cmd_outer__page'
			});

			if (cmd == 'ChangeTo' || cmd == 'ChangeAll') {
				editor.fire('saveSnapshot');
			}

			if (cmd == 'FinishChecking') {
				editor.config.wsc_onFinish.call(CKEDITOR.document.getWindow().getFrame());
			}

		},
		constraints = {
			minWidth: 560,
			minHeight: 444
		};

	function initView(dialog) {
		var newViewSettings = {
				left: parseInt(editor.config.wsc_left, 10),
				top: parseInt(editor.config.wsc_top, 10),
				width: parseInt(editor.config.wsc_width, 10),
				height: parseInt(editor.config.wsc_height, 10)
			},
			viewSize = CKEDITOR.document.getWindow().getViewPaneSize(),
			currentPosition = dialog.getPosition(),
			currentSize = dialog.getSize(),
			savePosition = 0;

		if(!dialog._.resized) {
			var wrapperHeight = currentSize.height - dialog.parts.contents.getSize('height',  !(CKEDITOR.env.gecko || CKEDITOR.env.opera || CKEDITOR.env.ie && CKEDITOR.env.quirks)),
				wrapperWidth = currentSize.width - dialog.parts.contents.getSize('width', 1);

			if(newViewSettings.width < constraints.minWidth || isNaN(newViewSettings.width)) {
				newViewSettings.width = constraints.minWidth;
			}
			if(newViewSettings.width > viewSize.width - wrapperWidth) {
				newViewSettings.width = viewSize.width - wrapperWidth;
			}

			if(newViewSettings.height < constraints.minHeight || isNaN(newViewSettings.height)) {
				newViewSettings.height = constraints.minHeight;
			}
			if(newViewSettings.height > viewSize.height - wrapperHeight) {
				newViewSettings.height = viewSize.height - wrapperHeight;
			}

			currentSize.width = newViewSettings.width + wrapperWidth;
			currentSize.height = newViewSettings.height + wrapperHeight;

			dialog._.fromResizeEvent = false;
			dialog.resize(newViewSettings.width, newViewSettings.height);
			setTimeout(function() {
				dialog._.fromResizeEvent = false;
				CKEDITOR.dialog.fire('resize', {
					dialog: dialog,
					width: newViewSettings.width,
					height: newViewSettings.height
				}, editor);
			}, 300);
		}

		if(!dialog._.moved) {
			savePosition = isNaN(newViewSettings.left) && isNaN(newViewSettings.top) ? 0 : 1;

			if(isNaN(newViewSettings.left)) {
				newViewSettings.left = (viewSize.width - currentSize.width) / 2;
			}
			if(newViewSettings.left < 0) {
				newViewSettings.left = 0;
			}
			if(newViewSettings.left > viewSize.width - currentSize.width) {
				newViewSettings.left = viewSize.width - currentSize.width;
			}

			if(isNaN(newViewSettings.top)) {
				newViewSettings.top = (viewSize.height - currentSize.height) / 2;
			}
			if(newViewSettings.top < 0) {
				newViewSettings.top = 0;
			}
			if(newViewSettings.top > viewSize.height - currentSize.height) {
				newViewSettings.top = viewSize.height - currentSize.height;
			}

			dialog.move(newViewSettings.left, newViewSettings.top, savePosition);
		}
	}

	function createWscObjectForUdAndUdnSyncrhonization() {
		editor.wsc = {};

		//DataStorage object for cookies and localStorage manipulation
		(function( object ) {
			'use strict';

			var DataTypeManager = {
				separator: '<$>',
				getDataType: function(value) {
					var type;

					if(typeof value === 'undefined') {
						type = 'undefined';
					} else if(value === null) {
						type = 'null';
					} else {
						type = Object.prototype.toString.call(value).slice(8, -1);
					}
					return type;
				},
				convertDataToString: function(value) {
					var str,
						type = this.getDataType(value).toLowerCase();

					str = type + this.separator + value;
					return str;
				},
				// get value type and convert value due to type, since all stored values are String
				restoreDataFromString: function(str) {
					var value = str,
						type,
						separatorStartIndex;

					// @TODO: remove this line much later. Support of old format for options
					str = this.backCompatibility(str);

					if(typeof str === 'string') {
						separatorStartIndex = str.indexOf(this.separator);
						type = str.substring(0, separatorStartIndex);
						value = str.substring(separatorStartIndex + this.separator.length);

						switch(type) {
							case 'boolean':
								value = value === 'true';
							break;
							case 'number':
								value = parseFloat(value);
							break;
							// we assume that we will store string values only, due to performance
							case 'array':
								value = value === '' ? [] : value.split(',');
							break;
							case 'null':
								value = null;
							break;
							case 'undefined':
								value = undefined;
							break;
						}
					}
					return value;
				},
				// old data type support
				// here we trying to convert data from old format into new
				// @TODO: remove this function much later
				backCompatibility: function(str) {
					var convertedStr = str,
						value,
						separatorStartIndex;

					if(typeof str === 'string') {
						separatorStartIndex = str.indexOf(this.separator);
						// is it old format?
						if(separatorStartIndex < 0) {
							// try to get number from string
							value = parseFloat(str);
							// is it not a number?
							if(isNaN(value)) {
								// yes, this is not a number. Lets check is this is an array "[comma,separated,values]"
								if((str[0] === '[') && (str[str.length - 1] === ']')) {
									// this is an array. Lets remove brackets symbols and extract the words
									str = str.replace('[', '');
									str = str.replace(']', '');
									if(str === '') {
										value = [];
									} else {
										value = str.split(',');
									}
									// value = str === '[]' ? [] : str.split(',');
								} else if(str === 'true' || str === 'false') {
									// this is boolean value
									value = str === 'true';
								} else {
									// this is string
									value = str;
								}
							}

							convertedStr = this.convertDataToString(value);
						}
					}

					return convertedStr;
				}
			};

			var LocalStorage = {

				get: function( key ) {
					var value = DataTypeManager.restoreDataFromString( window.localStorage.getItem(key) );
					return value;
				},

				set: function( key, value ) {
					var _value = DataTypeManager.convertDataToString( value );
					window.localStorage.setItem( key, _value );
				},

				del: function( key ) {
					window.localStorage.removeItem( key );
				},

				clear: function() {
					window.localStorage.clear();
				}
			};

			var CookiesStorage = {

				expiration: (function() {
					return 60 * 60 * 24 * 366;
				}()),

				get: function(key) {
					var value = DataTypeManager.restoreDataFromString(this.getCookie(key));
					return value;
				},

				set: function(key, value) {
					var _value = DataTypeManager.convertDataToString(value);
					this.setCookie(key, _value, {expires: this.expiration});
				},

				del: function(key) {
					this.deleteCookie(key);
				},

				getCookie: function(name) {
					var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
					return matches ? decodeURIComponent(matches[1]) : undefined;
				},

				setCookie: function(name, value, props) {
					props = props || {};
					var exp = props.expires;

					if (typeof exp === "number" && exp) {
						var d = new Date();

						d.setTime(d.getTime() + exp * 1000);
						exp = props.expires = d;
					}

					if(exp && exp.toUTCString) {
						props.expires = exp.toUTCString();
					}

					value = encodeURIComponent(value);
					var updatedCookie = name + "=" + value;

					for(var propName in props) {
						var propValue = props[propName];

						updatedCookie += "; " + propName;

						if(propValue !== true) {
							updatedCookie += "=" + propValue;
						}
					}

					document.cookie = updatedCookie;
				},

				deleteCookie: function(name) {
					this.setCookie(name, null, {expires: -1});
				},

				// delete all cookies
				clear: function() {
					var cookies = document.cookie.split(";");

					for (var i = 0; i < cookies.length; i++) {
						var cookie = cookies[i];
						var eqPos = cookie.indexOf("=");
						var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;

						this.deleteCookie(name);
					}
				}
			};

			var strategy = window.localStorage ? LocalStorage : CookiesStorage;

			var DataStorage = {
				// Get data within storage for key
				getData: function( key ) {
					return strategy.get( key );
				},

				// Set data within storage
				setData: function( key, value ) {
					strategy.set( key, value );
				},

				// Delete data within storage for key
				deleteData: function( key ) {
					strategy.del( key );
				},

				// Clear storage
				clear: function() {
					strategy.clear();
				}
			};

			// Static Module of Storage Data in the localStorage.
			object.DataStorage = DataStorage;
		}( editor.wsc ));

		editor.wsc.operationWithUDN = function(command, UDName) {
			var obj = {
				'udn': UDName,
				'id': 'operationWithUDN',
				'udnCmd': command
			};
			var currentTabId = NS.dialog._.currentTabId,
				frameId = NS.iframeNumber + '_' + currentTabId;

			appTools.postMessage.send({
				'message': obj,
				'target': NS.targetFromFrame[frameId]
			});
		};
		editor.wsc.getLocalStorageUDN = function() {
			var udn = editor.wsc.DataStorage.getData('scayt_user_dictionary_name');

			if (!udn) {
				return;
			}

			return udn;
		};
		editor.wsc.getLocalStorageUD = function() {
			var ud = editor.wsc.DataStorage.getData('scayt_user_dictionary');

			if (!ud) {
				return;
			}

			return ud;
		};
		editor.wsc.addWords = function(words, callback) {
			var url = editor.config.wsc.DefaultParams.serviceHost + editor.config.wsc.DefaultParams.ssrvHost +
						'?cmd=dictionary&format=json&' +
						'customerid=1%3AncttD3-fIoSf2-huzwE4-Y5muI2-mD0Tt-kG9Wz-UEDFC-tYu243-1Uq474-d9Z2l3&' +
						'action=addword&word='+ words + '&callback=toString&synchronization=true',
				script = document.createElement('script');

			script['type'] = 'text/javascript';
			script['src'] = url;
			document.getElementsByTagName("head")[0].appendChild(script);

			//chrome, firefox, safari
			script.onload = callback;

			//IE
			script.onreadystatechange = function() {
				if (this.readyState === 'loaded') {
					callback();
				}
			};
		};
		editor.wsc.cgiOrigin = function() {
			var wscServiceHostString = editor.config.wsc.DefaultParams.serviceHost,
				wscServiceHostArray = wscServiceHostString.split('/'),
				cgiOrigin = wscServiceHostArray[0] + '//' + wscServiceHostArray[2];

			return cgiOrigin;
		};
		editor.wsc.isSsrvSame = false;
	}

 return {
		title: editor.config.wsc_dialogTitle || editor.lang.wsc.title,
		minWidth: constraints.minWidth,
		minHeight: constraints.minHeight,
		buttons: [CKEDITOR.dialog.cancelButton],
		onLoad: function() {
			NS.dialog = this;
			hideThesaurusTab();
			hideGrammTab();
			showSpellTab();

			//creating wsc object for UD synchronization between wsc and scayt
			if (editor.plugins.scayt) {
				createWscObjectForUdAndUdnSyncrhonization();
			}
		},
		onShow: function() {
			NS.dialog = this;

			editor.lockSelection(editor.getSelection());

			NS.TextAreaNumber = 'cke_textarea_' + editor.name;
			appTools.postMessage.init(handlerIncomingData);
			NS.dataTemp = editor.getData();
			//NS.div_overlay.setDisable();
			NS.OverlayPlace = NS.dialog.parts.tabs.getParent().$;
			if(CKEDITOR && CKEDITOR.config){
				NS.wsc_customerId =  editor.config.wsc_customerId;
				NS.cust_dic_ids = editor.config.wsc_customDictionaryIds;
				NS.userDictionaryName = editor.config.wsc_userDictionaryName;
				NS.defaultLanguage = CKEDITOR.config.defaultLanguage;
				var	protocol = document.location.protocol == "file:" ? "http:" : document.location.protocol;
				var wscCoreUrl = editor.config.wsc_customLoaderScript  || ( protocol + '//www.webspellchecker.net/spellcheck3/script/ssrv.cgi?plugin=fck2&customerid=' + NS.wsc_customerId + '&cmd=script&doc=wsc&schema=22');
			} else {
				NS.dialog.hide();
				return;
			}

			initView(this);
			CKEDITOR.scriptLoader.load(wscCoreUrl, function(success) {
				if(CKEDITOR.config && CKEDITOR.config.wsc && CKEDITOR.config.wsc.DefaultParams){
					NS.serverLocationHash = CKEDITOR.config.wsc.DefaultParams.serviceHost;
					NS.logotype = CKEDITOR.config.wsc.DefaultParams.logoPath;
					NS.loadIcon = CKEDITOR.config.wsc.DefaultParams.iconPath;
					NS.loadIconEmptyEditor = CKEDITOR.config.wsc.DefaultParams.iconPathEmptyEditor;
					NS.LangComparer = new CKEDITOR.config.wsc.DefaultParams._SP_FCK_LangCompare();
				}else{
					NS.serverLocationHash = DefaultParams.serviceHost;
					NS.logotype = DefaultParams.logoPath;
					NS.loadIcon = DefaultParams.iconPath;
					NS.loadIconEmptyEditor = DefaultParams.iconPathEmptyEditor;
					NS.LangComparer = new _SP_FCK_LangCompare();
				}

				NS.pluginPath = CKEDITOR.getUrl(editor.plugins.wsc.path);
				NS.iframeNumber = NS.TextAreaNumber;
				NS.templatePath = NS.pluginPath + 'dialogs/tmp.html';
				NS.LangComparer.setDefaulLangCode( NS.defaultLanguage );
				NS.currentLang = editor.config.wsc_lang || NS.LangComparer.getSPLangCode( editor.langCode ) || 'en_US';
				NS.interfaceLang = editor.config.wsc_interfaceLang; //option to customize the interface language 12/28/2015
				NS.selectingLang = NS.currentLang;
				NS.div_overlay = new overlayBlock({
					opacity: "1",
					background: "#fff url(" + NS.loadIcon + ") no-repeat 50% 50%",
					target: NS.OverlayPlace

				});

				var number_ck = NS.dialog.parts.tabs.getId(),
					dialogPartsTab = CKEDITOR.document.getById(number_ck);

				dialogPartsTab.setStyle('width', '97%');
				if (!dialogPartsTab.getElementsByTag('DIV').count()){
					dialogPartsTab.append(NS.buildSelectLang(NS.dialog.getParentEditor().name));
				}

				NS.div_overlay_no_check = new overlayBlock({
					opacity: "1",
					id: 'no_check_over',
					background: "#fff url(" + NS.loadIconEmptyEditor + ") no-repeat 50% 50%",
					target: NS.OverlayPlace
				});

				if (success) {
					showFirstTab(NS.dialog);
					NS.dialog.setupContent(NS.dialog);
				}

				if (editor.plugins.scayt) {
					//is ssrv.cgi path for WSC and scayt same
					editor.wsc.isSsrvSame = (function() {
						var wscSsrvWholePath,
							wscServiceHost = CKEDITOR.config.wsc.DefaultParams.serviceHost.replace('lf/22/js/../../../', '').split('//')[1],
							wscSsrvHost = CKEDITOR.config.wsc.DefaultParams.ssrvHost,
							scaytSsrvWholePath,
							scaytSsrvProtocol,
							scaytSsrvHost,
							scaytSsrvPath,

							scaytSrcUrl = editor.config.scayt_srcUrl,
							scaytSsrvSrcUrlSsrvProtocol,
							scaytSsrvSrcUrlSsrvHost,
							scaytSsrvSrcUrlSsrvPath,

							scaytBasePath,
							scaytBasePathSsrvProtocol,
							scaytBasePathSsrvHost,
							scaytBasePathSsrvPath;

						if (window.SCAYT && window.SCAYT.CKSCAYT) {
							scaytBasePath = SCAYT.CKSCAYT.prototype.basePath;
							scaytBasePathSsrvProtocol = scaytBasePath.split('//')[0];
							scaytBasePathSsrvHost = scaytBasePath.split('//')[1].split('/')[0];
							scaytBasePathSsrvPath = scaytBasePath.split(scaytBasePathSsrvHost + '/')[1].replace('/lf/scayt3/ckscayt/', '') + '/script/ssrv.cgi';
						}

						if (scaytSrcUrl && !scaytBasePath && !editor.config.scayt_servicePath) {
							scaytSsrvSrcUrlSsrvProtocol = scaytSrcUrl.split('//')[0];
							scaytSsrvSrcUrlSsrvHost = scaytSrcUrl.split('//')[1].split('/')[0];
							scaytSsrvSrcUrlSsrvPath = scaytSrcUrl.split(scaytSsrvSrcUrlSsrvHost + '/')[1].replace('/lf/scayt3/ckscayt/ckscayt.js', '') + '/script/ssrv.cgi';
						}

						scaytSsrvProtocol = editor.config.scayt_serviceProtocol || scaytBasePathSsrvProtocol || scaytSsrvSrcUrlSsrvProtocol;
						scaytSsrvHost = editor.config.scayt_serviceHost || scaytBasePathSsrvHost || scaytSsrvSrcUrlSsrvHost;
						scaytSsrvPath = editor.config.scayt_servicePath || scaytBasePathSsrvPath || scaytSsrvSrcUrlSsrvPath;

						wscSsrvWholePath = '//' + wscServiceHost + wscSsrvHost;
						scaytSsrvWholePath = '//' + scaytSsrvHost + '/' + scaytSsrvPath;

						return wscSsrvWholePath === scaytSsrvWholePath;
					})();
				}

				//wsc on scayt UserDictionary and UserDictionaryName synchronization
				if (window.SCAYT && editor.wsc && editor.wsc.isSsrvSame) {
					var cgiOrigin = editor.wsc.cgiOrigin();
					editor.wsc.syncIsDone = false;

					var getUdOrUdn = function (e) {
						if (e.origin === cgiOrigin) {
							var data = JSON.parse(e.data);

							if (data.ud && data.ud !== 'undefined') {
								editor.wsc.ud = data.ud;
							} else if (data.ud === 'undefined') {
								editor.wsc.ud = undefined;
							}

							if (data.udn && data.udn !== 'undefined') {
								editor.wsc.udn = data.udn;
							} else if (data.udn === 'undefined') {
								editor.wsc.udn = undefined;
							}

							if (!editor.wsc.syncIsDone) {
								udSynchronization(editor.wsc.ud);
								editor.wsc.syncIsDone = true;
							}
						}
					};

					var udSynchronization = function(cookieUd) {
						var localStorageUdArray = editor.wsc.getLocalStorageUD(),
							newUd;

						if (localStorageUdArray instanceof Array) {
							newUd = localStorageUdArray.toString();
						}

						if (newUd !== undefined && newUd !== '') {
							setTimeout(function() {
								editor.wsc.addWords(newUd, function() {
									showFirstTab(NS.dialog);
									NS.dialog.setupContent(NS.dialog);
								});
							}, 400);
						}
					};

					if (window.addEventListener){
						addEventListener("message", getUdOrUdn, false);
					} else {
						window.attachEvent("onmessage", getUdOrUdn);
					}

					//wsc on scayt UserDictionaryName synchronization
					setTimeout(
						function() {
							var udn = editor.wsc.getLocalStorageUDN();

							if (udn !== undefined) {
								editor.wsc.operationWithUDN('restore', udn);
							}

						},
					500); //need to wait spell.js file to load

				}
			});

		},
		onHide: function() {
			var scaytPlugin = CKEDITOR.plugins.scayt,
				scaytInstance = editor.scayt;

			editor.unlockSelection();

			if(scaytPlugin && scaytInstance && scaytPlugin.state[editor.name]) {
				scaytInstance.setMarkupPaused(false);
			}

			NS.dataTemp = '';
			NS.sessionid = '';
			appTools.postMessage.unbindHandler(handlerIncomingData);

			//scayt on wsc UserDictionary and UserDictionaryName synchronization
			if (editor.plugins.scayt && editor.wsc && editor.wsc.isSsrvSame) {
				var	wscUDN = editor.wsc.udn,
					wscUD = editor.wsc.ud,
					wscUDarray,
					i;

				if (editor.scayt) { // if SCAYT active
					if (!wscUDN) {
						editor.wsc.DataStorage.setData('scayt_user_dictionary_name', '');
						editor.scayt.removeUserDictionary();
					} else {
						editor.wsc.DataStorage.setData('scayt_user_dictionary_name', wscUDN);
						editor.scayt.restoreUserDictionary(wscUDN);
					}

					if (wscUD) {
						setTimeout(function() {
							wscUDarray = wscUD.split(',');
							for (i = 0; i < wscUDarray.length; i += 1) {
								editor.scayt.addWordToUserDictionary(wscUDarray[i]);
							}
						}, 200); //wait for 'removeUserDictionary' command response
					}

					if (!wscUD) {
						editor.wsc.DataStorage.setData('scayt_user_dictionary', []);
					}

				} else { //if SCAYT not active

					if (!wscUDN) {
						editor.wsc.DataStorage.setData('scayt_user_dictionary_name', '');
					} else {
						editor.wsc.DataStorage.setData('scayt_user_dictionary_name', wscUDN);
					}

					if (wscUD) {
						wscUDarray = wscUD.split(',');
						editor.wsc.DataStorage.setData('scayt_user_dictionary', wscUDarray);
					}
				}
			}
		},
		contents: [
			{
				id: 'SpellTab',
				label: 'SpellChecker',
				accessKey: 'S',
				elements: [
					{
						type: 'html',
						id: 'banner',
						label: 'banner',
						style: '', //TODO
						html: '<div></div>'
					},
					{
						type: 'html',
						id: 'Content',
						label: 'spellContent',
						html: '',
						setup: function(dialog) {
							var tabId = NS.iframeNumber + '_' + dialog._.currentTabId;
							var iframe = document.getElementById(tabId);
							NS.targetFromFrame[tabId] = iframe.contentWindow;
						}
					},
					{
						type: 'hbox',
						id: 'bottomGroup',
						style: 'width:560px; margin: 0 auto;',
						widths: ['50%', '50%'],
						className: 'wsc-spelltab-bottom',
						children: [
							{
								type: 'hbox',
								id: 'leftCol',
								align: 'left',
								width: '50%',
								children: [
									{
										type: 'vbox',
										id: 'rightCol1',
										widths: ['50%', '50%'],
										children: [
											{
												type: 'text',
												id: 'ChangeTo_label',
												label: NS.LocalizationLabel['ChangeTo_label'].text + ':',
												labelLayout: 'horizontal',
												labelStyle: 'font: 12px/25px arial, sans-serif;',
												width: '140px',
												'default': '',
												onShow: function() {
													NS.textNode['SpellTab'] = this;
													NS.LocalizationLabel['ChangeTo_label'].instance = this;
												},
												onHide: function() {
													this.reset();
												}
											},
											{
												type: 'hbox',
												id: 'rightCol',
												align: 'right',
												width: '30%',
												children: [
													{
														type: 'vbox',
														id: 'rightCol_col__left',
														children: [
															{
																type: 'text',
																id: 'labelSuggestions',
																label: NS.LocalizationLabel['Suggestions'].text + ':',
																onShow: function() {
																	NS.LocalizationLabel['Suggestions'].instance = this;
																	this.getInputElement().setStyles({
																		display: 'none'
																	});
																}
															},
						 									{
																type: 'html',
																id: 'logo',
																html: '',
																setup: function(dialog) {
																	this.getElement().$.src = NS.logotype;
																	this.getElement().getParent().setStyles({
																		"text-align": "left"
																	});
																}
															}
														]
													},
													{
														type: 'select',
														id: 'list_of_suggestions',
														labelStyle: 'font: 12px/25px arial, sans-serif;',
														size: '6',
														inputStyle: 'width: 140px; height: auto;',
														items: [['loading...']],
														onShow: function() {
															selectNode = this;
														},
														onChange: function() {
															NS.textNode['SpellTab'].setValue(this.getValue());
														}
													}
												]
											}
										]
									}
								]
							},
							{
								type: 'hbox',
								id: 'rightCol',
								align: 'right',
								width: '50%',
								children: [
									{
										type: 'vbox',
										id: 'rightCol_col__left',
										widths: ['50%', '50%', '50%', '50%'],
										children: [
											{
												type: 'button',
												id: 'ChangeTo_button',
												label: NS.LocalizationButton['ChangeTo_button'].text,
												title: 'Change to',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'ChangeTo');
													NS.LocalizationButton['ChangeTo_button'].instance = this;
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'ChangeAll',
												label: NS.LocalizationButton['ChangeAll'].text,
												title: 'Change All',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", this.id);
													NS.LocalizationButton['ChangeAll'].instance = this;
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'AddWord',
												label: NS.LocalizationButton['AddWord'].text,
												title: 'Add word',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", this.id);
													NS.LocalizationButton['AddWord'].instance = this;
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'FinishChecking_button',
												label: NS.LocalizationButton['FinishChecking_button'].text,
												title: 'Finish Checking',
												style: 'width: 100%;margin-top: 9px;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'FinishChecking');
													NS.LocalizationButton['FinishChecking_button'].instance = this;
												},
												onClick: handlerButtons
											}
										]
									},
									{
										type: 'vbox',
										id: 'rightCol_col__right',
										widths: ['50%', '50%', '50%'],
										children: [
											{
												type: 'button',
												id: 'IgnoreWord',
												label: NS.LocalizationButton['IgnoreWord'].text,
												title: 'Ignore word',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", this.id);
													NS.LocalizationButton['IgnoreWord'].instance = this;
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'IgnoreAllWords',
												label: NS.LocalizationButton['IgnoreAllWords'].text,
												title: 'Ignore all words',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", this.id);
													NS.LocalizationButton['IgnoreAllWords'].instance = this;
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'Options',
												label: NS.LocalizationButton['Options'].text,
												title: 'Option',
												style: 'width: 100%;',
												onLoad: function() {
													NS.LocalizationButton['Options'].instance = this;
													if (document.location.protocol == "file:") {
														this.disable();
													}
												},
												onClick: function() {
													// because in chrome and safary document.activeElement returns <body> tag. We need to signal that clicked element is active
													this.getElement().focus();

													if (document.location.protocol == "file:") {
														alert('WSC: Options functionality is disabled when runing from file system');
													} else {
														activeElement = document.activeElement;
														editor.openDialog('options');
													}
												}
											}
										]
									}
								]
							}
				]
			},
			{
				type: 'hbox',
				id: 'BlockFinishChecking',
				style: 'width:560px; margin: 0 auto;',
				widths: ['70%', '30%'],
				onShow: function() {
					this.getElement().setStyles({
						display: 'block',
						position: 'absolute',
						left: '-9999px'
					});
				},
				onHide: showCurrentTabs,
				children: [
					{
						type: 'hbox',
						id: 'leftCol',
						align: 'left',
						width: '70%',
						children: [
							{
								type: 'vbox',
								id: 'rightCol1',
								setup: function() {
									this.getChild()[0].getElement().$.src = NS.logotype;
									this.getChild()[0].getElement().getParent().setStyles({
										"text-align": "center"
									});
								},
								children: [
									{
										type: 'html',
										id: 'logo',
										html: ''
									}
								]
							}
						]
					},
					{
						type: 'hbox',
						id: 'rightCol',
						align: 'right',
						width: '30%',
						children: [
							{
								type: 'vbox',
								id: 'rightCol_col__left',
								children: [
									{
										type: 'button',
										id: 'Option_button',
										label: NS.LocalizationButton['Options'].text,
										title: 'Option',
										style: 'width: 100%;',
										onLoad: function() {
											this.getElement().setAttribute("title-cmd", this.id);
											if (document.location.protocol == "file:") {
												this.disable();
											}
										},
										onClick: function() {
											// because in chrome and safary document.activeElement returns <body> tag. We need to signal that clicked element is active
											this.getElement().focus();

											if (document.location.protocol == "file:") {
												alert('WSC: Options functionality is disabled when runing from file system');
											} else {
												activeElement = document.activeElement;
												editor.openDialog('options');
											}
										}
									},
									{
										type: 'button',
										id: 'FinishChecking_button_block',
										label: NS.LocalizationButton['FinishChecking_button_block'].text,
										title: 'Finish Checking',
										style: 'width: 100%;',
										onLoad: function() {
											this.getElement().setAttribute("title-cmd", 'FinishChecking');
										},
										onClick: handlerButtons
									}
								]
							}
						]
					}
				]
			}
		]
		},
			{
				id: 'GrammTab',
				label: 'Grammar',
				accessKey: 'G',
				elements: [
					{
						type: 'html',
						id: 'banner',
						label: 'banner',
						style: '', //TODO
						html: '<div></div>'
					},
					{
						type: 'html',
						id: 'Content',
						label: 'GrammarContent',
						html: '',
						setup: function() {
							var tabId = NS.iframeNumber + '_' + NS.dialog._.currentTabId;
							var iframe = document.getElementById(tabId);
							NS.targetFromFrame[tabId] = iframe.contentWindow;
						}
					},
					{
						type: 'vbox',
						id: 'bottomGroup',
						style: 'width:560px; margin: 0 auto;',
						children: [
							{
								type: 'hbox',
								id: 'leftCol',
								widths: ['66%', '34%'],
								children: [
									{
										type: 'vbox',
										children: [
											{
												type: 'text',
												id: 'text',
												label: "Change to:",
												labelLayout: 'horizontal',
												labelStyle: 'font: 12px/25px arial, sans-serif;',
												inputStyle: 'float: right; width: 200px;',
												'default': '',
												onShow: function() {
													NS.textNode['GrammTab'] = this;
												},
												onHide: function() {
													this.reset();
												}
											},
											{
												type: 'html',
												id: 'html_text',
												html: "<div style='min-height: 17px; line-height: 17px; padding: 5px; text-align: left;background: #F1F1F1;color: #595959; white-space: normal!important;'></div>",
												onShow: function(e) {
													NS.textNodeInfo['GrammTab'] = this;
												}
											},
											{
												type: 'html',
												id: 'radio',
												html: "",
												onShow: function() {
													NS.grammerSuggest = this;
												}
											}
										]
									},
									{
										type: 'vbox',
										children: [
											{
												type: 'button',
												id: 'ChangeTo_button',
												label: 'Change to',
												title: 'Change to',
												style: 'width: 133px; float: right;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'ChangeTo');
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'IgnoreWord',
												label: 'Ignore word',
												title: 'Ignore word',
												style: 'width: 133px; float: right;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", this.id);
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'IgnoreAllWords',
												label: 'Ignore Problem',
												title: 'Ignore Problem',
												style: 'width: 133px; float: right;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", this.id);
												},
												onClick: handlerButtons
											},
											{
												type: 'button',
												id: 'FinishChecking_button',
												label: NS.LocalizationButton['FinishChecking_button'].text,
												title: 'Finish Checking',
												style: 'width: 133px; float: right; margin-top: 9px;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'FinishChecking');
												},
												onClick: handlerButtons
											}
										]
									}
								]
							}
						]
					},
					{
						type: 'hbox',
						id: 'BlockFinishChecking',
						style: 'width:560px; margin: 0 auto;',
						widths: ['70%', '30%'],
						onShow: function() {
							this.getElement().setStyles({
								display: 'block',
								position: 'absolute',
								left: '-9999px'
							});
						},
						onHide: showCurrentTabs,
						children: [
							{
								type: 'hbox',
								id: 'leftCol',
								align: 'left',
								width: '70%',
								children: [
									{
										type: 'vbox',
										id: 'rightCol1',
										children: [
											{
												type: 'html',
												id: 'logo',
												html: '',
												setup: function() {
													this.getElement().$.src = NS.logotype;
													this.getElement().getParent().setStyles({
														"text-align": "center"
													});
												}
											}
										]
									}
								]
							},
							{
								type: 'hbox',
								id: 'rightCol',
								align: 'right',
								width: '30%',
								children: [
									{
										type: 'vbox',
										id: 'rightCol_col__left',
										children: [
											{
												type: 'button',
												id: 'FinishChecking_button_block',
												label: NS.LocalizationButton['FinishChecking_button_block'].text,
												title: 'Finish Checking',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'FinishChecking');
												},
												onClick: handlerButtons
											}
										]
									}
								]
							}
						]
					}
				]
			},
			{
				id: 'Thesaurus',
				label: 'Thesaurus',
				accessKey: 'T',
				elements: [
					{
						type: 'html',
						id: 'banner',
						label: 'banner',
						style: '', //TODO
						html: '<div></div>'
					},
					{
						type: 'html',
						id: 'Content',
						label: 'spellContent',
						html: '',
						setup: function() {
							var tabId = NS.iframeNumber + '_' + NS.dialog._.currentTabId;
							var iframe = document.getElementById(tabId);
							NS.targetFromFrame[tabId] = iframe.contentWindow;
						}
					},
					{
						type: 'vbox',
						id: 'bottomGroup',
						style: 'width:560px; margin: -10px auto; overflow: hidden;',
						children: [
							{
								type: 'hbox',
								widths: ['75%', '25%'],
								children: [
									{
										type: 'vbox',
										children: [
											{
												type: 'hbox',
												widths: ['65%', '35%'],
												children: [
													{
														type: 'text',
														id: 'ChangeTo_label',
														label: NS.LocalizationLabel['ChangeTo_label'].text + ':',
														labelLayout: 'horizontal',
														inputStyle: 'width: 160px;',
														labelStyle: 'font: 12px/25px arial, sans-serif;',
														'default': '',
														onShow: function(e) {
															NS.textNode['Thesaurus'] = this;
															NS.LocalizationLabel['ChangeTo_label'].instance = this;
														},
														onHide: function() {
															this.reset();
														}
													},
													{
														type: 'button',
														id: 'ChangeTo_button',
														label: NS.LocalizationButton['ChangeTo_button'].text,
														title: 'Change to',
														style: 'width: 121px; margin-top: 1px;',
														onLoad: function() {
															this.getElement().setAttribute("title-cmd", 'ChangeTo');
															NS.LocalizationButton['ChangeTo_button'].instance = this;
														},
														onClick: handlerButtons
													}
												]
											},
											{
												type: 'hbox',
												children: [
													{
														type: 'select',
														id: 'Categories',
														label: NS.LocalizationLabel['Categories'].text + ':',
														labelStyle: 'font: 12px/25px arial, sans-serif;',
														size: '5',
														inputStyle: 'width: 180px; height: auto;',
														items: [],
														onShow: function() {
															NS.selectNode['Categories'] = this;
															NS.LocalizationLabel['Categories'].instance = this;
														},
														onChange: function() {
															NS.buildOptionSynonyms(this.getValue());
														}
													},
													{
														type: 'select',
														id: 'Synonyms',
														label: NS.LocalizationLabel['Synonyms'].text + ':',
														labelStyle: 'font: 12px/25px arial, sans-serif;',
														size: '5',
														inputStyle: 'width: 180px; height: auto;',
														items: [],
														onShow: function() {
															NS.selectNode['Synonyms'] = this;
															NS.textNode['Thesaurus'].setValue(this.getValue());
															NS.LocalizationLabel['Synonyms'].instance = this;
														},
														onChange: function(e) {
															NS.textNode['Thesaurus'].setValue(this.getValue());
														}
													}
												]
											}
										]
									},
									{
										type: 'vbox',
										width: '120px',
										style: "margin-top:46px;",
										children: [
											{
												type: 'html',
												id: 'logotype',
												label: 'WebSpellChecker.net',
												html: '',
												setup: function() {
													this.getElement().$.src = NS.logotype;
													this.getElement().getParent().setStyles({
														"text-align": "center"
													});
												}
											},
											{
												type: 'button',
												id: 'FinishChecking_button',
												label: NS.LocalizationButton['FinishChecking_button'].text,
												title: 'Finish Checking',
												style: 'width: 100%; float: right; margin-top: 9px;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'FinishChecking');
												},
												onClick: handlerButtons
											}
										]
									}
								]
							}
						]
					},
					{
						type: 'hbox',
						id: 'BlockFinishChecking',
						style: 'width:560px; margin: 0 auto;',
						widths: ['70%', '30%'],
						onShow: function() {
							this.getElement().setStyles({
								display: 'block',
								position: 'absolute',
								left: '-9999px'
							});
						},
						children: [
							{
								type: 'hbox',
								id: 'leftCol',
								align: 'left',
								width: '70%',
								children: [
									{
										type: 'vbox',
										id: 'rightCol1',
										children: [
											{
												type: 'html',
												id: 'logo',
												html: '',
												setup: function() {
													this.getElement().$.src = NS.logotype;
													this.getElement().getParent().setStyles({
														"text-align": "center"
													});
												}
											}
										]
									}
								]
							},
							{
								type: 'hbox',
								id: 'rightCol',
								align: 'right',
								width: '30%',
								children: [
									{
										type: 'vbox',
										id: 'rightCol_col__left',
										children: [
											{
												type: 'button',
												id: 'FinishChecking_button_block',
												label: NS.LocalizationButton['FinishChecking_button_block'].text,
												title: 'Finish Checking',
												style: 'width: 100%;',
												onLoad: function() {
													this.getElement().setAttribute("title-cmd", 'FinishChecking');
												},
												onClick: handlerButtons
											}
										]
									}
								]
							}
						]
					}
				]
			}
		]
	};
});

var activeElement = null;

// Options dialog
CKEDITOR.dialog.add('options', function(editor) {
	var dialog = null;
	var linkOnCheckbox = {};
	var checkboxState = {};
	var ospString = null;
	var OptionsTextError = null;
	var cmd = null;

	var set_osp = [];
	var dictionaryState = {
		'udn': appTools.cookie.get('udn'),
		'osp': appTools.cookie.get('osp')
	};

	var setHandlerOptions = function() {
		var osp = appTools.cookie.get('osp'),
			strToArr =  osp.split("");

		checkboxState['IgnoreAllCapsWords']		= strToArr[0];
		checkboxState['IgnoreWordsNumbers']		= strToArr[1];
		checkboxState['IgnoreMixedCaseWords']	= strToArr[2];
		checkboxState['IgnoreDomainNames']		= strToArr[3];
	};

	var sendDicOptions = function(event) {
		event = event || window.event;
		cmd = this.getElement().getAttribute("title-cmd");
		var osp = [];

		osp[0] = checkboxState['IgnoreAllCapsWords'];
		osp[1] = checkboxState['IgnoreWordsNumbers'];
		osp[2] = checkboxState['IgnoreMixedCaseWords'];
		osp[3] = checkboxState['IgnoreDomainNames'];

		osp = osp.toString().replace(/,/g, "");


		appTools.cookie.set('osp', osp);
		appTools.cookie.set('udnCmd', cmd ? cmd : 'ignore');
		if (cmd == "delete") {

			appTools.postMessage.send({
				'id': 'options_dic_send'
			});
		} else {
			var udn = '';
			if(nameNode.getValue() !== ''){
				udn = nameNode.getValue();
			}
			appTools.cookie.set('udn', udn);
			appTools.postMessage.send({
				'id': 'options_dic_send'
			});
		}

	};


	var sendAllOptions = function() {
		var osp = [];

		osp[0] = checkboxState['IgnoreAllCapsWords'];
		osp[1] = checkboxState['IgnoreWordsNumbers'];
		osp[2] = checkboxState['IgnoreMixedCaseWords'];
		osp[3] = checkboxState['IgnoreDomainNames'];

		osp = osp.toString().replace(/,/g, "");

		appTools.cookie.set('osp', osp);

		appTools.postMessage.send({
			'id': 'options_checkbox_send'
		});


	};

	var cameOptions = function() {
		OptionsTextError.getElement().setHtml(NS.LocalizationComing['error']);
		OptionsTextError.getElement().show();
	};

	return {
		title: NS.LocalizationComing['Options'],
		minWidth: 430,
		minHeight: 130,
		resizable: CKEDITOR.DIALOG_RESIZE_NONE,
		contents: [
			{
			id: 'OptionsTab',
			label: 'Options',
			accessKey: 'O',
			elements: [
				{
					type: 'hbox',
					id: 'options_error',
					children: [
						{
							type: 'html',
							style: "display: block;text-align: center;white-space: normal!important; font-size: 12px;color:red",
							html: '<div></div>',
							onShow: function() {
								OptionsTextError = this;
							}
						}
					]
				},
				{
				type: 'vbox',
				id: 'Options_content',
				children: [
					{
						type: 'hbox',
						id: 'Options_manager',
						widths: ['52%', '48%'],
						children: [
							{
								type: 'fieldset',
								label: 'Spell Checking Options',
								style: 'border: none;margin-top: 13px;padding: 10px 0 10px 10px',
								onShow: function() {
									this.getInputElement().$.children[0].innerHTML = NS.LocalizationComing['SpellCheckingOptions'];
								},
								children: [
									{
										type: 'vbox',
										id: 'Options_checkbox',
										children: [
											{
												type: 'checkbox',
												id: 'IgnoreAllCapsWords',
												label: 'Ignore All-Caps Words',
												labelStyle: 'margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;',
												style: "float:left; min-height: 16px;",
												'default': '',
												onClick: function() {
													checkboxState[this.id] = (!this.getValue()) ? 0 : 1;
												}
											},
											{
												type: 'checkbox',
												id: 'IgnoreWordsNumbers',
												label: 'Ignore Words with Numbers',
												labelStyle: 'margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;',
												style: "float:left; min-height: 16px;",
												'default': '',
												onClick: function() {
													checkboxState[this.id] = (!this.getValue()) ? 0 : 1;
												}
											},
											{
												type: 'checkbox',
												id: 'IgnoreMixedCaseWords',
												label: 'Ignore Mixed-Case Words',
												labelStyle: 'margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;',
												style: "float:left; min-height: 16px;",
												'default': '',
												onClick: function() {
													checkboxState[this.id] = (!this.getValue()) ? 0 : 1;
												}
											},
											{
												type: 'checkbox',
												id: 'IgnoreDomainNames',
												label: 'Ignore Domain Names',
												labelStyle: 'margin-left: 5px; font: 12px/16px arial, sans-serif;display: inline-block;white-space: normal;',
												style: "float:left; min-height: 16px;",
												'default': '',
												onClick: function() {
													checkboxState[this.id] = (!this.getValue()) ? 0 : 1;
												}
											}
									]
								}
							]
						},
						{
							type: 'vbox',
							id: 'Options_DictionaryName',
							children: [
								{
									type: 'text',
									id: 'DictionaryName',
									style: 'margin-bottom: 10px',
									label: 'Dictionary Name:',
									labelLayout: 'vertical',
									labelStyle: 'font: 12px/25px arial, sans-serif;',
									'default': '',
									onLoad: function() {
										nameNode = this;
										var udn = NS.userDictionaryName ? NS.userDictionaryName : appTools.cookie.get('udn') && undefined ? ' ' : this.getValue();
										this.setValue(udn);
									},
									onShow: function() {
										nameNode = this;
										var udn = !appTools.cookie.get('udn') ? this.getValue() : appTools.cookie.get('udn');
										this.setValue(udn);
										this.setLabel(NS.LocalizationComing['DictionaryName']);
									},
									onHide: function() {
										this.reset();
									}
								},
								{
									type: 'hbox',
									id: 'Options_buttons',
									children: [
										{
											type: 'vbox',
											id: 'Options_leftCol_col',
											widths: ['50%', '50%'],
											children: [
												{
													type: 'button',
													id: 'create',
													label: 'Create',
													title: 'Create',
													style: 'width: 100%;',
													onLoad: function() {
														this.getElement().setAttribute("title-cmd", this.id);
													},
													onShow: function() {
														var el = this.getElement().getFirst() || this.getElement();

														el.setText(NS.LocalizationComing['Create']);
													},
													onClick: sendDicOptions
												},
												{
													type: 'button',
													id: 'restore',
													label: 'Restore',
													title: 'Restore',
													style: 'width: 100%;',
													onLoad: function() {
														this.getElement().setAttribute("title-cmd", this.id);
													},
													onShow: function() {
														var el = this.getElement().getFirst() || this.getElement();

														el.setText(NS.LocalizationComing['Restore']);
													},
													onClick: sendDicOptions
												}
											]
										},
										{
											type: 'vbox',
											id: 'Options_rightCol_col',
											widths: ['50%', '50%'],
											children: [
												{
													type: 'button',
													id: 'rename',
													label: 'Rename',
													title: 'Rename',
													style: 'width: 100%;',
													onLoad: function() {
														this.getElement().setAttribute("title-cmd", this.id);
													},
													onShow: function() {
														var el = this.getElement().getFirst() || this.getElement();

														el.setText(NS.LocalizationComing['Rename']);
													},
													onClick: sendDicOptions
												},
												{
													type: 'button',
													id: 'delete',
													label: 'Remove',
													title: 'Remove',
													style: 'width: 100%;',
													onLoad: function() {
														this.getElement().setAttribute("title-cmd", this.id);
													},
													onShow: function() {
														var el = this.getElement().getFirst() || this.getElement();

														el.setText(NS.LocalizationComing['Remove']);
													},
													onClick: sendDicOptions
												}
											]
										}
									]
								}
							]
						}
					]
				},
				{
					type: 'hbox',
					id: 'Options_text',
					children: [
						{
							type: 'html',
							style: "text-align: justify;margin-top: 15px;white-space: normal!important; font-size: 12px;color:#777;",
							html: "<div>" + NS.LocalizationComing['OptionsTextIntro'] + "</div>",
							onShow: function() {
								this.getElement().setText(NS.LocalizationComing['OptionsTextIntro']);
							}
						}
					]
				}
			]
		}
	]
}
],
		buttons: [CKEDITOR.dialog.okButton, CKEDITOR.dialog.cancelButton],
		onOk: function() {
			sendAllOptions();
			OptionsTextError.getElement().hide();
			OptionsTextError.getElement().setHtml(' ');
		},
		onLoad: function() {
			dialog = this;
			// appTools.postMessage.init(cameOptions);

			linkOnCheckbox['IgnoreAllCapsWords'] = dialog.getContentElement('OptionsTab', 'IgnoreAllCapsWords');
			linkOnCheckbox['IgnoreWordsNumbers'] = dialog.getContentElement('OptionsTab', 'IgnoreWordsNumbers');
			linkOnCheckbox['IgnoreMixedCaseWords'] = dialog.getContentElement('OptionsTab', 'IgnoreMixedCaseWords');
			linkOnCheckbox['IgnoreDomainNames'] = dialog.getContentElement('OptionsTab', 'IgnoreDomainNames');

		},
		onShow: function() {
			appTools.postMessage.init(cameOptions);
			setHandlerOptions();

			(!parseInt(checkboxState['IgnoreAllCapsWords'], 10)) ? linkOnCheckbox['IgnoreAllCapsWords'].setValue('', false) : linkOnCheckbox['IgnoreAllCapsWords'].setValue('checked', false);
			(!parseInt(checkboxState['IgnoreWordsNumbers'], 10)) ? linkOnCheckbox['IgnoreWordsNumbers'].setValue('', false) : linkOnCheckbox['IgnoreWordsNumbers'].setValue('checked', false);
			(!parseInt(checkboxState['IgnoreMixedCaseWords'], 10)) ? linkOnCheckbox['IgnoreMixedCaseWords'].setValue('', false) : linkOnCheckbox['IgnoreMixedCaseWords'].setValue('checked', false);
			(!parseInt(checkboxState['IgnoreDomainNames'], 10)) ? linkOnCheckbox['IgnoreDomainNames'].setValue('', false) : linkOnCheckbox['IgnoreDomainNames'].setValue('checked', false);

			checkboxState['IgnoreAllCapsWords'] = (!linkOnCheckbox['IgnoreAllCapsWords'].getValue()) ? 0 : 1;
			checkboxState['IgnoreWordsNumbers'] = (!linkOnCheckbox['IgnoreWordsNumbers'].getValue()) ? 0 : 1;
			checkboxState['IgnoreMixedCaseWords'] = (!linkOnCheckbox['IgnoreMixedCaseWords'].getValue()) ? 0 : 1;
			checkboxState['IgnoreDomainNames'] = (!linkOnCheckbox['IgnoreDomainNames'].getValue()) ? 0 : 1;

			linkOnCheckbox['IgnoreAllCapsWords'].getElement().$.lastChild.innerHTML = NS.LocalizationComing['IgnoreAllCapsWords'];
			linkOnCheckbox['IgnoreWordsNumbers'].getElement().$.lastChild.innerHTML = NS.LocalizationComing['IgnoreWordsWithNumbers'];
			linkOnCheckbox['IgnoreMixedCaseWords'].getElement().$.lastChild.innerHTML = NS.LocalizationComing['IgnoreMixedCaseWords'];
			linkOnCheckbox['IgnoreDomainNames'].getElement().$.lastChild.innerHTML = NS.LocalizationComing['IgnoreDomainNames'];
		},
		onHide: function() {
			appTools.postMessage.unbindHandler(cameOptions);
			if(activeElement) {
				try {
					activeElement.focus();
				} catch(e) {}
			}
		}
	};
});

// Expand the spell-check frame when dialog resized. (#6829)
CKEDITOR.dialog.on( 'resize', function( evt ) {
	var data = evt.data,
		dialog = data.dialog,
		currentTabId = dialog._.currentTabId,
		tabID = NS.iframeNumber + '_' + currentTabId,
		iframe = CKEDITOR.document.getById(tabID);

	if ( dialog._.name == 'checkspell' ) {
		if (NS.bnr) {
			iframe && iframe.setSize( 'height', data.height - '310' );
		} else {
			iframe && iframe.setSize( 'height', data.height - '220' );
		}

		// add flag that indicate whether dialog has been resized by user
		if(dialog._.fromResizeEvent && !dialog._.resized) {
			dialog._.resized = true;
		}
		dialog._.fromResizeEvent = true;
	}
});

CKEDITOR.on('dialogDefinition', function(dialogDefinitionEvent) {

    if(dialogDefinitionEvent.data.name === 'checkspell') {
		var dialogDefinition = dialogDefinitionEvent.data.definition;

		 NS.onLoadOverlay = new overlayBlock({
			opacity: "1",
			background: "#fff",
			target: dialogDefinition.dialog.parts.tabs.getParent().$
		});

		NS.onLoadOverlay.setEnable();

		dialogDefinition.dialog.on('cancel', function(cancelEvent) {
			dialogDefinition.dialog.getParentEditor().config.wsc_onClose.call(this.document.getWindow().getFrame());
    		NS.div_overlay.setDisable();
    		NS.onLoadOverlay.setDisable();
			return false;
		}, this, null, -1);
	}
});
})();
