/*
 Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"nl",name:"ImgAlt",callback:function(c){function a(a){c.call(this,a)}a.prototype=new c;a.prototype.constructor=a;a.prototype.validate=function(a){var b=[];a.alt+""||b.push(this.lang.errorEmpty);b.length||(b=c.prototype.validate.call(this,a));return b};a.prototype.lang={altLabel:"Alternatieve tekst",errorTooLong:"Alternatieve tekst is te lang. Deze mag maximaal {limit} karaktersbevatten terwijl opgegeven tekst {length} bevat",errorWhitespace:"Alternatieve tekst mag niet alleen uit spaties bestaan",
errorSameAsFileName:"Alt-tekst van de afbeelding mag niet hetzelfde zijn als de bestandsnaam",errorEmpty:"Alternatieve tekst mag niet leeg zijn"};CKEDITOR.plugins.a11ychecker.quickFixes.add("nl/ImgAltNonEmpty",a)}})})();