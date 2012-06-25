/*!
 * jQuery Right-Click Plugin
 *
 * Version 1.01
 *
 * Cory S.N. LaViska
 * A Beautiful Site (http://abeautifulsite.net/)
 * 20 December 2008
 *
 * Visit http://abeautifulsite.net/notebook/68 for more information
 *
 * License:
 * This plugin is dual-licensed under the GNU General Public License and the MIT License
 * and is copyright 2008 A Beautiful Site, LLC.
 */
if(jQuery){(function(){$.extend($.fn,{rightClick:function(a){$(this).each(function(){$(this).mousedown(function(c){var b=c;if($.browser.safari&&navigator.userAgent.indexOf("Mac")!=-1&&parseInt($.browser.version,10)<=525){if(b.button==2){a.call($(this),b);return false}else{return true}}else{$(this).mouseup(function(){$(this).unbind("mouseup");if(b.button==2){a.call($(this),b);return false}else{return true}})}});$(this)[0].oncontextmenu=function(){return false}});return $(this)},rightMouseDown:function(a){$(this).each(function(){$(this).mousedown(function(b){if(b.button==2){a.call($(this),b);return false}else{return true}});$(this)[0].oncontextmenu=function(){return false}});return $(this)},rightMouseUp:function(a){$(this).each(function(){$(this).mouseup(function(b){if(b.button==2){a.call($(this),b);return false}else{return true}});$(this)[0].oncontextmenu=function(){return false}});return $(this)},noContext:function(){$(this).each(function(){$(this)[0].oncontextmenu=function(){return false}});return $(this)}})})(jQuery)};