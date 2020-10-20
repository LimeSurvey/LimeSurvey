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

'use strict';

/* globals $, alert, window, document, console, LS, duplicatesubquestioncode */
/* globals strCantDeleteLastAnswer, lspickurl, strNoLabelSet */
/* globals cancel, lanameurl, langs, languagecount, lasaveurl */
/* globals lsdetailurl, ok */
/* globals saveaslabletitl, sCheckLabelURL, sLabelSetName */

// eslint-disable-next-line no-use-before-define
//LS = LS || {
  //onDocumentReady: {},
//};

// @todo Don't use globals.
/** @type {array} Used in setLabel */
let flag = [];
/** @type {bool} Used in ajaxcheckdup */
let check = true;

$(document).on('ready pjax:scriptcomplete', () => {
  // Since save button is not inside the form, we need to trigger it manually.
  $('#save-button').on('click', (ev) => {
    ev.preventDefault();
    $('#edit-question-form').submit();
    return false;
  });

  // Init Ace script editor.
  $('.ace:not(.none)').ace({
    mode: 'javascript',
  });

  // Hide help tips by default.
  $('.question-option-help').hide();

  // Hide all language except the selected one.
  $('.lang-switch-button').on('click', function langSwitchOnClick() {
    const lang = $(this).data('lang');
    const langClass = `.lang-${lang}`;
    $('.lang-hide').hide();
    $(langClass).show();
  });
});

/**
 * Update question attributes (general and advanced settings) when selecting question type.
 *
 * @param {string} questionType - One-letter string of question type
 * @param {string} generalSettingsUrl - URL to controller to fetch new HTML
 * @param {string} advancedSettingsUrl - URL to controller to fetch new HTML
 * @return {void}
 */
// eslint-disable-next-line no-unused-vars
async function updateQuestionAttributes(questionType, generalSettingsUrl, advancedSettingsUrl) {  // jshint ignore:line
  // If same question type, do nothing.
  // Else, fetch new HTML from server.
  $('#ls-loading').show();

  const generalSettingsPromise = new Promise((resolve, reject) => {
    $.ajax({
      url: generalSettingsUrl,
      method: 'GET',
      data: { questionType },
      dataType: 'html',
      success: (data) => {
        resolve(data);
      },
      error: (data) => {
        reject(data);
      },
    });
  });
  const advancedSettingsPromise = new Promise((resolve, reject) => {
    $.ajax({
      url: advancedSettingsUrl,
      method: 'GET',
      data: { questionType },
      dataType: 'html',
      success: (data) => {
        resolve(data);
      },
      error: (data) => {
        reject(data);
      },
    });
  });
  try {
    const [html, html2] = await Promise.all([generalSettingsPromise, advancedSettingsPromise]);
    $('#general-settings').replaceWith(html);
    // TODO: Double check HTML injected here. Extra div?
    $('#advanced-options-container').replaceWith(html2);
    $('.question-option-help').hide();
    $('#ls-loading').hide();
  } catch (ex) {
    $('#ls-loading').hide();
    // TODO: How to show internal errors?
    // eslint-disable-next-line no-alert
    alert(`Internal error: ${ex}`);
  }
}

// todo
// function updateQuestionTemplateOptions(questionType) {
// }

/**
 * BELOW IS FROM LS3 assets/scripts/admin/subquestions.js
 */

/**
 * @return {void}
 */
function updateRowProperties() {
  const sID = $('input[name=sid]').val();
  const gID = $('input[name=gid]').val();
  const qID = $('input[name=qid]').val();

  /**
   * This function adjusts the alternating table rows
   * if the list changed
   *
   * @param {object} $elm
   * @param {object} $attr
   * @param {object} $attrValue
   * @return {void}
   * @todo Define in function scope?
   */
  function updateIfEmpty($elm, $attr, $attrValue) {
    if ($elm.attr($attr) === '') {
      $elm.attr($attr, $attrValue);
    }
  }

  $('.answertable tbody').each(function loopTable() {
    const info = $(this).closest('table').attr('id').split('_');
    const language = info[1];
    const scaleId = info[2];
    $(this).children('tr').each(function loopTr() {
      const uniqueRowId = $(this).data('common-id').split('_').shift();

      if (!$(this).hasClass(`row_${uniqueRowId}`)) {
        $(this).addClass(`row_${uniqueRowId}`);
      }

      updateIfEmpty($(this).find('.oldcode'), 'name', `oldcode_${uniqueRowId}_${scaleId}`);
      updateIfEmpty($(this).find('.code'), 'id', `code_${uniqueRowId}_${scaleId}`);
      updateIfEmpty($(this).find('.code'), 'name', `code_${uniqueRowId}_${scaleId}`);
      updateIfEmpty($(this).find('.answer'), 'id', `answer_${language}_${uniqueRowId}_${scaleId}`);
      updateIfEmpty($(this).find('.answer'), 'name', `answer_${language}_${uniqueRowId}_${scaleId}`);
      updateIfEmpty($(this).find('.assessment'), 'id', `assessment_${uniqueRowId}_${scaleId}`);
      updateIfEmpty($(this).find('.assessment'), 'name', `assessment_${uniqueRowId}_${scaleId}`);
      // Newly inserted row editor button
      $(this).find('.editorLink').attr(
        'href',
        `javascript:start_popup_editor(
          'answer_${language}_${uniqueRowId}_${scaleId}','[Answer:](${language})','${sID}','${gID}','${qID}','editanswer','editanswer'
        )`
      );
      $(this).find('.editorLink').attr('id', `answer_${language}_${uniqueRowId}_${scaleId}_ctrl`);
      $(this).find('.btneditanswerena').attr('id', `answer_${language}_${uniqueRowId}_${scaleId}_popupctrlena`);
      $(this).find('.btneditanswerena').attr('name', `answer_${language}_${uniqueRowId}_${scaleId}_popupctrlena`);
      $(this).find('.btneditanswerdis').attr('id', `answer_${language}_${uniqueRowId}_${scaleId}_popupctrldis`);
      $(this).find('.btneditanswerdis').attr('name', `answer_${language}_${uniqueRowId}_${scaleId}_popupctrldis`);
    });
  });
}

/**
 * @param {event} event
 * @param {object} ui
 * @return {void}
 */
function startmove(event, ui) {
  const $that = ui.item;
  const oldindex = Number($that.parent().children().index($that) + 1);
  $that.data('oldindex', oldindex);
}

/**
 * @return {void}
 */
function endmove() {
  updateRowProperties();
}

/**
 * @param {event} event
 * @param {object} ui ??
 * @return {void}
 */
function aftermove(event, ui) {
  // But first we have change the sortorder in translations, too
  const $that = ui.item;
  const newindex = Number($that.parent().children().index($that) + 1);
  const oldindex = $that.data('oldindex');
  const languages = langs.split(';');

  LS.ld.forEach(languages, (curLanguage, x) => {
    if (x > 0) {
      // const tablebody = $(`#tabpage_${languages[x]}`).find('tbody');
      if (newindex < oldindex) {
        $(`#tabpage_${languages[x]} tbody tr:nth-child(${newindex})`).before($(`#tabpage_${languages[x]} tbody tr:nth-child(${oldindex})`));
      } else {
        $(`#tabpage_${languages[x]} tbody tr:nth-child(${newindex})`).after($(`#tabpage_${languages[x]} tbody tr:nth-child(${oldindex})`));
        // tablebody.find('.row_'+newindex).after(tablebody.find('.row_'+oldindex));
      }
    }
  });
}

/**
 * Bind relevance equation to expand on click (only once)
 *
 * @return {void}
 */
function bindExpandRelevanceEquation() {
  $('.relevance').off('click').on('click', () => {
    $('#rel-eq-th').toggleClass('col-md-1 col-md-4', 'fast');
    $('.relevance').data('toggle', '').tooltip('destroy');
    $('.relevance').off('click');
  });
}

function addInputPredefined(i) {
  const $elDatas = $('#add-input-javascript-datas');
  const scaleId = $('#current_scale_id').val();
  // We build the datas for the request
  const datas = {
    surveyid: $elDatas.data('surveyid'),
    gid: $elDatas.data('gid'),
    codes: JSON.stringify({lbl_1: 'eins'}),  // jshint ignore:line
    scale_id: scaleId,  // jshint ignore:line
    position: i,
    type: 'subquestion',
    languages: JSON.stringify($elDatas.data('languages').join(';')),
  };
  // We get the HTML of the new row to insert
  return $.ajax({
    type: 'GET',
    contentType: 'json',
    url: $elDatas.data('url'),
    data: datas,
  });
}
/**
 * @return {boolean} true if relevance equation field is expanded
 */
function relevanceIsExpanded() {
  return $('#rel-eq-th').hasClass('col-md-4');
}

/**
 * Bind click to expand relevance equation
 * if not already expanded.
 *
 * @return {void}
 */
function bindClickIfNotExpanded() {
  if (!relevanceIsExpanded()) {
    bindExpandRelevanceEquation();
    // Activate tooltip
    window.LS.doToolTip();
  }
}

/**
 * Get toolrip data for relevance equation.
 * If expanded, returns empty string
 *
 * @return {string}
 */
//function getRelevanceToolTip() {
  //const relevanceTooltip = !relevanceIsExpanded() ? `data-toggle="tooltip" data-title="${clickToExpand}"` : '';
  //return relevanceTooltip;
//}

/**
 * Delete row?
 *
 * @param {object} jQueryItem
 * @return {void}
 */
function deleteSubquestionRow(jQueryItem) {
  if ($(jQueryItem).is('[id]')) {
    const rowinfo = $(jQueryItem).attr('id').split('_');
    // TODO: What is rowinfo[2]?
    $('#deletedqids').val(`${$('#deletedqids').val()} ${rowinfo[2]}`);
  }
}

/**
 * Delete subquestion row.
 * Executed when user click "Delete" button.
 *
 * @param {event} e
 * @return {void}
 */
function deleteSubquestionInput(e) {
  e.preventDefault();
  const target = e.target;
  // 1.) Check if there is at least one answe
  let position;
  const countanswers = $(target).closest('tbody').children('tr').length; // Maybe use class is better
  if (countanswers > 1) {
    // 2.) Remove the table row
    const classes = $(target).closest('tr').attr('class').split(' ');
    LS.ld.forEach(classes, (curClass) => {
      if (curClass.substr(0, 3) === 'row') {
        position = curClass.substr(4);
      }
    });

    const info = $(target).closest('table').attr('id').split('_');
    const scaleId = info[2];
    const languages = langs.split(';');

    LS.ld.forEach(languages, (curLanguage, x) => {
      const tablerow = $(`#tabpage_${languages[x]}`).find(`#answers_${languages[x]}_${scaleId} .row_${position}`);
      if (x === 0) {
        tablerow.fadeTo(400, 0, function fadeAndRemove() {
          $(target).remove();
          updateRowProperties();
        });
      } else {
        tablerow.remove();
      }
      deleteSubquestionRow($(tablerow));
    });
  } else {
    // TODO: why block?
    // TODO: application/views/admin/survey/Question/_subQuestionsAndAnwsersJsVariables.php
    $.blockUI({ message: `<p><br/>${strCantDeleteLastAnswer}</p>` });
    setTimeout($.unblockUI, 1000);
  }
  updateRowProperties();
}

/**
 * Delete answer option row.
 * Executed when user clicks "Delete" button.
 *
 * @param {event} e
 * @return {void}
 */
function deleteAnswerOptionInput(e) {
  e.preventDefault();
}

/**
 * add addinputQuickEdit : for usage with the quickAdd Button
 *
 * @param {object} $currentTable
 * @param {string} language
 * @param {bool} first
 * @param {number} scaleId
 * @param {array} codes
 * @return {Promise}
 */
function addinputQuickEdit($currentTable, language, first, scaleId, codes) {
  codes = codes || [];
  const $elDatas = $('#add-input-javascript-datas'); // This hidden element  on the page contains various datas for this function
  const $url = $elDatas.data('quickurl'); // Url for the request
  const $errormessage = $elDatas.data('errormessage'); // the error message if the AJAX request failed
  const $defer = $.Deferred();
  let $codes;
  let datas;

  // We get all the subquestion codes currently displayed
  // We get all the subquestion codes currently displayed
  if ($currentTable.find('.code').length > 0) {
    $currentTable.find('.code').each(function () {
      codes.push($(this).val());
    });
  } else {
    $currentTable.find('.code-title').each(function () {
      codes.push($(this).text().trim());
    });
  }

  // We convert them to json for the request
  $codes = JSON.stringify(codes);
  // We build the datas for the request
  datas = {
    codes: $codes,
    // In $elDatas.data('scale-id') ?
    scale_id: scaleId,  // jshint ignore:line
    type: 'subquestion',
    position: null,
    first,
    language,
  };

  // We get the HTML of the new row to insert
  $.ajax({
    // TODO: We don't update DB, no need post
    type: 'POST',
    url: $url,
    data: datas,
    success(htmlrow) {
      const $langTable = $(`#answers_${language}_${scaleId}`);
      $defer.resolve({ lng: language, langtable: $langTable, html: htmlrow });
    },
    error(html, status) {
      alert($errormessage);
      $defer.reject([html, status, $errormessage]);
    },
  });
  return $defer.promise();
}

/**
 * Add one subquestion row using Ajax.
 *
 * @param {event} e
 * @return {void}
 */
function addSubquestionInput(e) {
  e.preventDefault();
  const target = e.target;
  const $that = $(target); // The "add" button
  const $currentRow = $that.closest('.row-container'); // The row containing the "add" button
  const $currentTable = $that.closest('.answertable');
  const $commonId = $currentRow.data('common-id'); // The common id of this row in the other languages
  const $elDatas = $('#add-subquestion-input-javascript-datas'); // This hidden element  on the page contains various datas for this function
  const $url = $elDatas.data('url'); // Url for the request
  const $errormessage = $elDatas.data('errormessage'); // the error message if the AJAX request failed
  const $languages = JSON.stringify(langs); // The languages
  let $codes;
  let datas;

  console.log('$url', $url);

  // We get all the subquestion codes currently displayed
  const codes = [];
  $currentTable.find('.code').each(function () {
    codes.push($(target).val());
  });

  // We convert them to json for the request
  $codes = JSON.stringify(codes);

  // We build the datas for the request
  datas = `surveyid=${$elDatas.data('surveyid')}`;
  datas += `&gid=${$elDatas.data('gid')}`;
  datas += `&qid=${$elDatas.data('qid')}`;
  datas += `&codes=${$codes}`;
  datas += `&scale_id=${$(target).find('i').data('scale-id')}`;
  datas += '&position=0';
  datas += `&languages=${$languages}`;

  const rebindClickHandler = () => {
    // @TODO answer option
    $('.btnaddsubquestion').off('click.subquestions').on('click.subquestions', addSubquestionInput);
    $('.btndelsubquestion').off('click.subquestions').on('click.subquestions', deleteSubquestionInput);
  };

  // We get the HTML of the different rows to insert  (one by language)
  $.ajax({
    type: 'GET',
    url: $url,
    data: datas,
    success(arrayofhtml) {
      // arrayofhtml is a json object containing the different HTML row by language
      // eg: {"en":"{the html of the en row}", "fr":{the html of the fr row}}

      // We insert each row for each language
      $.each(arrayofhtml, (lang, htmlRow) => {
        const $elRowToUpdate = $(`#row_${lang}_${$commonId}`); // The row for the current language
        $elRowToUpdate.after(htmlRow); // We insert the HTML of the new row after this one
      });
      rebindClickHandler();
    },
    error() {
      alert($errormessage);
    },
  });
}

/**
 * Add one answer option row using Ajax.
 *
 * @param {event} e
 * @return {void}
 */
function addAnswerOptionInput() {
  // todo
}

//function updatecodes() {
//}

/**
 * @param {*} mixedVar
 * @return {bool}
 */
function isNumeric(mixedVar) {
  return (typeof (mixedVar) === 'number' || typeof (mixedVar) === 'string') && mixedVar !== '' && !isNaN(mixedVar);
}

/**
 * @param {string} sSourceCode
 * @return {string}
 */
function getNextCode(sSourceCode) {  // jshint ignore: line
  const sourcecode = sSourceCode;
  let i = 1;
  let found = true;
  let foundnumber = -1;
  const sclength = sourcecode.length;
  while (i <= sclength && found === true) {
    found = isNumeric(sourcecode.substr(sclength - i, i));
    if (found) {
      foundnumber = sourcecode.substr(sclength - i, i);
      i++;
    }
  }
  if (foundnumber === -1) {
    return (sourcecode);
  }

  foundnumber++;
  foundnumber += '';
  const result = sourcecode.substr(0, sclength - foundnumber.length) + foundnumber;
  return (result);
}

/**
* Check if all existing codes are unique
* If sNewValue is not empty then only sNewValue is checked for uniqueness against the existing codes
*
* @param sNewValue
* @returns {bool} False if codes are not unique
*/
function areCodesUnique(sNewValue) {  // jshint ignore: line
  const languages = langs.split(';');
  let dupefound = false;
  $(`#tabpage_${languages[0]} .answertable tbody`).each(function () {
    let codearray = [];
    $(this).find('tr .code').each(function () {
      codearray.push($(this).val());
    });
    if (sNewValue !== '') {
      codearray = window.LS.getUnique(codearray);
      codearray.push(sNewValue);
    }
    if (window.LS.arrHasDupes(codearray)) {
      dupefound = true;
    }
  });
  if (dupefound) {
    return false;
  }
}

/**
 * @return {void}
 */
//function popupeditor() {
  //const inputId = $(this).parent().find('.answer').attr('id');
  //start_popup_editor(inputId); // jshint ignore:line
//}

/**
 * @return {bool}
 */
function codeDuplicatesCheck() {
  // $('.code[data-toggle="tooltip"]').data('toggle', '').tooltip('destroy');
  const languages = langs.split(';');
  let cansubmit = true;
  $(`#tabpage_${languages[0]} .answertable tbody`).each(function () {
    const codearray = [];
    $(this).find('tr .code').each(function () {
      codearray.push($(this).val().toLowerCase());
    });
    const theDuplicate = window.LS.arrHasDupesWhich(codearray);
    if (theDuplicate !== false) {
      $('#error-modal .modal-body-text').html(duplicatesubquestioncode);
      $('#error-modal').modal();
      cansubmit = false;
    }
  });
  console.ls.log(`cansubmit: ${cansubmit}`);
  return cansubmit;
}

/**
 * @todo Does what?
 * @return {void}
 */
function lsBrowserDestruct() {
  $('#labelsets').select2('destroy');
  $('#labelsetpreview').empty();
}

// previews the labels in a label set after selecting it in the select box
// previews the labels in a label set after selecting it in the select box
function lspreview(lid) {
  const surveyid = $('input[name=sid]').val();
  return $.ajax({
    url: lsdetailurl,
    data: { sid: surveyid, lid },
    cache: true,
    success(json) {
      console.ls.log('lspreview', json);
      if (json.languages === []) {
        console.ls.console.warn('NOTHING TO RENDER!', json);
        return;
      }

      const $liTemplate = $('<li role="presentation"></li>');
      const $aTemplate = $('<a data-toggle="tab"></a>');
      const $tabTodyTemplate = $('<div></div>');
      const $listTemplate = $('<div class="list-group selector_label-list"></div>');
      const $listItemTemplate = $('<div class="list-group-item row selector_label-list-row"></div>');
      const $tabindex = $('<ul class="nav nav-tabs" role="tablist"></ul>');
      const $tabbody = $('<div class="tab-content" style="max-height: 50vh; overflow:auto;"></div>');

      console.ls.group('LanguageParsing');
      const i = 0;
      $.each(json.languages, (language, languageName) => {
        console.ls.log('Language', language, languageName);
        const $linkItem = $aTemplate.clone();
        const $bodyItem = $tabTodyTemplate.clone();
        var $itemList = $listTemplate.clone();

        const classLink = i === 0 ? 'active' : '';
        const classBody = i === 0 ? 'tab-pane tab-pane fade in active' : 'tab-page tab-pane fade';

        $linkItem.addClass(classLink).attr('href', `#language_${language}`).text(languageName);
        $liTemplate.clone().append($linkItem).appendTo($tabindex);

        $bodyItem.addClass(classBody).attr('id', `language_${language}`);
        $tabbody.append($bodyItem);

        console.ls.group('ParseLabelSet');

        const labelSet = json.results[language];
        console.ls.log('LabelSet', labelSet);

        $itemList = $listTemplate.clone();

        console.ls.group('ParseLabels');
        $.each(labelSet.labels, (i, label) => {
          console.ls.log('Label', i, label);
          // Label title is not concatenated directly because it may have non-encoded HTML
          const $labelTitleDiv = $('<div class="col-md-8"></div>');
          $labelTitleDiv.text(label.title);
          const $listItem = $listItemTemplate.clone();
          $listItem.append(`<div class="col-md-3 text-right" style="border-right: 4px solid #cdcdcd">${label.code}</div>`);
          $listItem.append($labelTitleDiv);
          $listItem.append('<div class="col-md-1"></div>');
          $listItem.attr('data-label', JSON.stringify(label));
          $itemList.append($listItem);
        });

        console.ls.groupEnd('ParseLabels');
        $bodyItem.append(`<h4>${labelSet.label_name}</h4>`);  // jshint ignore: line
        $itemList.appendTo($bodyItem);

        console.ls.groupEnd('ParseLabelSet');
      });
      console.ls.groupEnd('LanguageParsing');
      $('#labelsetpreview').empty();
      $('<div></div>').append($tabindex).append($tabbody).appendTo($('#labelsetpreview'));
      $tabindex.find('li').first().find('a').trigger('click');
    },
  });
}

/**
 * @param {event} e
 * @return {void}
 */
function lsbrowser(e) {
  const scaleId = $(e.relatedTarget).data('scale-id');
  $('body').append(`<input type="hidde" id="current_scale_id" value="${scaleId}" name="current_scale_id" />`);

  $('#labelsets').select2();
  $('#labelsetpreview').html('');
  const surveyid = $('input[name=sid]').val();
  $.ajax({
    url: lspickurl,
    data: { sid: surveyid, match: 1 },
    success(jsonString) {
      console.ls.log('combined String', jsonString);
      if (jsonString.success !== true) {
        $('#labelsetpreview').html(`<p class='alert'>${strNoLabelSet}</p>`);
        $('#btnlsreplace').addClass('disabled');
        $('#btnlsinsert').addClass('disabled');
        $('#btnlsreplace').attr('disabled', 'disabled');
        $('#btnlsinsert').attr('disabled', 'disabled');
      } else {
        $('#labelsets').find('option').each((i, option) => { if ($(option).attr('value')) { $(option).remove(); } });
        console.ls.group('SelectParsing');
        console.ls.log('allResults', jsonString.labelsets);
        $.each(jsonString.labelsets, (i, item) => {
          console.log('SelectItem', item);
          const newOption = $(`<option value="${item.lid}">${item.label_name}</option>`);  // jshint ignore: line
          console.ls.log('newOption', newOption);
          $('#labelsets').append(newOption).trigger('change');
        });
        console.ls.groupEnd('SelectParsing');
      }
    },
  });

  $('#labelsets').on('change', function () {
    const value = $(this).val();
    if (parseFloat(value) === value) {
      lspreview(value);
    }
  });
}

/**
 * @param {string} type ???
 * @return {void}
 */
function transferlabels(type) {
  //const surveyid = $('input[name=sid]').val();
  //const languages = langs.split(';');
  //const labels = [];
  const scaleId = $('#current_scale_id').val();

  addInputPredefined(1).then((result) => {
    console.ls.log(result);
    $.each(result, (lng, row) => {
      const $table = $(`#answers_${lng}_${scaleId}`);

      if (type === 'replace') {
        $table.find('tbody').find('tr').each((i, tableRow) => {
          deleteSubquestionRow($(tableRow));
          $(tableRow).remove();
        });
      }

      $('#labelsetpreview').find(`#language_${lng}`).find('.selector_label-list').find('.selector_label-list-row')
        .each((i, item) => {
          try {
            const label = $(item).data('label');
            const $row = $(row);
            const $tr = $row.eq(4);
            const randId = `new${Math.floor(Math.random() * 10000)}`;

            $tr.attr('data-common-id', $tr.attr('data-common-id').replace('/new[0-9]{3,6}/', randId));
            $tr.attr('id', $tr.attr('id').replace('/new[0-9]{3-5}/', randId));

            $row.find('input').each((j, inputField) => {
              $(inputField).attr('name', $(inputField).attr('name').replace(/new[0-9]{3,6}/, randId));
              $(inputField).attr('id', $(inputField).attr('id').replace(/new[0-9]{3,6}/, randId));
            });

            if ($row.find('td.code-title').find('input[type=text]').length > 0) {
              $row.find('td.code-title').find('input[type=text]').val(label.code);
            } else {
              $row.find('td.code-title').text(label.code);
            }
            if ($row.find('td.relevance-equation').find('input[type=text]').length > 0) {
              $row.find('td.relevance-equation').find('input[type=text]').val(1);
            } else {
              // ??
            }

            $row.find('td.code-title').find('input[type=text]').val(label.code);
            $row.find('td.subquestion-text').find('input[type=text]').val(label.title);
            $table.find('tbody').append($row);
          } catch (e) {
            console.ls.error(e);
          }
        });

      $('.tab-page:first .answertable tbody').sortable('refresh');
      updateRowProperties();
      $('#labelsetbrowserModal').modal('hide');
      $('#current_scale_id').remove();
    });
  });
}

/**
 * Quick-add subquestions/answers
 *
 * @global langs
 *
 * @param {int} scaleId
 * @param {string} addOrReplace - Either 'add' or 'replace'
 * @return {void}
 */
function quickaddlabels(scaleId, addOrReplace, tableId) {
  console.ls.log('quickaddlabels');
  //const sID = $('input[name=sid]').val();
  //const gID = $('input[name=gid]').val();
  //const qID = $('input[name=qid]').val();
  const codes = [];
  const closestTable = $(`#${tableId}`);
  const lsreplace = (addOrReplace === 'replace');

  if (lsreplace) {
    $(`.answertable:eq(${scaleId}) tbody tr`).each(function () {
      const aRowInfo = this.id.split('_');
      $('#deletedqids').val(`${$('#deletedqids').val()} ${aRowInfo[2]}`);
    });
  }

  if (closestTable.find('.code').length < 0) {
    closestTable.find('.code-title').each(function () {
      codes.push($(this).text());
    });
  } else {
    closestTable.find('.code').each(function () {
      codes.push($(this).val());
    });
  }

  const languages = langs.split(';');
  const promises = [];
  const answers = [];
  let separatorchar;
  const lsrows = $('#quickaddarea').val().split('\n');
  const allrows = $(`.answertable:eq(${scaleId}) tbody tr`).length;

  if (lsrows[0].indexOf('\t') === -1) {
    separatorchar = ';';
  } else {
    separatorchar = '\t';
  }

  let numericSuffix = '';
  let n = 1;
  let numeric = true;
  let currentCharacter;
  const codeSigil = (codes[0] !== undefined ? codes[0].split('') : ('001').split(''));
  while (numeric === true && n <= codeSigil.length) {
    currentCharacter = codeSigil.pop(); // get the current character
    if (!isNaN(Number(currentCharacter))) // check if it's numerical
    {
      numericSuffix = `${currentCharacter}${numericSuffix}`; // store it in a string
      n++;
    } else {
      numeric = false; // At first non numeric character found, the loop is stoped
    }
  }
  // Sometimes "0" is interpreted as NaN so test if it's just a missing Zero
  if (isNaN(Number(currentCharacter))) {
    codeSigil.push(currentCharacter);
  }

  LS.ld.forEach(lsrows, (value, k) => {
    const thisrow = value.splitCSV(separatorchar);

    if (thisrow.length <= languages.length) {
      let qCode = (parseInt(k) + 1);
      if (lsreplace === false) {
        qCode += (parseInt(allrows));
      }
      while (qCode.toString().length < numericSuffix.length) {
        qCode = `0${qCode}`;
      }
      thisrow.unshift(codeSigil.join('') + qCode);
    } else {
      thisrow[0] = thisrow[0].replace(/[^A-Za-z0-9]/g, '').substr(0, 20);
    }
    const quid = `new${Math.floor(Math.random() * 10000)}`;

    LS.ld.forEach(languages, (language, x) => {
      if (typeof thisrow[parseInt(x) + 1] === 'undefined') {
        thisrow[parseInt(x) + 1] = thisrow[1];
      }

      if (!answers[language]) {
        answers[language] = [];
      }
      if (lsreplace) {
        $(`#answers_${language}_${scaleId} tbody`).empty();
      }
      answers[language].push(
        { text: thisrow[(parseInt(x) + 1)], code: thisrow[0], quid },
      );
    });

    // $('#answers_'+languages[x]+'_'+scaleId+' tbody').append(tablerows);
  });

  LS.ld.forEach(languages, (language, x) => {
    // Unbind any previous events
    $(`#answers_${language}_${scaleId} .btnaddanswer`).off('click.subquestions');
    $(`#answers_${language}_${scaleId} .btndelanswer`).off('click.subquestions');
    $(`#answers_${language}_${scaleId} .btnaddsubquestion`).off('click.subquestions');
    $(`#answers_${language}_${scaleId} .btndelsubquestion`).off('click.subquestions');
    $(`#answers_${language}_${scaleId} .answer`).off('focus');
    $(`#answers_${language}_${scaleId} .btnaddanswer`).on('click.subquestions', addAnswerOptionInput);
    $(`#answers_${language}_${scaleId} .btndelanswer`).on('click.subquestions', deleteAnswerOptionInput);
    $(`#answers_${language}_${scaleId} .btnaddsubquestion`).on('click.subquestions', addSubquestionInput);
    $(`#answers_${language}_${scaleId} .btndelsubquestion`).on('click.subquestions', deleteSubquestionInput);

    promises.push(
      addinputQuickEdit(closestTable, language, (x === 0), scaleId, codes),
    );
  });

  $.when.apply($, promises).done(
    function () {
      /* $('#quickadd').dialog('close'); */
      $.each(arguments, (i, item) => {
        $.each(answers[item.lng], (j, row) => {
          const { html } = item;
          const htmlQuid = html.replace('/({{quid_placeholder}})/g', row.quid);
          const htmlRowObject = $(htmlQuid);
          htmlRowObject.find('input.answer').val(row.text);
          if (htmlRowObject.find('input.code').length > 0) {
            htmlRowObject.find('input.code').val(row.code);
          } else {
            htmlRowObject.find('td.code-title').text(row.code);
          }
          htmlRowObject.find('td.relevance-equation').find('input').val(1);

          item.langtable.find('tbody').append(htmlRowObject);
        });
      });
      $('#quickaddarea').val('');
      $('.tab-page:first .answertable tbody').sortable('refresh');
      updateRowProperties();
      $('#quickaddModal').modal('hide');
      bindClickIfNotExpanded();
    },
    function () {
      console.ls.log(arguments);
      /* $('#quickadd').dialog('close'); */
      $('#quickaddarea').val('');
      $('.tab-page:first .answertable tbody').sortable('refresh');
      updateRowProperties();
      $('#quickaddModal').modal('hide');
      bindClickIfNotExpanded();
    },
  );
}

/**
 * @return {void}
 */
function getLabel() {
  // TODO
  //const answer_table = $(this).parent().children().eq(0);
  //const scaleId = window.LS.removechars($(this).attr('id'));

  // $('#saveaslabel').dialog('open');
  updateRowProperties();
}

/**
 * @param {event} event
 * @return {void}
 */
function setlabel(event) {
  const target = event.target;
  switch ($(target).attr('id')) {
    case 'newlabel':
      if (!flag[0]) {
        $('#lasets').parent().remove();
        $(target).parent().after(`<p class="label-name-wrapper"><label for="laname">${sLabelSetName}:</label> ` +
          '<input type="text" name="laname" id="laname"></p>');
        flag[0] = true;
        flag[1] = false;
      }
      break;

    case 'replacelabel':
      if (!flag[1]) {
        $('#laname').parent().remove();
        $(target).parent().after('<p class="label-name-wrapper"><select name="laname" id="lasets"><option value=""></option></select></p>');
        $.getJSON(lanameurl, (data) => {
          $.each(data, (key, val) => {
            $('#lasets').append(`<option value="${key}">${val}</option>`);
          });
        });
        $('#lasets option[value=""]').remove();
        flag[1] = true;
        flag[0] = false;
      }
      break;
  }
}

/**
 * @return {void}
 */
function ajaxcheckdup() {
  check = true; // set check to true everytime on call
  return jQuery.getJSON(lanameurl, (data) => {
    $.each(data, (key, val) => {
      $('#saveaslabelModal').modal('hide');
      $('#dialog-confirm-replaceModal').modal('hide');

      if ($('#laname').val() === val) {
        if ($('#dialog-duplicate').is(':visible')) {
          $('#dialog-duplicate').effect('pulsate', { times: 3 }, 3000);
        } else {
          $('#dialog-duplicate').show();
        }
        check = false;
        return false;
      }
    });
  });
}

/**
 * @return {void}
 */
function ajaxreqsave() {
  // todo: scale id is not defined
  /*
  const lid = $('#lasets').val() ? $('#lasets').val() : 0;
  // get code for the current scale
  const code = [];
  if ($('.code').length > 0) { // Deactivated survey
    $('.code').each(function () {
      if ($(this).attr('id').substr(-1) === scaleId) code.push($(this).val());
    });
  } else { // Activated survey
    $('.answertable input[name^="code_"]').each(function () {
      if ($(this).attr('name').substr(-1) === scaleId) code.push($(this).attr('value'));
    });
  }

  const answers = {};
  const languages = langs.split(';');

  for (let x in languages) {
    answers[languages[x]] = [];
    $('.answer').each(function () {  // jshint ignore: line
      if ($(this).attr('id').substr(-1) === scaleId && $(this).attr('id').indexOf(languages[x]) !== -1) answers[languages[x]].push($(this).val());
    });
  }

  $.post(lasaveurl, {
    laname: $('#laname').val(), lid, code, answers,
  }, (data) => {
    // $("#saveaslabel").dialog('close');
    $('#saveaslabelModal').modal('hide');
    $('#dialog-confirm-replaceModal').modal('hide');

    if ($.parseJSON(data) === 'ok') {
      if ($('#dialog-result').is(':visible')) {
        $('#dialog-result-content').empty().append(lasuccess);
        $('#dialog-result').effect('pulsate', { times: 3 }, 3000);
      } else {
        $('#dialog-result').removeClass('alert-warning').addClass('alert-success');
        $('#dialog-result-content').empty().append(lasuccess);
        $('#dialog-result').show();
      }
    } else {
      $('#dialog-result').removeClass('alert-success').addClass('alert-warning');
      $('#dialog-result-content').empty().append(lafail);
      $('#dialog-result').show();
    }
  });
  */
}

/**
 * @return {void}
 */
function savelabel() {
  const lid = $('#lasets').val() ? $('#lasets').val() : 0;
  if (lid === 0) {
    const response = ajaxcheckdup();
    response.complete(() => {
      if (check) {
        ajaxreqsave();
      }
    });
  } else {
    const aLanguages = langs.split(';');
    $.post(sCheckLabelURL, { languages: aLanguages, lid, bCheckAssessments: 1 }, (data) => {
      $('#strReplaceMessage').html(data);
      $('#dialog-confirm-replaceModal').modal();
      $('#btnlconfirmreplace').click(() => {
        ajaxreqsave();
      });
    });
  }
}

$(document).on('ready pjax:scriptcomplete', () => {
  $('.tab-page:first .answertable tbody').sortable({
    containment: 'parent',
    start: startmove,
    stop: endmove,
    update: aftermove,
    distance: 3,
  });

  $('.btnaddsubquestion').on('click.subquestions', addSubquestionInput);
  $('.btndelsubquestion').on('click.subquestions', deleteSubquestionInput);
  $('.btnaddanswer').on('click.subquestions', addAnswerOptionInput);
  $('.btndelanswer').on('click.subquestions', deleteAnswerOptionInput);
  $('#labelsetbrowserModal').on('shown.bs.modal.', lsbrowser);
  $('#labelsetbrowserModal').on('hidden.bs.modal.', lsBrowserDestruct);

  $('#btnlsreplace').on('click', (e) => { e.preventDefault(); transferlabels('replace'); });
  $('#btnlsinsert').on('click', (e) => { e.preventDefault(); transferlabels('insert'); });

  $('#quickaddModal').on('show.bs.modal', (e) => {
    const scaleId = $(e.relatedTarget).data('scale-id');
    const tableId = $(e.relatedTarget).closest('div.action-buttons').siblings('table.answertable').attr('id');

    $('#btnqainsert').off('click').on('click', () => {
      quickaddlabels(scaleId, 'add', tableId);
    });

    $('#btnqareplace').off('click').on('click', () => {
      quickaddlabels(scaleId, 'replace', tableId);
    });
  });

  $('#labelsets').click(lspreview);
  // $('#languagefilter').click(lsbrowser);
  $('.bthsaveaslabel').click(getLabel);
  $('input[name=savelabeloption]:radio').click(setlabel);
  flag = [false, false];
  $('#btnsave').click(savelabel);
  updateRowProperties();

  bindExpandRelevanceEquation();
});

/* Event added on document for all button (new one added in js too)
 * TODO : use a real ajax system : see scripts/question.js validateQuestion function for example
 * */
$(document).on('click', '#editsubquestionsform :submit', () => {
  // Validate duplicate before try to submit: surely some other javascript elsewhere
  return codeDuplicatesCheck();
});
