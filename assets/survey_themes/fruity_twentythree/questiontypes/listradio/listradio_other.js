/**
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 *
 * ListRadio - Clear "other" text field on deselect
 *
 * Listens for the "ls:singlechoiceDeselected" custom event dispatched by
 * listradio.js. When the deselected radio was the "other" option ("-oth-"),
 * the accompanying text input is cleared so LimeSurvey core does not
 * immediately re-select it via its keyup handler.
 */

const ListRadioOtherScripts = (() => {
    let initialized = false;

    const init = () => {
        if (initialized) {
            return;
        }
        initialized = true;

        $(document).on('ls:singlechoiceDeselected', '.deselect-singlechoice.list-radio', function (e) {
            const { name, value } = e.originalEvent.detail;

            if (value !== '-oth-') {
                return;
            }

            const otherTextField = document.getElementById('answer' + name + 'othertext');
            if (otherTextField) {
                otherTextField.value = '';
            }
        });
    };

    return { init };
})();

$(document).on('ready pjax:scriptcomplete', function () {
    ListRadioOtherScripts.init();
});

export default ListRadioOtherScripts;