/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"en",name:"QuickFix",callback:function(c){function a(a){c.call(this,a)}a.prototype=new c;a.prototype.constructor=a;a.prototype.fix=function(a,c){for(var d=this.issue.element,e=d.getNext(),g=d.getAttribute("href"),f="",h=function(a){return a?a.getName&&"a"==a.getName()&&e.getAttribute("href")==g||a.type===CKEDITOR.NODE_TEXT&&a.getText().match(/^[\s]*$/):!1},b;h(e);)b=e,f+=b.type===CKEDITOR.NODE_TEXT&&b.getText().match(/^[\s]*$/)?b.getText():
b.getHtml(),e=b.getNext(),b.remove();f&&d.setHtml(d.getHtml()+f);c&&c(this)};a.prototype.lang={};CKEDITOR.plugins.a11ychecker.quickFixes.add("en/AnchorsMerge",a)}})})();