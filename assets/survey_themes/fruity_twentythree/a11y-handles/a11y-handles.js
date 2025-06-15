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

	const initLiveRegion = function initLiveRegion() {
		const LiveRegion = document.createElement('div');
		LiveRegion.id = 'live-region';
		LiveRegion.classList.add('sr-only');
		LiveRegion.setAttribute('aria-live', 'polite');
		LiveRegion.setAttribute('role', 'status');
		document.body.appendChild(LiveRegion);
	};
	let timeout;
	const liveAnnounce = function liveAnnounce(message) {
		const liveRegion = document.getElementById('live-region');
		clearTimeout(timeout);
		liveRegion.innerHTML = '';
		timeout = setTimeout(() => {
			liveRegion.innerHTML = '<p>' + message + '</p>';
		}, 500);
	};

	return {
		handleTooltip: handleTooltip,
		initLiveRegion: initLiveRegion,
		liveAnnounce: liveAnnounce,
	};
};

$(document).on('ready pjax:scriptcomplete', function () {
	A11yHandles().handleTooltip();
	A11yHandles().initLiveRegion();
});

$(document).on('classChangeError', function (event) {
	A11yHandles().liveAnnounce(event.target.textContent.trim());
});

$(document).on('classChangeGood', function (event) {
	A11yHandles().liveAnnounce(event.target.textContent.trim());
});
