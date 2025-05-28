/******************
    User custom JS
    ---------------

   Put JS-functions for your template here.
   If possible use a closure, or add them to the general Template Object "Template"
*/

$(document).on('ready pjax:scriptcomplete', function () {
	/**
	 * Code included inside this will only run once the page Document Object Model (DOM) is ready for JavaScript code to execute
	 * @see https://learn.jquery.com/using-jquery-core/document-ready/
	 */
	document.body.addEventListener('keydown', function (e) {
		if (e.key == 'Escape') {
			console.log('Escape key pressed');
			const exampleTriggerEl = document.getElementById('progressbar-top');
			const tooltip = bootstrap.Tooltip.getInstance(exampleTriggerEl);
			tooltip.hide();
		}
	});
});
console.log('Custom JS loaded');
