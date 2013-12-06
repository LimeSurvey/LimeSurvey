/*!
 * jQuery twitter bootstrap wizard plugin
 * Examples and documentation at: http://github.com/VinceG/twitter-bootstrap-wizard
 * version 1.0
 * Requires jQuery v1.3.2 or later
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Authors: Vadim Vincent Gabriel (http://vadimg.com)
 */
;(function($) {
var bootstrapWizardCreate = function(element, options) {
	var element = $(element);
	var obj = this;
	
	// Merge options with defaults
	//var $settings = $.extend($.fn.bootstrapWizard.defaults, options || {});
	var $settings = $.extend({}, $.fn.bootstrapWizard.defaults, options);
	var $activeTab = null;
	var $navigation = null;
	
	this.fixNavigationButtons = function() {
		// Get the current active tab
		if(!$activeTab.length) {
			// Select first one
			$navigation.find('a:first').tab('show');
			$activeTab = $navigation.find('li:first');
		}
		
		// See if we currently in the first then disable the previous and last buttons
		if(obj.firstIndex() >= obj.currentIndex()) {
			$('li.previous', element).addClass('disabled');
		} else{
			$('li.previous', element).removeClass('disabled');
		}
		
		if(obj.currentIndex() >= obj.navigationLength()) {
			$('li.next', element).addClass('disabled');
		} else {
			$('li.next', element).removeClass('disabled');
		}
		
		if($settings.onTabShow && typeof $settings.onTabShow === 'function' && $settings.onTabShow($activeTab, $navigation, obj.currentIndex())===false){
		    return false;
		}
	};
	
	this.next = function(e) {
		
		// If we clicked the last then dont activate this
		if(element.hasClass('last')) {
			return false;
		}
		
		if($settings.onNext && typeof $settings.onNext === 'function' && $settings.onNext($activeTab, $navigation, obj.nextIndex())===false){
		    return false;
		}
		
		// Did we click the last button
		$index = obj.nextIndex();
		if($index > obj.navigationLength()) {
		} else {
			$navigation.find('li:eq('+$index+') a').tab('show');
		}
    };

	this.previous = function(e) {
		
		// If we clicked the first then dont activate this
		if(element.hasClass('first')) {
			return false;
		}
		
		if($settings.onPrevious && typeof $settings.onPrevious === 'function' && $settings.onPrevious($activeTab, $navigation, obj.previousIndex())===false){
		    return false;
		}
		
      	$index = obj.previousIndex();
		if($index < 0) {
		} else {
			$navigation.find('li:eq('+$index+') a').tab('show');
		}
    };

	this.first = function(e) {
		if($settings.onFirst && typeof $settings.onFirst === 'function' && $settings.onFirst($activeTab, $navigation, obj.firstIndex())===false){
		    return false;
		}
		
		// If the element is disabled then we won't do anything
		if(element.hasClass('disabled')) {
			return false;
		}
		$navigation.find('li:eq(0) a').tab('show');
		
	};
	this.last = function(e) {
		if($settings.onLast && typeof $settings.onLast === 'function' && $settings.onLast($activeTab, $navigation, obj.lastIndex())===false){
		    return false;
		}
		
		// If the element is disabled then we won't do anything
		if(element.hasClass('disabled')) {
			return false;
		}
		$navigation.find('li:eq('+obj.navigationLength()+') a').tab('show');
	};
	this.currentIndex = function() {
		return $navigation.find('li').index($activeTab);
	};
	this.firstIndex = function() {
		return 0;
	};
	this.lastIndex = function() {
		return obj.navigationLength();
	};
	this.getIndex = function(elem) {
		return $navigation.find('li').index(elem);
	};
	this.nextIndex = function() {
		return $navigation.find('li').index($activeTab) + 1;
	};
	this.previousIndex = function() {
		return $navigation.find('li').index($activeTab) - 1;
	};
	this.navigationLength = function() {
		return $navigation.find('li').length - 1;
	};
	this.activeTab = function() {
		return $activeTab;
	};
	this.nextTab = function() {
		return $navigation.find('li:eq('+(obj.currentIndex()+1)+')').length ? $navigation.find('li:eq('+(obj.currentIndex()+1)+')') : null;
	};
	this.previousTab = function() {
		if(obj.currentIndex() <= 0) {
			return null;
		}
		return $navigation.find('li:eq('+parseInt(obj.currentIndex()-1)+')');
	};
	
	$navigation = element.find('ul:first', element);
	$activeTab = $navigation.find('li.active', element);
	
	if(!$navigation.hasClass($settings.class)) {
		$navigation.addClass($settings.class);
	}
	
	// Load onShow
	if($settings.onInit && typeof $settings.onInit === 'function'){
	    $settings.onInit($activeTab, $navigation, 0);
	}
	
	// Next/Previous events
	$($settings.nextSelector, element).bind('click', obj.next);
	$($settings.previousSelector, element).bind('click', obj.previous);
	$($settings.lastSelector, element).bind('click', obj.last);
	$($settings.firstSelector, element).bind('click', obj.first);

	// Load onShow
	if($settings.onShow && typeof $settings.onShow === 'function'){
	    $settings.onShow($activeTab, $navigation, obj.nextIndex());
	}
	
	// Work the next/previous buttons
	obj.fixNavigationButtons();
	
	$('a[data-toggle="tab"]', element).on('click', function (e) {
		if($settings.onTabClick && typeof $settings.onTabClick === 'function' && $settings.onTabClick($activeTab, $navigation, obj.currentIndex())===false){
		    return false;
		}
	});
	
	$('a[data-toggle="tab"]', element).on('show', function (e) {
		$element = $(e.target).parent();
		// If it's disabled then do not change
		if($element.hasClass('disabled')) {
			return false;
		}
		
	  	$activeTab = $element; // activated tab
		obj.fixNavigationButtons();

	});
};
$.fn.bootstrapWizard = function(options) {	
    return this.each(function(index){
        var element = $(this);
		// Return early if this element already has a plugin instance
		if (element.data('bootstrapWizard')) return;
		// pass options to plugin constructor
		var wizard = new bootstrapWizardCreate(element, options);
		// Store plugin object in this element's data
		element.data('bootstrapWizard', wizard);
    });
};

// expose options
$.fn.bootstrapWizard.defaults = {
    'class'         : 'nav nav-pills',
	'nextSelector': '.wizard li.next',
	'previousSelector': '.wizard li.previous',
	'firstSelector': '.wizard li.first',
	'lastSelector': '.wizard li.last',
	'onShow' : null,
	'onInit': null,
	'onNext': null,
	'onPrevious': null,
	'onLast': null,
	'onFirst': null,
	'onTabClick': null,
	'onTabShow': null
};

})(jQuery);
