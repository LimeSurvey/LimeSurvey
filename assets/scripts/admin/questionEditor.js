/*
 * LimeSurvey (tm)
 * Copyright (C) 2012-2016 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v3 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
var LS = LS || {
    onDocumentReady: {}
};

$(document).on("ready pjax:scriptcomplete", function () {
    // Since save button is not inside the form, we need to trigger it manually.
    $('#save-button').on('click', function(ev) {
        ev.preventDefault();
        $('#edit-question-form').submit();
        return false;
    });

    // Init Ace script editor.
    $('.ace:not(.none)').ace({
        'mode' : 'javascript'
    });

    // Hide help tips by default.
    $('.question-option-help').hide();
});

/**
 * Update question attributes (general and advanced settings) when selecting question type.
 *
 * @param {string} questionType - One-letter string of question type
 * @param {string} generalSettingsUrl - URL to controller to fetch new HTML
 * @param {string} advancedSettingsUrl - URL to controller to fetch new HTML
 * @return {void}
 */
async function updateQuestionAttributes(questionType, generalSettingsUrl, advancedSettingsUrl) {
    // If same question type, do nothing.
    // Else, fetch new HTML from server.
    $('#ls-loading').show();

    const generalSettingsPromise = new Promise((resolve, reject) => {
        $.ajax({
            url: generalSettingsUrl,
            method: "GET",
            data: {questionType: questionType},
            dataType: "html",
            success: (data) => {
                resolve(data);
            },
            error: (data) => {
                reject(data);
            }
        });
    });
    const advancedSettingsPromise = new Promise((resolve, reject) => {
        $.ajax({
            url: advancedSettingsUrl,
            method: "GET",
            data: {questionType: questionType},
            dataType: "html",
            success: (data) => {
                resolve(data);
            },
            error: (data) => {
                reject(data);
            }
        });
    });
    try {
        const [html, html2] = await Promise.all([generalSettingsPromise, advancedSettingsPromise]);
        $("#general-settings").replaceWith(html);
        $("#advanced-settings").replaceWith(html2);
        $('.question-option-help').hide();
        $('#ls-loading').hide();
    } catch (ex) {
        $('#ls-loading').hide();
        alert('Internal error: ' + ex);
    }
}

function updateQuestionTemplateOptions(questionType) {
}
