/*
 Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"de",name:"QuickFix",callback:function(b){function a(a){b.call(this,a)}a.prototype=new b;a.prototype.constructor=a;a.prototype.getTargetName=function(a){return"h1"};a.prototype.display=function(a){a.setInputs({})};a.prototype.fix=function(a,b){var c=new CKEDITOR.dom.element(this.getTargetName(a));c.replace(this.issue.element);this.issue.element.moveChildren(c);this.issue.element=c;b&&b(this)};a.prototype.lang={};CKEDITOR.plugins.a11ychecker.quickFixes.add("de/ElementReplace",
a)}})})();