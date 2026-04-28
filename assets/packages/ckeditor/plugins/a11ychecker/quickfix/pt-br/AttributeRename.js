/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"pt-br",name:"QuickFix",callback:function(b){function a(a){b.call(this,a)}a.prototype=new b;a.prototype.constructor=a;a.prototype.attributeName="title";a.prototype.attributeTargetName="alt";a.prototype.getProposedValue=function(){return this.issue.element.getAttribute(this.attributeName)||""};a.prototype.display=function(a){a.setInputs({value:{type:"text",label:"Value",value:this.getProposedValue()}})};a.prototype.fix=function(a,b){var c=
this.issue.element;c.setAttribute(this.attributeTargetName,a.value);c.removeAttribute(this.attributeName);b&&b(this)};a.prototype.lang={};CKEDITOR.plugins.a11ychecker.quickFixes.add("pt-br/AttributeRename",a)}})})();