let timeout;
const A11yHandles = {
	initLiveRegion: function initLiveRegion() {
		const LiveRegion = document.createElement('div');
		LiveRegion.id = 'live-region';
		LiveRegion.classList.add('sr-only');
		LiveRegion.setAttribute('aria-live', 'polite');
		LiveRegion.setAttribute('role', 'status');
		document.body.appendChild(LiveRegion);
	},

	liveAnnounce: function liveAnnounce(message) {
		const liveRegion = document.getElementById('live-region');
		clearTimeout(timeout);
		liveRegion.innerHTML = '';
		timeout = setTimeout(() => {
			liveRegion.innerHTML = '<p>' + message + '</p>';
		}, 500);
	},
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
	A11yHandles.initLiveRegion();
	A11yHandles.handleTooltip();
	A11yHandles.handleTitle();
});

$(document).on('classChangeError', function (event) {
	A11yHandles.liveAnnounce(event.target.textContent.trim());
});

$(document).on('classChangeGood', function (event) {
	A11yHandles.liveAnnounce(event.target.textContent.trim());
});

export default A11yHandles;
