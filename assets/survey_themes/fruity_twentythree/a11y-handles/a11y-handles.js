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

/**
 * Hides the keyboard focus ring while the participant uses a mouse or touch, and shows it again
 * as soon as they navigate with the keyboard. The mode is kept across pages in the session storage.
 * Without JavaScript the class is never set, so the focus ring always stays visible.
 */
const initNavigationMode = function initNavigationMode() {
	const pointerClass = 'ls-pointer-nav';
	const storageKey = 'lsNavigationMode';
	// Keys that move the focus or change a choice
	const navigationKeys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'PageUp', 'PageDown', ' '];
	const textEntrySelector = 'textarea, [contenteditable]:not([contenteditable="false"]), ' +
		'input:not([type="radio"]):not([type="checkbox"]):not([type="button"]):not([type="submit"])' +
		':not([type="reset"]):not([type="image"]):not([type="range"]):not([type="file"]):not([type="color"])';

	const setMode = function setMode(mode) {
		document.documentElement.classList.toggle(pointerClass, mode === 'pointer');
		try {
			sessionStorage.setItem(storageKey, mode);
		} catch (e) {
			// Storage can be unavailable (private mode, blocked site data): The mode then only lasts for this page
		}
	};

	try {
		if (sessionStorage.getItem(storageKey) === 'pointer') {
			document.documentElement.classList.add(pointerClass);
		}
	} catch (e) {
		// See setMode()
	}

	document.addEventListener('pointerdown', function () {
		setMode('pointer');
	}, true);
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Tab') {
			setMode('keyboard');
			return;
		}
		// Arrow keys, Space etc. inside a text field edit the text instead of navigating
		if (navigationKeys.includes(event.key) && !(event.target.matches && event.target.matches(textEntrySelector))) {
			setMode('keyboard');
		}
	}, true);
};

// Document-level listeners: Register only once, they survive the ajax page loads
initNavigationMode();

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
