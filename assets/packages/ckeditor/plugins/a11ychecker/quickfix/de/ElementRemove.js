﻿/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"de",name:"QuickFix",callback:function(b){function a(a){b.call(this,a)}a.prototype=new b;a.prototype.constructor=a;a.prototype.display=function(a){a.setInputs({})};a.prototype.fix=function(a,b){this.issue.element.remove();b&&b(this)};a.prototype.lang={};CKEDITOR.plugins.a11ychecker.quickFixes.add("de/ElementRemove",a)}})})();