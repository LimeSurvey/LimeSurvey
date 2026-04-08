/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"en",name:"ImgAlt",callback:function(c){function a(a){c.call(this,a)}a.prototype=new c;a.prototype.constructor=a;a.prototype.validate=function(a){var b=[];a.alt+""||b.push(this.lang.errorEmpty);b.length||(b=c.prototype.validate.call(this,a));return b};a.prototype.lang={altLabel:"Alternative text",errorTooLong:"Alternative text is too long. It should be up to {limit} characters while your has {length}",errorWhitespace:"Alternative text can not only contain whitespace characters",
errorSameAsFileName:"Image alt should not be the same as the file name",errorEmpty:"Alternative text can not be empty"};CKEDITOR.plugins.a11ychecker.quickFixes.add("en/ImgAltNonEmpty",a)}})})();