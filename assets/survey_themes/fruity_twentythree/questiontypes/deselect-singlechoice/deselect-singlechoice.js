/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 *
 * Deselect single-choice radio buttons
 *
 * Enables deselection of an already-selected radio button by clicking it again
 * (pointer) or pressing Space while it is focused (keyboard / AT).
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
 * Pointer path: uses mousedown to capture pre-click checked state, then a
 * native capture click listener (so stopPropagation in inline onclick handlers
 * such as cancelBubbleThis cannot block it). The actual deselect is deferred
 * via setTimeout so our checkconditions('') wins over any inline checkconditions
 * call with the previous value.
 *
 * Keyboard path: handled entirely in keydown. preventDefault suppresses the
 * synthetic click so the capture listener does not also fire, and checkconditions
 * can be called directly without deferral.
 */

const DeselectSinglechoiceScripts = (() => {
    let initialized = false;
    let previouslyCheckedRadio = null;

    const CONTAINER_SELECTOR = '.deselect-singlechoice.list-radio, .deselect-singlechoice.list-with-comment';
    const RADIO_SELECTOR     = '.deselect-singlechoice.list-radio input[type="radio"], .deselect-singlechoice.list-with-comment input[type="radio"]';
    const LABEL_SELECTOR     = '.deselect-singlechoice.list-radio label, .deselect-singlechoice.list-with-comment label';

    const recordCheckedState = (radio) => {
        previouslyCheckedRadio = (radio && radio.checked) ? radio : null;
    };

    const deselect = (radio) => {
        const name = radio.name;
        const value = radio.value;
        const container = radio.closest('.deselect-singlechoice');

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
    };

    const init = () => {
        if (initialized) {
            return;
        }
        initialized = true;

        // Pointer: direct click on the radio input circle
        $(document).on('mousedown', RADIO_SELECTOR, function () {
            recordCheckedState(this);
        });

        // Pointer: click via an associated label (label may be a sibling, not a parent).
        // mousedown fires on the label; the browser then synthesises a click on the input.
        $(document).on('mousedown', LABEL_SELECTOR, function () {
            const forId = this.getAttribute('for');
            recordCheckedState(forId ? document.getElementById(forId) : null);
        });

        // Keyboard / AT: Space on an already-checked radio.
        // Handled entirely in keydown; preventDefault suppresses the synthetic click
        // so the capture listener below does not also fire.
        $(document).on('keydown', RADIO_SELECTOR, function (e) {
            if (e.key !== ' ' || !this.checked) {
                return;
            }
            e.preventDefault();
            deselect(this);
        });

        // Pointer: native capture listener fires top-down before stopPropagation()
        // in inline onclick handlers (e.g. cancelBubbleThis in image-select questions).
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

            // Defer so our checkconditions('') runs after any inline onclick
            // handler that calls checkconditions with the previous value.
            setTimeout(() => deselect(radio), 0);
        }, true); // capture phase
    };

    return { init };
})();

$(document).on('ready pjax:scriptcomplete', function () {
    DeselectSinglechoiceScripts.init();
});

export default DeselectSinglechoiceScripts;