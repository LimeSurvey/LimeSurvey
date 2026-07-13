/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 *
 * ListRadio - Deselectable single-choice radio buttons
 *
 * Enables deselection of an already-selected radio button by clicking it again.
 * Applies to list-radio question containers that also carry the CSS class
 * "deselect-singlechoice", added by the fruity_twentythree theme when the
 * "deselectsinglechoice" theme option is enabled.
 *
 * When a radio is deselected, a custom "ls:singlechoiceDeselected" event is
 * dispatched on the question container with detail { name, value }, allowing
 * other modules to react (e.g. clearing the "other" text field).
 */

const ListRadioScripts = (() => {
    let initialized = false;
    let previouslyCheckedRadio = null;

    const recordCheckedState = (radio) => {
        previouslyCheckedRadio = (radio && radio.checked) ? radio : null;
    };

    const init = () => {
        if (initialized) {
            return;
        }
        initialized = true;

        // Direct click on the radio input circle
        $(document).on('mousedown', '.deselect-singlechoice.list-radio input[type="radio"]', function () {
            recordCheckedState(this);
        });

        // Click via an associated label (label is a sibling, not a parent)
        // mousedown fires on the label; the browser then synthesises a click on the input
        $(document).on('mousedown', '.deselect-singlechoice.list-radio label', function () {
            const forId = this.getAttribute('for');
            recordCheckedState(forId ? document.getElementById(forId) : null);
        });

        $(document).on('click', '.deselect-singlechoice.list-radio input[type="radio"]', function () {
            if (this !== previouslyCheckedRadio) {
                previouslyCheckedRadio = null;
                return;
            }

            this.checked = false;
            previouslyCheckedRadio = null;

            const name = this.name;
            const value = this.value;

            // Update the hidden java field used by the expression manager
            const javaField = document.getElementById('java' + name);
            if (javaField) {
                javaField.value = '';
            }

            // Notify other modules that a radio was deselected
            const container = this.closest('.deselect-singlechoice');
            if (container) {
                container.dispatchEvent(new CustomEvent('ls:singlechoiceDeselected', {
                    bubbles: true,
                    detail: { name, value }
                }));
            }

            // Notify the expression manager about the cleared value
            if (typeof checkconditions === 'function') {
                checkconditions('', name, 'radio');
            }
        });
    };

    return { init };
})();

$(document).on('ready pjax:scriptcomplete', function () {
    ListRadioScripts.init();
});

export default ListRadioScripts;