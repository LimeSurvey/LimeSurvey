/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 *
 * Deselect single-choice radio buttons
 *
 * Enables deselection of an already-selected radio button by clicking it again.
 * Applies to single-choice question containers that also carry the CSS class
 * "deselect-singlechoice", added by the fruity_twentythree theme when the
 * "deselectsinglechoice" theme option is enabled.
 *
 * Supported question types (matched by their container CSS class):
 *   - list-radio        (type L)
 *   - list-with-comment (type O)
 *
 * When a radio is deselected, a custom "ls:singlechoiceDeselected" event is
 * dispatched on the question container with detail { name, value }, allowing
 * other modules to react (e.g. clearing the "other" text field).
 *
 * Uses event capture for the click listener so it fires even when inline onclick
 * handlers call stopPropagation() (e.g. cancelBubbleThis in image-select questions).
 * The actual deselect is deferred via setTimeout so it runs after all synchronous
 * click handlers, ensuring our checkconditions('') call wins over any inline ones.
 */

const DeselectSinglechoiceScripts = (() => {
    let initialized = false;
    let previouslyCheckedRadio = null;

    const CONTAINER_SELECTOR  = '.deselect-singlechoice.list-radio, .deselect-singlechoice.list-with-comment';
    const RADIO_SELECTOR       = '.deselect-singlechoice.list-radio input[type="radio"], .deselect-singlechoice.list-with-comment input[type="radio"]';
    const LABEL_SELECTOR       = '.deselect-singlechoice.list-radio label, .deselect-singlechoice.list-with-comment label';

    const recordCheckedState = (radio) => {
        previouslyCheckedRadio = (radio && radio.checked) ? radio : null;
    };

    const init = () => {
        if (initialized) {
            return;
        }
        initialized = true;

        // Direct click on the radio input circle
        $(document).on('mousedown', RADIO_SELECTOR, function () {
            recordCheckedState(this);
        });

        // Click via an associated label (label may be a sibling, not a parent).
        // mousedown fires on the label; the browser then synthesises a click on the input.
        $(document).on('mousedown', LABEL_SELECTOR, function () {
            const forId = this.getAttribute('for');
            recordCheckedState(forId ? document.getElementById(forId) : null);
        });

        // Native capture listener: fires top-down before stopPropagation() in inline
        // onclick handlers (e.g. cancelBubbleThis used by image-select questions).
        document.addEventListener('click', function (e) {
            const radio = e.target;
            if (radio.tagName !== 'INPUT' || radio.type !== 'radio') {
                return;
            }
            if (!radio.closest(CONTAINER_SELECTOR)) {
                return;
            }
            if (radio !== previouslyCheckedRadio) {
                previouslyCheckedRadio = null;
                return;
            }

            previouslyCheckedRadio = null;
            const name = radio.name;
            const value = radio.value;
            const container = radio.closest('.deselect-singlechoice');

            // Defer the actual deselect so it runs after all synchronous click handlers
            // (including inline onclick handlers such as checkconditions(code, ...)).
            // This ensures our checkconditions('') call is the last one.
            setTimeout(() => {
                radio.checked = false;

                const javaField = document.getElementById('java' + name);
                if (javaField) {
                    javaField.value = '';
                }

                if (container) {
                    container.dispatchEvent(new CustomEvent('ls:singlechoiceDeselected', {
                        bubbles: true,
                        detail: { name, value }
                    }));
                }

                if (typeof checkconditions === 'function') {
                    checkconditions('', name, 'radio');
                }
            }, 0);
        }, true); // capture phase
    };

    return { init };
})();

$(document).on('ready pjax:scriptcomplete', function () {
    DeselectSinglechoiceScripts.init();
});

export default DeselectSinglechoiceScripts;