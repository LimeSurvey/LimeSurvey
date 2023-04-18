/**
 * @license Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/license
 */

( function() {
	'use strict';

	CKEDITOR.plugins.a11ychecker.quickFixes.get( { langCode: 'pt-br',
		name: 'ImgAlt',
		callback: function( ImgAlt ) {

			/**
			 * Fixes the image with missing alt attribute, requiring non-empty alt.
			 *
			 * @constructor
			 */
			function ImgAltNonEmpty( issue ) {
				ImgAlt.call( this, issue );
			}

			ImgAltNonEmpty.prototype = new ImgAlt();
			ImgAltNonEmpty.prototype.constructor = ImgAltNonEmpty;

			ImgAltNonEmpty.prototype.validate = function( formAttributes ) {
				var ret = [],
					proposedAlt = formAttributes.alt + '';

				if ( !proposedAlt ) {
					ret.push( this.lang.errorEmpty );
				}

				if ( !ret.length ) {
					ret = ImgAlt.prototype.validate.call( this, formAttributes );
				}

				return ret;
			};

			ImgAltNonEmpty.prototype.lang = {"altLabel":"Texto alternativo","errorTooLong":"O texto alternativo é muito longo. Este deve conter no máximo {limit} caracteres, enquanto o seu possui {length}","errorWhitespace":"O texto alternativo não pode conter somente espaços em branco.","errorSameAsFileName":"O texto alternativo da imagem não deve ter o mesmo nome do arquivo da imagem","errorEmpty":"O texto alternativo não deve estar vazio"};
			CKEDITOR.plugins.a11ychecker.quickFixes.add( 'pt-br/ImgAltNonEmpty', ImgAltNonEmpty );
		}
	} );
}() );
