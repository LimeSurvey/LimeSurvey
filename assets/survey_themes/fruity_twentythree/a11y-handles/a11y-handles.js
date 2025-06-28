const A11yHandles = {
	handleTooltip: function handleTooltip() {
		document.body.addEventListener('keydown', function (e) {
			if (e.key == 'Escape') {
				console.log('Escape key pressed');
				document
					.querySelectorAll('[data-bs-toggle="tooltip"]')
					.forEach((tooltipTriggerEl) => {
						const tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
						tooltip.hide();
					});
			}
		});
	},

	handleTitle: function handleTitle() {
		const existingTitle = document.title;
		const heading = document.querySelector('h1.group-title');
		if (heading) {
			document.title = existingTitle + ' - ' + heading.textContent;
		}
	},
};

$(document).on('ready pjax:scriptcomplete', function () {
	A11yHandles.handleTooltip();
	A11yHandles.handleTitle();
});
