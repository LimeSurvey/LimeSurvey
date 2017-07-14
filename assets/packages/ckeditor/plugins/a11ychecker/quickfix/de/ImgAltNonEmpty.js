/*
 Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"de",name:"ImgAlt",callback:function(c){function a(a){c.call(this,a)}a.prototype=new c;a.prototype.constructor=a;a.prototype.validate=function(a){var b=[];a.alt+""||b.push(this.lang.errorEmpty);b.length||(b=c.prototype.validate.call(this,a));return b};a.prototype.lang={altLabel:"Alternativtext",errorTooLong:"Der Alternativtext ist zu lang. Er sollte {limit} Zeichen lang sein, ist aber aktuell {length} Zeichen lang",errorWhitespace:"Der Alternativtext kann nicht nur Leerzeichen enthalten",
errorSameAsFileName:"Der Alternativtext sollte nicht dem Dateinamen entsprechen",errorEmpty:"Der Alternativtext sollte nicht leer sein"};CKEDITOR.plugins.a11ychecker.quickFixes.add("de/ImgAltNonEmpty",a)}})})();