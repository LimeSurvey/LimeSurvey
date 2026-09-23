/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 *
 * ListRadio - Clear "other" text field on deselect
 *
 * Listens for the "ls:singlechoiceDeselected" custom event dispatched by
 * deselect-singlechoice.js. When the deselected radio was the "other" option ("-oth-"),
 * performs all cleanup needed across question types:
 *
 * - Standard list-radio: clears the other text input (#answer{name}othertext)
 * - Bootstrap buttons:   hides #div{name}other (adds ls-js-hidden) and clears
 *                        the auxiliary hidden field (#answer{name}othertextaux)
 *
 * Without this, LimeSurvey core would immediately re-select the "other" radio
 * because its keyup handler fires whenever the text field is non-empty.
 */

const DeselectSinglechoiceOtherScripts = (() => {
    let initialized = false;

    const init = () => {
        if (initialized) {
            return;
        }
        initialized = true;

        $(document).on('ls:singlechoiceDeselected', '.deselect-singlechoice.list-radio, .deselect-singlechoice.list-with-comment', function (e) {
            const { name, value } = e.originalEvent.detail;

            if (value !== '-oth-') {
                return;
            }

            // Standard list-radio: clear the visible other text input
            const otherTextField = document.getElementById('answer' + name + 'othertext');
            if (otherTextField) {
                otherTextField.value = '';
            }

            // Bootstrap buttons: hide the other text container and clear the aux field
            const otherContainer = document.getElementById('div' + name + 'other');
            if (otherContainer) {
                otherContainer.classList.add('ls-js-hidden');

                const otherTextAux = document.getElementById('answer' + name + 'othertextaux');
                if (otherTextAux) {
                    otherTextAux.value = '';
                }
            }
        });
    };

    return { init };
})();

$(document).on('ready pjax:scriptcomplete', function () {
    DeselectSinglechoiceOtherScripts.init();
});

export default DeselectSinglechoiceOtherScripts;