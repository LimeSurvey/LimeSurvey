/*
 Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"de",name:"QuickFix",callback:function(b){function a(c){b.call(this,c)}var e=/^[\s\n\r]+$/g;a.prototype=new b;a.prototype.constructor=a;a.prototype.display=function(c){c.setInputs({caption:{type:"text",label:this.lang.captionLabel}})};a.prototype.fix=function(c,a){var b=this.issue.element,d=b.getDocument().createElement("caption");d.setHtml(c.caption);b.append(d,!0);a&&a(this)};a.prototype.validate=function(a){a=a.caption;var b=[];
a&&!a.match(e)||b.push(this.lang.errorEmpty);return b};a.prototype.lang={captionLabel:"Beschriftung",errorEmpty:"Beschriftungen dürfen nicht leer sein"};CKEDITOR.plugins.a11ychecker.quickFixes.add("de/AddTableCaption",a)}})})();