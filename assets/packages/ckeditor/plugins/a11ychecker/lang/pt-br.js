/**
 * @license Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/license
 */

CKEDITOR.plugins.setLang( 'a11ychecker', 'pt-br', {
	toolbar: 'Verificador de Acessibilidade',
	closeBtn: 'Fechar',
	testability: {
		'0': 'observação',
		'0.5': 'alerta',
		'1': 'erro'
	},
	ignoreBtn: 'Ignorar',
	ignoreBtnTitle: 'Ignorar este problema',
	stopIgnoreBtn: 'Parar de ignorar',
	listeningInfo: 'Aguardando por mudanças no conteúdo. Quando terminar, clique em <strong>Verificar novamente</strong> abaixo.',
	listeningCheckAgain: 'Verificar novamente',
	balloonLabel: 'Verificador de Acessibilidade',
	navigationNext: 'Próximo',
	navigationNextTitle: 'Próximo problema',
	navigationPrev: 'Anterior',
	navigationPrevTitle: 'Problema anterior',
	quickFixButton: 'Reparar rapidamente',
	quickFixButtonTitle: 'Reparar rapidamente este problema',
	navigationCounter: 'Problema {current} de {total} ({testability})',
	noIssuesMessage: 'O documento não possui nenhum problema de acessibilidade identificado.'
} );
