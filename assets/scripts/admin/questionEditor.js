// @flow
// @ts-check

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

/**
 * To check with TypeScript:
 *   tsc --allowJs --noEmit --target ES6 assets/scripts/admin/decl.d.ts assets/scripts/admin/questionEditor.js
 * To check with Flow:
 *   flow check-contents < assets/scripts/admin/questionEditor.js
 * To check with jshint:
 *   jshint assets/scripts/admin/questionEditor.js
 */

// Flow declarations. The TypeScript declaration are in decl.d.ts.
// flowlint unclear-type: off
/*flow-include
declare var $: any
declare var _: any
declare var LS: any
*/
// flowlint unclear-type: error

// Globals for jshint.
/* globals $, _, alert, document, LS */

/**
 * BELOW IS FROM LS3 assets/scripts/admin/subquestions.js
 */

// Wrap it in closure to avoid global variables.
// TODO: Use modules? https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules
// TODO: Include functions from assets/packages/adminbasics/src/pages/subquestionandanswers.js
// TODO: Use class for quick-add
// TODO: Use class for label sets
LS.questionEditor = (function () {

  // TODO: Does not work with pjax loading.
  /** @type {Object} */
  let languageJson;
  const value = $('input[name=translation-strings-json]').val();
  try {
    languageJson = JSON.parse(unescape(value));
  } catch (e) {
    alert('Internal error: Could not parse language JSON');
    throw 'abort';
  }

  /** @type {boolean} Used in ajaxcheckdup */
  let check = true;

  /** @type {number} */
  const sid = parseInt($('input[name=sid]').val());
  if (isNaN(sid)) {
    alert($('input[name=sid]').val());
    alert('Internal error: No survey id found');
    throw 'abort';
  }

  /*:: declare function updateRowProperties(): void */
  /**
   * Rebind onclick events for subquestions and answer options?
   *
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

  /**
   * @param {number} i
   * @param {string} source
   * @return {Promise<XMLHttpRequest>}
   */
  function addInputPredefined(i /*: number */, source) /*: Promise<XMLHttpRequest> */ {
    // TODO: Support answer options
    let $dataInput;
    if (source === 'subquestions') {
       $dataInput = $('#add-subquestion-input-javascript-datas');
    } else if (source === 'answeroptions') {
       $dataInput = $('#add-answer-option-input-javascript-datas');
    } else {
      alert('Internal error: source is not subquestions or answeroptions: ' + source);
      throw 'abort';
    }

    if ($dataInput.length === 0) {
      alert('Internal error: Found no data element in addInputPredefined');
      throw 'abort';
    }

    const scaleId = $('#current_scale_id').val();
    // We build the datas for the request
    const datas = {
      surveyid: $dataInput.data('surveyid'),
      gid: $dataInput.data('gid'),
      codes: JSON.stringify({lbl_1: 'eins'}),  // jshint ignore:line
      scale_id: scaleId,  // jshint ignore:line
      position: i,
      type: 'subquestion',
      languages: JSON.stringify($dataInput.data('languages').join(';')),
    };
    // We get the HTML of the new row to insert
    return $.ajax({
      type: 'GET',
      contentType: 'json',
      url: $dataInput.data('url'),
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
      LS.doToolTip();
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

  /*:: declare function addinputQuickEdit({}, string, boolean, number, Array<string>, string): {} */
  /**
   * add addinputQuickEdit : for usage with the quickAdd Button
   *
   * @param {object} $currentTable
   * @param {string} language
   * @param {boolean} first
   * @param {number} scaleId
   * @param {array} _codes
   * @param {string} tableIdPrefix Either 'subquestions' or 'answeroptions'
   * @return {Promise}
   */
  function addinputQuickEdit($currentTable, language, first, scaleId, _codes, tableIdPrefix) {
    const codes = _codes || [];
    // This hidden element  on the page contains various datas for this function
    // TODO: Use class with state instead? `new QuickAdd('subquestions');`
    const dataInputId = $currentTable.data('input-data-id');
    if (dataInputId == null) {
      alert('Internal error: Missing data input id');
      throw 'abort';
    }
    const dataInput = document.getElementById(dataInputId);
    if (dataInput == null) {
      alert('Internal error: Could not find data input');
      throw 'abort';
    }
    const url = dataInput.dataset.quickurl;
    const errormessage = dataInput.dataset.errormessage;
    const $defer = $.Deferred();

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
    const codesJson = JSON.stringify(codes);
    // We build the datas for the request
    const datas = {
      codes: codesJson,
      // In $dataInput.data('scale-id') ?
      scale_id: scaleId,  // jshint ignore:line
      //type: 'subquestion',
      position: null,
      first,
      language,
    };

    // We get the HTML of the new row to insert
    $.ajax({
      // TODO: We don't update DB, no need post
      type: 'POST',
      url: url,
      data: datas,
      success(htmlrow) {
        const $langTable = $(`#${tableIdPrefix}_${language}_${scaleId}`);
        $defer.resolve({ lang: language, langtable: $langTable, html: htmlrow });
      },
      error(html, status) {
        alert('Internal error: ' + errormessage);
        $defer.reject([html, status, errormessage]);
      },
    });
    return $defer.promise();
  }

  /*:: declare function deleteSubquestionInput(Event): void */
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
    const countanswers = $(target).closest('tbody').children('tr').length; // Maybe use class is better
    if (countanswers > 1) {
      // NB: Only answer options use position. Subquestions use id.
      let position;
      const classes = $(target).closest('tr').attr('class').split(' ');
      _.forEach(classes, (curClass) => {
        if (curClass.substr(0, 3) === 'row') {
          position = curClass.substr(4);
        }
      });

      const info = $(target).closest('tr').attr('id').split('_');
      // TODO: use data-scaleid.
      const scaleId = info[3];
      const subquestionId = info[2];
      const languages = languageJson.langs.split(';');

      _.forEach(languages, (curLanguage, x) => {
        const $tablerow = $(`#row_${languages[x]}_${subquestionId}_${scaleId}`);
        if ($tablerow.length === 0) {
          alert('Internal error: Could not find row to delete');
          throw 'abort';
        }
        if (x === 0) {
          $tablerow.fadeTo(300, 0, function fadeAndRemove() {
            $tablerow.remove();
            updateRowProperties();
          });
        } else {
          $tablerow.remove();
        }
      });
    } else {
      // Do nothing, can't delete last row.
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
    const target = e.target;
    // 1.) Check if there is at least one answe
    const countanswers = $(target).closest('tbody').children('tr').length; // Maybe use class is better
    if (countanswers > 1) {
      // NB: Only answer options use position. Subquestions use id.
      let position;
      const classes = $(target).closest('tr').attr('class').split(' ');
      _.forEach(classes, (curClass) => {
        if (curClass.substr(0, 3) === 'row') {
          position = curClass.substr(4);
        }
      });

      // Info is array like: ["row", lang, position, questionId, scale id].
      const info = $(target).closest('tr').attr('id').split('_');
      // TODO: use data-scaleid.
      const languages = languageJson.langs.split(';');

      _.forEach(languages, (curLanguage, x) => {
        // TODO: This is the only row that's different from deleteSubquestionInput().
        const $tablerow = $(`#row_${languages[x]}_${info[2]}_${info[3]}_${info[4]}`);
        if ($tablerow.length === 0) {
          alert('Internal error: Could not find row to delete');
          throw 'abort';
        }
        if (x === 0) {
          $tablerow.fadeTo(300, 0, function fadeAndRemove() {
            $tablerow.remove();
            updateRowProperties();
          });
        } else {
          $tablerow.remove();
        }
      });
    } else {
      // Do nothing, can't delete last row.
    }
    updateRowProperties();
  }

  /**
   * Helper function for addSubquestionInput and addAnswerOptionInput.
   *
   * @param {EventTarget} target
   * @param {Object} data Data from relevant <input> in the view.
   * @param {Function} rebindClickHandler
   * @return {void}
   */
  function addNewInputAux(target /*: EventTarget */, data /*: {[string]: string} */, rebindClickHandler /*: () => void */)
  {
    if (!(target instanceof HTMLElement)) {
      alert('Internal error: Target is expected to be HTMLElement');
      throw 'abort';
    }

    // The "add" button
    const $that = $(target);
    // The row containing the "add" button
    const $currentRow = $that.closest('.row-container');
    const $currentTable = $that.closest('.answertable');
    // The common id of this row in the other languages
    const commonId = $currentRow.data('common-id');
    // Url for the request
    const url = data.url;
    // the error message if the AJAX request failed
    const errormessage = data.errormessage;
    const languages = JSON.stringify(languageJson.langs);

    if ($currentTable.length === 0) {
      alert('Internal error: Found no answertable');
      throw 'abort';
    }

    // We get all the subquestion codes currently displayed
    const codes = [];
    $currentTable.find('.code').each(function (i, elem) {
      codes.push($(elem).val());
    });

    // We convert them to json for the request
    const codesJson = JSON.stringify(codes);

    // TODO: Why on earth is target sometimes <i>, sometimes <button>??
    let targetData = {};
    if (target.nodeName === 'BUTTON') {
      targetData = $(target).find('i').data();
    } else if (target.nodeName === 'I') {
      targetData = $(target).data();
    } else {
      alert('Internal error: Unknown nodeName: ' + target.nodeName);
      throw 'abort';
    }
    const scaleId = targetData.scaleId;

    if (scaleId === '' || scaleId === undefined) {
      alert('Internal error: Could not find scale_id');
      throw 'abort';
    }

    // We build the datas for the request
    // TODO: Use object instead of string.
    let datas = `surveyid=${data.surveyid}`;
    datas += `&gid=${data.gid}`;
    datas += `&qid=${data.qid}`;
    datas += `&codes=${codesJson}`;
    datas += `&scale_id=${scaleId}`;
    datas += '&position=0';
    datas += `&languages=${languages}`;

    // We get the HTML of the different rows to insert  (one by language)
    $.ajax({
      type: 'GET',
      url: url,
      data: datas,
      success(arrayofhtml) {
        // arrayofhtml is a json object containing the different HTML row by language
        // eg: {"en":"{the html of the en row}", "fr":{the html of the fr row}}

        // We insert each row for each language
        $.each(arrayofhtml, (lang, htmlRow) => {
          const $elRowToUpdate = $(`#row_${lang}_${commonId}`); // The row for the current language
          $elRowToUpdate.after(htmlRow); // We insert the HTML of the new row after this one
        });
        rebindClickHandler();
      },
      error() {
        alert(errormessage);
      },
    });
  }

  /*:: declare function addSubquestionInput(Event): void */
  /**
   * Add one subquestion row using Ajax.
   *
   * @param {Event} e
   * @return {void}
   */
  function addSubquestionInput(e) {
    e.preventDefault();
    const target = e.target;
    const data = $('#add-subquestion-input-javascript-datas').data();
    const rebindClickHandler = () => {
      $('.btnaddsubquestion').off('click.subquestions').on('click.subquestions', addSubquestionInput);
      $('.btndelsubquestion').off('click.subquestions').on('click.subquestions', deleteSubquestionInput);
    };
    addNewInputAux(target, data, rebindClickHandler);
  }

  /**
   * Add one answer option row using Ajax.
   *
   * @param {event} e
   * @return {void}
   */
  function addAnswerOptionInput(e) {
    e.preventDefault();
    const target = e.target;
    const data = $('#add-answer-option-input-javascript-datas').data();
    const rebindClickHandler = () => {
      $('.btnaddanswer').off('click.subquestions').on('click.subquestions', addAnswerOptionInput);
      $('.btndelanswer').off('click.subquestions').on('click.subquestions', deleteAnswerOptionInput);
    };
    addNewInputAux(target, data, rebindClickHandler);
  }

  //function updatecodes() {
  //}

  /**
   * @param {any} mixedVar
   * @return {boolean}
   */
  function isNumeric(mixedVar /*: mixed */) {
    return (typeof (mixedVar) === 'number' || typeof (mixedVar) === 'string') && mixedVar !== '' &&
      (typeof mixedVar === 'number' && !isNaN(mixedVar));
  }

  /**
   * @param {string} sSourceCode
   * @return {string}
   * @todo Used in label sets?
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
        foundnumber = parseInt(sourcecode.substr(sclength - i, i));
        i++;
      }
    }
    if (foundnumber === -1) {
      return sourcecode;
    }

    foundnumber++;
    const foundnumberString = foundnumber.toString();
    const result = sourcecode.substr(0, sclength - foundnumberString.length) + foundnumberString;
    return (result);
  }

  /**
   * Check if all existing codes are unique
   * If sNewValue is not empty then only sNewValue is checked for uniqueness against the existing codes
   *
   * @param sNewValue
   * @returns {boolean} False if codes are not unique
   */
  function areCodesUnique(sNewValue) {  // jshint ignore: line
    const languages = languageJson.langs.split(';');
    let dupefound = false;
    $(`#tabpage_${languages[0]} .answertable tbody`).each(function () {
      let codearray = [];
      $(this).find('tr .code').each(function () {
        codearray.push($(this).val());
      });
      if (sNewValue !== '') {
        codearray = LS.getUnique(codearray);
        codearray.push(sNewValue);
      }
      if (LS.arrHasDupes(codearray)) {
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
   * Reset label set picker after hide.
   * @return {void}
   */
  function labelSetDestruct() {
    console.log('labelSetDestruct');
    $('#labelsets').select2('destroy');
    $('#labelsetpreview').empty();
  }

  /**
   * Previews the labels in a modal after selecting it in the select box
   *
   * @param {number} lid Label set id
   * @return {void}
   */
  function showLabelSetPreview(lid /*: number */) /*: void */ {
    return $.ajax({
      url: languageJson.lsdetailurl,
      data: {sid, lid},
      cache: true,
      success(json /*: {results: Array<{label_name: string, labels: Array<{code: string, title: string}>}>, languages: {}} */) {
        if (json.languages === []) {
          alert('Internal error: No languages');
          throw 'abort';
        }

        const $liTemplate = $('<li role="presentation"></li>');
        const $aTemplate = $('<a data-toggle="tab"></a>');
        const $tabTodyTemplate = $('<div></div>');
        const $listTemplate = $('<div class="list-group selector_label-list"></div>');
        const $listItemTemplate = $('<div class="list-group-item row selector_label-list-row"></div>');
        const $tabindex = $('<ul class="nav nav-tabs" role="tablist"></ul>');
        const $tabbody = $('<div class="tab-content" style="max-height: 50vh; overflow:auto;"></div>');

        const i = 0;
        $.each(json.languages, (language, languageName) => {
          const $linkItem = $aTemplate.clone();
          const $bodyItem = $tabTodyTemplate.clone();
          let $itemList = $listTemplate.clone();

          const classLink = i === 0 ? 'active' : '';
          const classBody = i === 0 ? 'tab-pane tab-pane fade in active' : 'tab-page tab-pane fade';

          $linkItem.addClass(classLink).attr('href', `#language_${language}`).text(languageName);
          $liTemplate.clone().append($linkItem).appendTo($tabindex);

          $bodyItem.addClass(classBody).attr('id', `language_${language}`);
          $tabbody.append($bodyItem);

          const labelSet = json.results[language];

          $itemList = $listTemplate.clone();

          labelSet.labels.forEach((label) => {
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

          $bodyItem.append(`<h4>${labelSet.label_name}</h4>`);  // jshint ignore: line
          $itemList.appendTo($bodyItem);
        });
        $('#labelsetpreview').empty();
        $('<div></div>').append($tabindex).append($tabbody).appendTo($('#labelsetpreview'));
        $tabindex.find('li').first().find('a').trigger('click');
      },
    });
  }

  /**
   * @param {Event} e
   * @return {void}
   * @todo Fix name
   * @todo What does it do?
   */
  function initLabelSetModal(e) {
    const target = e.target;
    if (!(target instanceof HTMLElement)) {
      alert('Internal error: target is expected to be HTMLElement');
      throw 'abort';
    }
    const scaleId = $(target).data('scale-id');
    if (scaleId == null) {
      alert('Internal error: No scale id in initLabelSetModal');
      throw 'abort';
    }

    // TODO: Send as input, not in DOM.
    $('body').append(`<input type="hidde" id="current_scale_id" value="${scaleId}" name="current_scale_id" />`);

    $('#labelsets').select2();
    $('#labelsetpreview').html('');
    $.ajax({
      url: languageJson.lspickurl,
      data: { sid, match: 1 },
      success(jsonString) {
        if (jsonString.success !== true) {
          $('#labelsetpreview').html(`<p class='alert'>${languageJson.strNoLabelSet}</p>`);
          $('#btnlsreplace').addClass('disabled');
          $('#btnlsinsert').addClass('disabled');
          $('#btnlsreplace').attr('disabled', 'disabled');
          $('#btnlsinsert').attr('disabled', 'disabled');
        } else {
          $('#labelsets').find('option').each((i, option) => { if ($(option).attr('value')) { $(option).remove(); } });
          jsonString.labelsets.forEach((item) => {
            const newOption = $(`<option value="${item.lid}">${item.label_name}</option>`);  // jshint ignore: line
            $('#labelsets').append(newOption).trigger('change');
          });
        }
      },
    });

    // Label set select2 element.
    $('#labelsets').on('change', function () {
      const value = $(this).val();
      showLabelSetPreview(parseInt(value));
    });
  }

  /**
   * Transfer labels from preview to subquestions/answer options.
   *
   * @param {string} type 'replace' or 'add'
   * @param {string} source 'subquestions' or 'answeroptions'
   * @return {void}
   */
  function transferLabels(type /*: string */, source /*: string */) /*: void */ {
    //const languages = languageJson.langs.split(';');
    //const labels = [];
    const scaleId = $('#current_scale_id').val();

    addInputPredefined(1, source).then((result) => {
      $.each(result, (lang, row) => {
        if (!(row instanceof HTMLElement)) {
          alert('Internal error: row is not an HTMLElement');
          throw 'abort';
        }

        // TODO: Answer options
        const tableId = `#${source}_${lang}_${scaleId}`;
        const $table = $(tableId);
        if ($table.length === 0) {
          alert('Internal error: Found no table to add labels to with id ' + tableId);
          throw 'abort';
        }

        if (type === 'replace') {
          $table.find('tbody').find('tr').each((i, tableRow) => {
            $(tableRow).remove();
          });
        }

        $('#labelsetpreview').find(`#language_${lang}`).find('.selector_label-list').find('.selector_label-list-row')
        .each((i, item) => {
          try {
            const label /*: {code: string, title: string} */ = $(item).data('label');
            console.log('row', row);
            const $row = $(row);
            console.log('$row', $row);
            let $tr;
            // TODO: Use classes instead of if-statements.
            if (source === 'subquestions') {
              $tr = $row.eq(4);
            } else if (source === 'answeroptions') {
              $tr = $row.eq(2);
            } else {
              alert('Internal error: source is not subquestions or answeroptions: ' + source);
              throw 'abort';
            }
            if ($tr.length === 0) {
              throw 'Found no $tr in transferLabels';
            }
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
            alert('Internal error in transferLabels: ' + e);
            throw 'abort';
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
   * @param {Array<string>} lsrows
   * @return {string}
   */
  function getSeparatorChar(lsrows) {
    if (lsrows[0].indexOf('\t') === -1) {
      return ';';
    } else {
      return '\t';
    }
  }

  /*:: declare function quickAddLabels(number, string, string): void */
  /**
   * Quick-add subquestions/answers
   *
   * @param {number} scaleId
   * @param {string} addOrReplace - Either 'add' or 'replace'
   * @param {string} tableId
   * @return {void}
   * @todo Unit-test this? How? With classes?
   * @todo Factor out functions.
   */
  function quickAddLabels(scaleId, addOrReplace, tableId) {
    //const sID = $('input[name=sid]').val();
    //const gID = $('input[name=gid]').val();
    //const qID = $('input[name=qid]').val();
    const codes = [];
    const $closestTable = $(`#${tableId}`);
    const tableIdPrefix = tableId.split('_')[0];
    const lsreplace = addOrReplace === 'replace';

    // Not needed, since we always delete all rows at save (when survey is not active).
    /*
    if (lsreplace) {
      $(`.answertable:eq(${scaleId}) tbody tr`).each(function () {
        const aRowInfo = this.id.split('_');
        const elem = $('#deletedqids');
        const previousVal = elem.val();
        const newVal = previousVal + ' ' + aRowInfo[2];
        elem.val(newVal);
      });
    }
    */

    if ($closestTable.find('.code').length < 0) {
      $closestTable.find('.code-title').each(function () {
        codes.push($(this).text());
      });
    } else {
      $closestTable.find('.code').each(function () {
        codes.push($(this).val());
      });
    }

    const languages = languageJson.langs.split(';');
    const promises = [];
    // TODO: Doc answers
    const answers = {};
    const lsrows = $('#quickaddarea').val().split('\n');
    const allrows = $closestTable.find('tr').length;
    const separatorchar = getSeparatorChar(lsrows);

    let numericSuffix = '';
    let n = 1;
    let numeric = true;
    let currentCharacter = 'A';
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

    // TODO: Document value
    // NB: splitCSV is added to string prototype in adminbasics.
    lsrows.forEach((value /*: string & {splitCSV: string => Array<string>} */, k /*: number */) => {
      const thisrow = value.splitCSV(separatorchar);

      if (thisrow.length <= languages.length) {
        let qCode = (parseInt(k) + 1).toString();
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
      // TODO: This should come from the server.
      const quid = `new${Math.floor(Math.random() * 10000)}`;

      // TODO: What's happening here?
      languages.forEach((language, x) => {
        if (typeof thisrow[parseInt(x) + 1] === 'undefined') {
          thisrow[parseInt(x) + 1] = thisrow[1];
        }

        if (!answers[language]) {
          answers[language] = [];
        }
        if (lsreplace) {
          $(`#subquestions_${language}_${scaleId} tbody`).empty();
        }
        answers[language].push(
          { text: thisrow[(parseInt(x) + 1)], code: thisrow[0], quid },
        );
      });

      // $('#subquestions_'+languages[x]+'_'+scaleId+' tbody').append(tablerows);
    });

    // TODO: One call per language, really?
    languages.forEach((language, x) => {
      // NB: promises is an array with promises.
      // NB: addinputQuickEdit returns a promise.
      promises.push(
        addinputQuickEdit($closestTable, language, x === 0, scaleId, codes, tableIdPrefix)
      );
    });

    $.when.apply($, promises).done(
      function (...args) {
        /* $('#quickadd').dialog('close'); */
        // TODO: What is item here?
        // TODO: Too dynamic to use "arguments", better to know.
        args.forEach((item /*: {lang: string, langtable: {}, html: string} */) => {
          answers[item.lang].forEach((row /*: {quid: string, text: string, code: string} */) => {
            const { html } = item;
            const htmlQuid = html.replace(/{{quid_placeholder}}/g, row.quid);
            const htmlRowObject = $(htmlQuid);
            if (htmlRowObject.length === 0) {
              alert('Internal error: Could not find htmlRowObject');
              throw 'abort';
            }
            htmlRowObject.find('input.answer').val(row.text);
            if (htmlRowObject.find('input.code').length > 0) {
              htmlRowObject.find('input.code').val(row.code);
            } else {
              htmlRowObject.find('td.code-title').text(row.code);
            }
            htmlRowObject.find('td.relevance-equation').find('input').val(1);

            $(item.langtable).find('tbody').append(htmlRowObject);
          });
        });
        $('#quickaddarea').val('');
        //$('.tab-page:first .answertable tbody').sortable('refresh');
        $('.tab-page:first .answertable tbody').sortable('refresh');
        updateRowProperties();
        $('#quickaddModal').modal('hide');
        bindClickIfNotExpanded();
      },
      function () {
        /* $('#quickadd').dialog('close'); */
        $('#quickaddarea').val('');
        $('.tab-page:first .answertable tbody').sortable('refresh');
        updateRowProperties();
        $('#quickaddModal').modal('hide');
        bindClickIfNotExpanded();

        // Unbind and bind events.
        $(`.btnaddanswer`).off('click.subquestions');
        $(`.btndelanswer`).off('click.subquestions');
        $(`.btnaddsubquestion`).off('click.subquestions');
        $(`.btndelsubquestion`).off('click.subquestions');
        $(`.answer`).off('focus');
        $(`.btnaddanswer`).on('click.subquestions', addAnswerOptionInput);
        $(`.btndelanswer`).on('click.subquestions', deleteAnswerOptionInput);
        $(`.btnaddsubquestion`).on('click.subquestions', addSubquestionInput);
        $(`.btndelsubquestion`).on('click.subquestions', deleteSubquestionInput);
      },
    );
  }

  /**
   * @return {void}
   */
  function getLabel() {
    console.log('getLabel');
    // TODO
    //const answer_table = $(this).parent().children().eq(0);
    //const scaleId = LS.removechars($(this).attr('id'));

    // $('#saveaslabel').dialog('open');
    updateRowProperties();
  }

  /**
   * Used for "Save as label set"
   *
   * @param {event} event
   * @return {void}
   */
  function saveAsLabelSetOptionClick(event /*: Event */) /*: void */ {
    console.log('saveAsLabelSetOptionClick');
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      alert('target is not an HTMLElement');
      throw 'abort';
    }

    const template = document.createElement('template');
    let child;
    const targetParent = target.parentNode;
    if (!(targetParent instanceof HTMLElement)) {
      throw 'Internal error: targetParent is not an instance of HTMLElement';
    }

    // Cleanup any previous HTML.
    const lasets = document.getElementById('lasets');
    if (lasets) {
        lasets.remove();
    }
    const laname = document.getElementById('laname');
    if (laname) {
        laname.remove();
    }

    // TODO: Split each case into a function.
    switch (target.getAttribute('id')) {
        // Save as new label set.
        case 'newlabel':
            template.innerHTML = `<p id="lasets" class="label-name-wrapper">
                 <label for="laname">${languageJson.sLabelSetName}:</label>
                 <input type="text" name="laname" id="laname">
               </p>`;
            child = template.content.firstElementChild;
            if (child) {
              targetParent.after(child);
            }
            break;
        // Replace an existing label set.
        case 'replacelabel':
            template.innerHTML = `
              <p id="laname" class="label-name-wrapper">
                <select name="laname">
                  <option value=""></option>
                </select>
              </p>' 
            `;
            // 
            child = template.content.firstElementChild;
            if (child) {
              targetParent.after(child);
            }
            //$('#lasets option[value=""]').remove();
            const select = document.querySelector('select[name="laname"]');
            if (!select) {
                alert('Found no <select>');
                throw 'abort';
            }
            $.getJSON(languageJson.lanameurl, (data) => {
              console.log('getJSON');
              $.each(data, (key, val) => {
                if (typeof val === 'string') {
                  $(select).append(`<option value="${key}">${val}</option>`);
                } else {
                  throw 'val is not string';
                }
              });
            });
            break;
        default:
            alert('Internal error: Unsupported id in target (saveAsLabelSetOptionClick)');
            throw 'abort';
    }
  }

  /*:: declare function ajaxcheckdup(): Promise<mixed> */
  /**
   * TODO: Used where???
   *
   * @return {Promise}
   */
  function ajaxcheckdup() {
    check = true; // set check to true everytime on call
    return $.getJSON(languageJson.lanameurl, (data) => {
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
        }
      });
    });
  }

  /**
   * Called when saving new label set
   *
   * @param {Event} e
   * @param {string} tableClassName 'subquestions-table' or 'answeroptions-table'
   * @return {void}
   */
  function saveLabelSetAjax(e /*: Event */, tableClassName /*: string */) {
    console.log('saveLabelSetAjax');
    // todo: scale id is not defined
    const scaleId = 1;
    const lasets = document.getElementById('lasets');
    let lid;
    if (lasets instanceof HTMLInputElement) {
      lid = lasets.value;
    } else {
      lid = 0;
    }

    // Get question/answer option codes for the current scale
    const codes = [];
    const table = document.querySelector('.' + tableClassName);
    if (table == null) {
      throw 'found no table with class .' + tableClassName;
    }

    const codeInputs = table.querySelectorAll('.code');
    console.log('codeInputs', codeInputs);
    if (codeInputs.length > 0) {
      // Deactivated survey
      codeInputs.forEach((codeInput) => {
        if (codeInput instanceof HTMLInputElement) {
          codes.push(codeInput.value);
        } else {
          throw 'codeInputs is not an HTMLInputElement';
        }
      });
    } else {
      // Activated survey
      // TODO
      $('.answertable input[name^="code_"]').each(function () {
        if ($(this).attr('name').substr(-1) === scaleId) {
            codes.push($(this).attr('value'));
        }
      });
    }
    console.log('codes', codes);

    const answers = {};

    const tables = document.querySelectorAll('.' + tableClassName);
    tables.forEach((table) => {
      table.querySelectorAll('.answer').forEach((answerInput) => {
        if (answerInput instanceof HTMLInputElement) {
          answers[answerInput.name] = answerInput.value;
        } else {
          throw 'answerInput is not an HTMLInputElement';
        }
      });
    });

    /*
    const token = $.ajaxSetup().data.YII_CSRF_TOKEN;
    const response = await fetch(
      languageJson.lasaveurl,
      {
        method: 'POST',
        headers: {
          //'Content-Type': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          'X-CSRFToken': token
        },
        // TODO: FormData here
        body: JSON.stringify(
          {
            laname: $('input[name=laname]').val(),
            lid: lid,
            code: codes,
            answers: answers,
            YII_CSRF_TOKEN: token,
            [languageJson.csrf.tokenName]: languageJson.csrf.token
          },
        ),
        //body: new URLSearchParams({
          //YII_CSRF_TOKEN: token
        //}).toString(),
        credentials: 'include'
      }
    );
    if (response.ok) {
      console.log(await response.json());
    } else {
      alert('Internal error: Could not POST request: ' + response.status + ', ' + response.statusText);
      throw 'abort';
    }
    */

    // NB: "Save as new label set" uses <input>, update existing uses <select>.
    let laname = $('input[name=laname]').val();
    let url;
    let labelSetId;
    if (laname) {
      url = languageJson.lasaveurl;
    } else {
      laname = $('select[name=laname]').text();
      // TODO: Duplicated to lid?
      labelSetId = $('select[name=laname]').val();
      url = languageJson.laupdateurl;
    }

    $.ajax({
      url,
      method: 'POST',
      data: {
        laname,
        lid,
        answers,
        labelSetId,
        codes,
      },
      /**
       * @param {any} data
       * @return {void}
       */
      success(successMessage) {
        LS.LsGlobalNotifier.create(
          successMessage,
          'well-lg bg-success text-center'
        );
      },
      /**
       * @param {any} data
       * @return {void}
       */
      error(data) {
        //console.log('data', data);
        //console.log('textStatus', textStatus);
        //console.log('jqXHR', jqXHR);
        if (data.responseJSON) {
          LS.LsGlobalNotifier.create(
            data.responseJSON.message,
            'well-lg bg-danger text-center'
          );
        } else {
          alert('Internal eror from Ajax call');
          throw 'abort';
        }
      }
    });

     /*
   }).fail((xhr, textStatus, errorThrown) => {
     console.log('fail');
     console.log(xhr);
     if (xhr.status === 500) {
       LS.LsGlobalNotifier.create(
         errorThrown,
         'well-lg bg-danger text-center'
       );
     } else if (xhr.status === 401) {
       LS.LsGlobalNotifier.create(
         "Not logged in",
         'well-lg bg-warning text-center'
       );
     } else {
       LS.LsGlobalNotifier.create(
         xhr.responseJSON.message,
         'well-lg bg-danger text-center'
       );
     }
   }).complete((xhr) => {
     console.log('complete');
     console.log(xhr);
   }).success((xhr) => {
     console.log('success');
     console.log(xhr);
   });;
   */
  }

    /**
     *
     * @param code {string}
     * @returns {boolean}
     */
  function checkCodeUniqueness(code) {
      let isValid = false;
      console.log('Is Valid: ' + isValid.toString());
      const checkQuestionCodeIsUniqueURL = languageJson.checkQuestionCodeIsUniqueURL;
      let checkCodePromise = getCheckUniquenessPromise(checkQuestionCodeIsUniqueURL, code);
      checkCodePromise.then((response) => {
          console.log('Status: ' +  response.status);
          console.log(response.statusText);
          console.log(response.statusCode);
           isValid = true;
          console.log('Is Valid: ' + isValid.toString());
      }).catch((error) => {
          console.log('Status: ' +  error.status);
          console.log(error);
           isValid = false;
          console.log('Is Valid: ' + isValid.toString());
      });

      return isValid;
  }

    /**
     * @param {string} url,
     * @param {string} code
     * @returns {Promise}
     */
  function getCheckUniquenessPromise(url, code) {
    console.log('URL: ' + url);
    return new Promise((resolve, reject) => {
      $.ajax({
        url: url,
        method: 'GET',
        data: { code: code },
        dataType: 'json',
        success: (data) => {
          resolve(data);
          console.log('Success');
          console.log(data);
        },
        error: (data) => {
          reject(data);
          console.log('Error');
          console.log(data);
        }
      });
    });
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
    const languages = languageJson.langs.split(';');

    // TODO: Replace with $.each()? Other order of value/key in callback.
    _.forEach(languages, (curLanguage, x) => {
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
   * @param {Event} event
   * @param {string} tableClassName 'subquestions-table' or 'answeroptions-table'
   * @return {void}
   */
  function onClickSaveLabelSet(event /*: Event */, tableClassName /*: string */) {
    console.log('tableClassName', tableClassName);
    // TODO: What is lid?
    // TODO: Where is lasets defined???
    const lid = $('#lasets').val() ? $('#lasets').val() : 0;
    if (lid === 0) {
      const response = ajaxcheckdup();
      response.then(() => {
        if (check) {
          saveLabelSetAjax(event, tableClassName);
        }
      });
    } else {
      console.log('here');
      const aLanguages = languageJson.langs.split(';');
      $.post(languageJson.sCheckLabelURL, { languages: aLanguages, lid, bCheckAssessments: 1 }, (data) => {
        $('#strReplaceMessage').html(data);
        $('#dialog-confirm-replaceModal').modal();
        $('#btnlconfirmreplace').click(() => {
          saveLabelSetAjax(event, tableClassName);
        });
      });
    }
  }
  /**
   * @return {boolean}
   * @todo Works?
   */
  function codeDuplicatesCheck() {
    // $('.code[data-toggle="tooltip"]').data('toggle', '').tooltip('destroy');
    const languages = languageJson.langs.split(';');
    let cansubmit = true;
    $(`#tabpage_${languages[0]} .answertable tbody`).each(function () {
      const codearray = [];
      $(this).find('tr .code').each(function () {
        codearray.push($(this).val().toLowerCase());
      });
      const theDuplicate = LS.arrHasDupesWhich(codearray);
      if (theDuplicate !== false) {
        $('#error-modal .modal-body-text').html(languageJson.subquestions.duplicatesubquestioncode);
        $('#error-modal').modal();
        cansubmit = false;
      }
    });
    return cansubmit;
  }

  /* Event added on document for all button (new one added in js too)
   * @todo used?
   */
  $(document).on('click', '#editsubquestionsform :submit', () => {
    // Validate duplicate before try to submit: surely some other javascript elsewhere
    return codeDuplicatesCheck();
  });

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

    $('#labelsetbrowserModal').on('hidden.bs.modal.', labelSetDestruct);

    $('#quickaddModal').on('show.bs.modal', (e) => {
      const scaleId = parseInt($(e.relatedTarget).data('scale-id'));
      const tableId = $(e.relatedTarget).closest('div.action-buttons').siblings('table.answertable').attr('id');
      if (tableId === '') {
        alert('Internal error: Did not find tableId');
        throw 'abort';
      }

      $('#btnqainsert').off('click').on('click', () => {
        quickAddLabels(scaleId, 'add', tableId);
      });

      $('#btnqareplace').off('click').on('click', () => {
        quickAddLabels(scaleId, 'replace', tableId);
      });
    });

    $('#labelsets').click(showLabelSetPreview);
    $('.bthsaveaslabel').click(getLabel);
    $('input[name=savelabeloption]:radio').click(saveAsLabelSetOptionClick);
    updateRowProperties();

    bindExpandRelevanceEquation();

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

    /*****************************************/
    // Check Question Code is unique.
    /*
    $('#questionCode').focusout(() => {
        let code = $('#questionCode').val();
        if (code !== undefined || code !== '') {
            console.log('Question Code: ' + code);
            let isValid = checkCodeUniqueness(code);
        //     if (!isValid) {
        //         console.log('Error Code is not unqiue.');
        //     }
        //     console.log('IsValid: ' + isValid);
        } else {
            console.log('Question Code is empty');
        }
    });
    */

    // Check Answer Code is unique.
     $('#answerCode').focusout( () => {
        // Answer code
     });

     // Check Sub Question Code is unique.
    /*****************************************/

    // Hide all language except the selected one.
    $('.lang-switch-button').on('click', function langSwitchOnClick() {
      const lang = $(this).data('lang');
      const langClass = `.lang-${lang}`;
      $('.lang-hide').hide();
      $(langClass).show();
    });
  });

  $(document).on('ready pjax:scriptcomplete', function () {
    // Hide all languages except main.
    $('.lang-hide').hide();
    const languages = languageJson.langs.split(';');
    $('.lang-' + languages[0]).show();

    // Land on summary page if qid != 0 (not new question).
    const qidInput = document.querySelector('input[name="question[qid]"]');
    if (qidInput === null) {
      alert('Internal error: Could not find qidInput');
      throw 'abort';
    }
    if (qidInput instanceof HTMLInputElement) {
      if (parseInt(qidInput.value) !== 0) {
        $('#advanced-question-editor').hide();
        $('#question-create-topbar').hide();
        $('#question-edit-topbar').hide();
      } else {
        $('#advanced-question-editor').show();
        $('#question-create-topbar').show();
      }
    } else {
      alert('Internal error: qidInput is not an HTMLInputElement');
      throw 'abort';
    }
  });

  // Return public functions to LS.questionEditor module.
  return {
    /**
     * Update question attributes (general and advanced settings) when selecting question type.
     * Used by question selector modal.
     *
     * @param {string} questionType - One-letter string of question type
     * @param {string} generalSettingsUrl - URL to controller to fetch new HTML
     * @param {string} advancedSettingsUrl - URL to controller to fetch new HTML
     * @return {Promise}
     */
    // eslint-disable-next-line no-unused-vars
    updateQuestionAttributes: async function (questionType, generalSettingsUrl, advancedSettingsUrl) {  // jshint ignore:line
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

        $('.lang-hide').hide();
        const languages = languageJson.langs.split(';');
        $('.lang-' + languages[0]).show();

        // TODO: Duplication.
        $('.btnaddsubquestion').on('click.subquestions', addSubquestionInput);
        $('.btndelsubquestion').on('click.subquestions', deleteSubquestionInput);
        $('.btnaddanswer').on('click.subquestions', addAnswerOptionInput);
        $('.btndelanswer').on('click.subquestions', deleteAnswerOptionInput);
      } catch (ex) {
        $('#ls-loading').hide();
        // TODO: How to show internal errors?
        // eslint-disable-next-line no-alert
        alert(`Internal error: ${ex}`);
      }
    },

    // Show summary page.
    showOverview: function () {
      $('#advanced-question-editor').hide();
      $('#question-create-topbar').hide();
      $('#question-edit-topbar').hide();
      $('#question-overview').show();
      $('#question-summary-topbar').show();
    },

    showEditor: function () {
      $('#question-overview').hide();
      $('#question-summary-topbar').hide();
      $('#advanced-question-editor').show();
      $('#question-create-topbar').show();
      $('#question-edit-topbar').show();
    },

    /**
     * Used in onclick event in Twig template.
     *
     * @param {Event} event
     * @param {string} source
     */
    showLabelSetPicker: function(event /*: Event */, source /*: string */) {
      $('#btnlsreplace').on('click', (e) => {
        e.preventDefault();
        transferLabels('replace', source);
      });
      $('#btnlsinsert').on('click', (e) => {
        e.preventDefault();
        transferLabels('insert', source);
      });

      $('#labelsetbrowserModal').modal('show');

      initLabelSetModal(event);
    },

    /**
     * Used in onclick event in Twig template.
     *
     * @param {Event} event
     * @param {string} source
     */
    showLabelSetSaver: function(event /*: Event */, tableClassName /*: string */) {
      const button = document.getElementById('btnsavelabelset');
      if (button == null) {
        throw 'Found no btnsavelabelset';
      }
      button.onclick = () => {
        event.preventDefault();
        onClickSaveLabelSet(event, tableClassName);
      };
      $('#saveaslabelModal').modal('show');
    },

    /**
     * Check with Ajax if question code (title) is unique.
     *
     * @param {string} code
     * @param {number} qid Question id (0 when creating new question)
     * @return {void}
     */
    checkQuestionCodeUniqueness: function(code, qid) {
      $('#question-code-unique-warning').addClass('hidden');
      $.ajax({
        url: languageJson.checkQuestionCodeIsUniqueURL,
        method: 'GET',
        data: {
          qid,
          code
        },
        success: (data) => {
          if (data !== 'true') {
            $('#question-code-unique-warning').removeClass('hidden');
          }
        },
        error: (data) => {
          alert('Internal error: ' + data);
          throw 'abort';
        }
      });
    },

    /**
     * @param {string} code
     * @param {string} sqid Subquestion id (newXXXX when creating new subquestion)
     * @return {void}
     */
    checkSubquestionCodeUniqueness: function(code, sqid) {
      // TODO: Hide error message.
      console.log('code', code);
      console.log('sqid', sqid);
      $.ajax({
        url: languageJson.checkSubquestionCodeIsUniqueURL,
        method: 'GET',
        data: {
          sqid,
          code,
          // NB: sid is defined at top of module.
          sid
        },
        statusCode: {
          '403': () => {
            alert('nooo');
          }
        },
        success: (data) => {
          if (data !== 'true') {
            // TODO: Show error message
          }
        },
        error: (data) => {
          alert('Internal error: ' + JSON.stringify(data));
          throw 'abort';
        }
      });
    }
  };
})();
