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
};

$(document).on('ready pjax:scriptcomplete', function () {
	A11yHandles.initLiveRegion();
});

$(document).on('classChangeError', function (event) {
	A11yHandles.liveAnnounce(event.target.textContent.trim());
});

$(document).on('classChangeGood', function (event) {
	A11yHandles.liveAnnounce(event.target.textContent.trim());
});

export default A11yHandles;
