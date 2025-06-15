export const A11yHandles = function () {
	const handleTooltip = function handleTooltip() {
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
	};

	return {
		handleTooltip: handleTooltip,
	};
};

$(document).on('ready pjax:scriptcomplete', function () {
	A11yHandles().handleTooltip();
});
