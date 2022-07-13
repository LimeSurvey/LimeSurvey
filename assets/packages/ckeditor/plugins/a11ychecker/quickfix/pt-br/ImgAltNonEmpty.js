/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"pt-br",name:"ImgAlt",callback:function(c){function a(a){c.call(this,a)}a.prototype=new c;a.prototype.constructor=a;a.prototype.validate=function(a){var b=[];a.alt+""||b.push(this.lang.errorEmpty);b.length||(b=c.prototype.validate.call(this,a));return b};a.prototype.lang={altLabel:"Texto alternativo",errorTooLong:"O texto alternativo é muito longo. Este deve conter no máximo {limit} caracteres, enquanto o seu possui {length}",errorWhitespace:"O texto alternativo não pode conter somente espaços em branco.",
errorSameAsFileName:"O texto alternativo da imagem não deve ter o mesmo nome do arquivo da imagem",errorEmpty:"O texto alternativo não deve estar vazio"};CKEDITOR.plugins.a11ychecker.quickFixes.add("pt-br/ImgAltNonEmpty",a)}})})();