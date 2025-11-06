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
 *
 * Translations from PHP are fetched from application/views/admin/survey/Question/_subQuestionsAndAnwsersJsVariables.php.
 */

// Flow declarations. The TypeScript declaration are in decl.d.ts.
// flowlint unclear-type: off
/*flow-include
declare var $: any
declare var _: any
declare var LS: any
declare var CKEDITOR: any
*/
// flowlint unclear-type: error

// Globals for jshint.
/* globals $, _, alert, document, CKEDITOR, ace, bootstrap */

// NB: All public functions are in LS.questionEditor.
var LS = LS || {};

/**
 * BELOW IS FROM LS3 assets/scripts/admin/subquestions.js
 */

// Wrap it in closure to avoid global variables.
// TODO: Use modules? https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules
// TODO: Include functions from assets/packages/adminbasics/src/pages/subquestionandanswers.js
// TODO: Use component for quick-add
// TODO: Use component for label sets
$(document).on('ready pjax:scriptcomplete', function () {
  // TODO: Routing?
  if (window.location.href.indexOf('questionAdministration') === -1) {
    return;
  }

  const isCopyMode = $('#form_copy_question').length > 0;

  // Initialice Ace editors if needed
  const aceInputs = $('.ace:not(.none)');
  if (aceInputs.length) {
    aceInputs.ace({
      'mode': 'javascript',
      'highlightActiveLine': false
    });
  }

  let scriptEditorInstance = null;
  let scriptEditorTarget = null;
  let scriptEditorContainerElement = null;
  let scriptEditorResizeHandler = null;

  function fetchScriptModalElements() {
    const element = document.getElementById('question-script-modal');
    if (!element) {
      return null;
    }
    const titleElement = element.querySelector('.modal-title');
    const defaultTitle = element.getAttribute('data-default-title') || (titleElement ? titleElement.textContent : '');
    const editorContainer = document.getElementById('question-script-modal-editor');

    return {
      element,
      titleElement,
      defaultTitle,
      editorContainer
    };
  }

  function prepareScriptModal() {
    const elements = fetchScriptModalElements();
    if (!elements) {
      return null;
    }

    if (elements.element.dataset.scriptEditorBound !== 'true') {
      elements.element.addEventListener('shown.bs.modal', () => {
        const latestElements = fetchScriptModalElements();
        const editor = ensureScriptEditorInstance(latestElements ? latestElements.editorContainer : null);
        if (editor) {
          window.setTimeout(() => {
            editor.resize(true);
            editor.focus();
          }, 0);
          if (scriptEditorResizeHandler) {
            window.removeEventListener('resize', scriptEditorResizeHandler);
          }
          scriptEditorResizeHandler = () => {
            editor.resize(true);
          };
          window.addEventListener('resize', scriptEditorResizeHandler);
        }
      });

      elements.element.addEventListener('hidden.bs.modal', () => {
        scriptEditorTarget = null;
        const latestElements = fetchScriptModalElements();
        if (latestElements && latestElements.titleElement) {
          latestElements.titleElement.textContent = latestElements.defaultTitle;
        }
        if (scriptEditorResizeHandler) {
          window.removeEventListener('resize', scriptEditorResizeHandler);
          scriptEditorResizeHandler = null;
        }
      });

      elements.element.dataset.scriptEditorBound = 'true';
    }

    return elements;
  }

  function ensureScriptEditorInstance(container) {
    if (!container || typeof ace === 'undefined') {
      return null;
    }
    if (!scriptEditorInstance || scriptEditorContainerElement !== container) {
      scriptEditorInstance = ace.edit(container);
      scriptEditorContainerElement = container;
      scriptEditorInstance.session.setMode('ace/mode/javascript');
      scriptEditorInstance.setOptions({
        wrap: true,
        tabSize: 4,
        useSoftTabs: true,
        highlightActiveLine: true
      });
    }
    return scriptEditorInstance;
  }

  prepareScriptModal();

  $(document).off('click.scriptEditor', '.script-editor-fullscreen').on('click.scriptEditor', '.script-editor-fullscreen', function (event) {
    event.preventDefault();
    const targetFieldId = $(this).attr('data-target-field-id') || $(this).data('targetFieldId');
    if (!targetFieldId) {
      console.warn('Script editor: missing target field id on trigger.');
      return;
    }

    const targetElement = document.getElementById(targetFieldId);
    if (!targetElement) {
      console.warn(`Script editor: target field ${targetFieldId} not found.`);
      return;
    }

    const modalElements = prepareScriptModal();
    if (!modalElements || typeof bootstrap === 'undefined') {
      console.error('Script editor modal is not available.');
      return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElements.element);
    const editor = ensureScriptEditorInstance(modalElements.editorContainer);
    if (!modal || !editor) {
      console.error('Script editor modal is not available.');
      return;
    }

    scriptEditorTarget = targetElement;
    const $target = $(targetElement);
    let currentValue = '';

    try {
      if (typeof $target.ace === 'function' && $target.ace('check')) {
        currentValue = $target.ace('val');
      } else {
        currentValue = $target.val();
      }
    } catch (err) {
      currentValue = $target.val();
    }

    editor.session.setValue(currentValue || '');
    editor.session.getUndoManager().reset();
    editor.clearSelection();
    editor.gotoLine(1, 0, false);

    const readOnly = $target.prop('readonly') === true;
    editor.setReadOnly(readOnly);

    if (modalElements.titleElement) {
      const titleFromButton = $(this).attr('data-modal-title') || $(this).data('modalTitle');
      modalElements.titleElement.textContent = titleFromButton || modalElements.defaultTitle;
    }

    modal.show();
  });

  // TODO: Remove this when Vue topbar is removed.
  $('#vue-topbar-container').hide();

  // TODO: Does not work with pjax loading.
  /** @type {Object} */
  let languageJson;
  const value = $('input[name=translation-strings-json]').val();
  try {
    languageJson = JSON.parse(unescape(value));
  } catch (e) {
    console.error('Could not parse language JSON - not on question editor page?');
    return;
  }

  /** @type {boolean} Used in ajaxcheckdup */
  let check = true;

  /** @type {number} */
  const sid = parseInt($('input[name=sid]').val());
  if (isNaN(sid)) {
    console.error('No survey id found - not on question editor page?');
    return;
  }

  /*:: declare function updateRowProperties(): void */
  /**
   * Rebind onclick events for subquestions and answer options?
   *
   * @return {void}
   */
  function updateRowProperties() {
    var sID = $('input[name=sid]').val();
    var gID = $('[name=question\\[gid\\]]').val();
    var qID = $('[name=question\\[qid\\]]').val();
    sID = $.isNumeric(sID) ? sID : '';
    gID = $.isNumeric(gID) ? gID : '';
    qID = $.isNumeric(qID) ? qID : '';

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
      });
    });
  }

  /**
   * TODO: Needed when sorting? Why?
   *
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
    $('.relevance').off('focus').on('focus', (event) => {
      if (event.relatedTarget && $(event.relatedTarget).hasClass('relevance')) {
        // Don't do anything when moving between relevance fields.
        return;
      }
      if ($('#rel-eq-th').hasClass('col-lg-4')) {
        // If already expanded, do not expand again.
        return;
      }
      $('#rel-eq-th').toggleClass('col-lg-1 col-lg-4', 'fast');
      $('.relevance').tooltip('dispose');
    });
    $('.relevance').off('blur').on('blur', (event) => {
      if (event.relatedTarget && $(event.relatedTarget).hasClass('relevance')) {
        // Don't do anything when moving between relevance fields.
        return;
      }
      if ($('#rel-eq-th').hasClass('col-lg-1')) {
        // If already collapsed, do not collapse again.
        return;
      }
      $('#rel-eq-th').toggleClass('col-lg-1 col-lg-4', 'fast');
      LS.doToolTip();
      //$('.relevance').each((index, element) => {
      //  bootstrap.Tooltip.getOrCreateInstance(element);
      //});
    });
    LS.doToolTip();
  }

  function bindSubQuestionEvents() {
    $('.btnaddsubquestion').off('click.subquestions').on('click.subquestions', addSubquestionInput);
    $('.btndelsubquestion').off('click.subquestions').on('click.subquestions', deleteSubquestionInput);
    bindExpandRelevanceEquation();
  }

  function bindAnswerEvents() {
    $('.btnaddanswer').off('click.subquestions').on('click.subquestions', addAnswerOptionInput);
    $('.btndelanswer').off('click.subquestions').on('click.subquestions', deleteAnswerOptionInput);
  }

  function toggleLanguageElements() {
    // get selected lang from dropdown
    let lang = $('.active .lang-switch-button').data('lang');
    //fallback: display main language
    if (lang === undefined) {
      const languages = languageJson.langs.split(';');
      lang = languages[0];
    }
    const langClass = `.lang-${lang}`;
    $('.lang-hide').hide();
    $(langClass).show();
  }

  /**
   * @param {number} position
   * @param {string} source Either 'subquestions' or 'answeroptions'
   * @return {Promise<XMLHttpRequest>}
   */
  function fetchLabelSetPredefined(position /*: number */, source /*: string */) /*: Promise<XMLHttpRequest> */ {
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
      alert('Internal error: Found no data element in fetchLabelSetPredefined');
      throw 'abort';
    }

    const scaleId = $('#current_scale_id').val();
    // We build the datas for the request
    const datas = {
      surveyid: $dataInput.data('surveyid'),
      gid: $dataInput.data('gid'),
      codes: JSON.stringify({lbl_1: 'eins'}),  // jshint ignore:line
      scale_id: scaleId,  // jshint ignore:line
      position: position,
      type: 'subquestion',
      languages: JSON.stringify($dataInput.data('languages').join(';')),
      subqid: '-QUIDPLACEHOLDER-'
    };
    // We get the HTML of the new row to insert
    return $.ajax({
      type: 'GET',
      contentType: 'application/json',
      url: $dataInput.data('url'),
      data: datas,
    });
  }

  /**
   * Get toolrip data for relevance equation.
   * If expanded, returns empty string
   *
   * @return {string}
   */
  //function getRelevanceToolTip() {
  //const relevanceTooltip = !relevanceIsExpanded() ? `data-bs-toggle="tooltip" data-title="${clickToExpand}"` : '';
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
        alert('Internal error in quick add: ' + errormessage);
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
        const rowId = `#row_${languages[x]}_${info[2]}_${info[3]}`;
        const $tablerow = $(rowId);
        if ($tablerow.length === 0) {
          console.error('info', info);
          alert('Internal error: Could not find row to delete with id ' + rowId);
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
      bindSubQuestionEvents()
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
      bindAnswerEvents();
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
   * @todo Used in label sets? But not in question editor?
   * @todo Remove
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
    $('#labelsets').select2('destroy');
    $('#labelsetpreview').empty();
    $('#labelsetalert').hide();
  }

  /**
   * Previews the labels in a modal after selecting it in the select box
   *
   * @param {number} lid Label set id
   * @return {void}
   */
  function showLabelSetPreview(lid /*: number */) /*: void */ {
    $('#labelsetpreview').html($('#labelsetsLoader').html());
    hideLabelSetAlert();
    return $.ajax({
      url: languageJson.lsdetailurl,
      data: {sid, lid},
      cache: true,
        success(json /*: {success: bool, results: Array<{label_name: string, labels: Array<{code: string, title: string}>}>, languages: {}} */) {
        if (json.success !== true) {
          $('#labelsetpreview').empty();
          showLabelSetAlert(languageJson.labelSetNotFound, 'danger'); // This could mean the label set is not found or it has no languages
        } else {
          if (json.languages === []) {
            alert('Internal error: No languages');
            throw 'abort';
          }

          const $liTemplate = $('<li class="nav-item" role="presentation"></li>');
          const $aTemplate = $('<button type="button" role="tab" class="nav-link" data-bs-toggle="tab" data-bs-target=""></button>');
          const $tabTodyTemplate = $('<div></div>');
          const $listTemplate = $('<div class="list-group selector_label-list container-fluid"></div>');
          const $listItemTemplate = $('<div class="row mb-3 selector_label-list-row"></div>');
          const $tabindex = $('<ul class="nav nav-tabs" role="tablist"></ul>');
          const $tabbody = $('<div class="tab-content" id="label-set-tab-content" style="max-height: 50vh; overflow:auto;"></div>');

          $('#labelsetpreview').empty();

          let hasInvalidCodes = false;
          let isEmpty = true;
          const source = $('#labelsetbrowserModal').data('source');
          let i = 0;
          $.each(json.languages, (language, languageName) => {
            const $linkItem = $aTemplate.clone();
            const $bodyItem = $tabTodyTemplate.clone();
            let $itemList = $listTemplate.clone();

            const classLink = i === 0 ? 'active' : '';
            const classBody = i === 0 ? 'tab-pane fade show active' : 'tab-pane fade';
            i++;

            $linkItem.addClass(classLink).attr('href', `#language_${language}`).text(languageName);
            $linkItem.data('bs-target', languageName);
            $liTemplate.clone().append($linkItem).appendTo($tabindex);

            $bodyItem.addClass(classBody).attr('id', `language_${language}`);
            $tabbody.append($bodyItem);

            const labelSet = json.results[language];

            $itemList = $listTemplate.clone();

            if (labelSet.labels) {
              isEmpty = false;
              labelSet.labels.forEach((label) => {
                // Label title is not concatenated directly because it may have non-encoded HTML
                const $labelTitleDiv = $('<div class="col-lg-9"></div>');
                $labelTitleDiv.text(label.title);
                const $listItem = $listItemTemplate.clone();
                $listItem.append(`<div class="col-lg-3 text-end" style="border-right: 4px solid #cdcdcd">${label.code}</div>`);
                $listItem.append($labelTitleDiv);
                $listItem.attr('data-label', JSON.stringify(label));
                $itemList.append($listItem);

                if (source === 'answeroptions' && label.code.length > 5) {
                  hasInvalidCodes = true;
                }
              });
            }

            $bodyItem.append(`<h2>${labelSet.label_name}</h2>`);  // jshint ignore: line
            $itemList.appendTo($bodyItem);
          });

          if (isEmpty) {
            showLabelSetAlert(languageJson.labelSetEmpty);
          } else {
            if (hasInvalidCodes) {
              showLabelSetAlert(languageJson.answeroptions.truncationWarning);
            }
            $('<div></div>').append($tabindex).append($tabbody).appendTo($('#labelsetpreview'));
            $tabindex.find('li').first().find('a').trigger('click');
          }
        }
      },
      error(jqXHR, textStatus, errorThrown) {
        $('#labelsetpreview').empty();
        showLabelSetAlert(languageJson.labelSetFail, 'danger');
        console.error(errorThrown);
      },
    });
  }

  /**
   * Shows an alert in the label set's modal
   *
   * @param {string} message The message to show
   * @param {?string} type Alert type (eg. 'danger')
   * @return {void}
   */
  function showLabelSetAlert(message /*: mixed */, type /*?: string */) /*: void */ {
      if (typeof message !== 'string') {
        throw 'expected string';
      }
      const alertType = type ?? 'warning';
      const alert = $('#labelsetalert');
      window.LS.ajaxAlerts(message, alertType, {inline: '#labelsetalert', class: 'ls-space margin bottom-0 top-15'});
      alert.show();
  }

  /**
   * Hides the alert in the label set's modal
   *
   * @return {void}
   */
  function hideLabelSetAlert() /*: void */ {
    $('#labelsetalert').empty().hide();
  }

  /**
   * Initialise the "Save as label set" modal.
   *
   * @param {Event} e
   * @return {void}
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

    $('#labelsetsModalContent').hide();
    $('#labelsetsLoader').show();

    // TODO: Send as input, not in DOM.
    if ($('#current_scale_id').length === 0) {
        $('body').append(`<input type="hidden" id="current_scale_id" value="${scaleId}" name="current_scale_id" />`);
    } else {
        $('#current_scale_id').val(scaleId);
    }
    $('#labelsets').select2({
        dropdownParent: $('#labelsetbrowserModal'),
        theme: 'bootstrap-5'
    });
    $('#labelsetpreview').html('');
    $('#labelsetsSelectorContainer').hide();
    hideLabelSetAlert();
    $.ajax({
      url: languageJson.lspickurl,
      data: {
        sid,
        match: 1,
        language: languageJson.langs.split(';')[0]
      },
      success(jsonString) {
        if (jsonString.success !== true) {
          showLabelSetAlert(languageJson.strNoLabelSet);
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
          $('#labelsetsSelectorContainer').show();
          $('#btnlsreplace').removeClass('disabled');
          $('#btnlsinsert').removeClass('disabled');
          $('#btnlsreplace').removeAttr('disabled');
          $('#btnlsinsert').removeAttr('disabled');
        }
      },
      error(jqXHR, textStatus, errorThrown) {
        showLabelSetAlert(languageJson.labelSetFail, 'danger');
        console.error(errorThrown);
      },
      complete() {
        $('#labelsetsLoader').hide();
        $('#labelsetsModalContent').show();
      }
    });

    // Label set select2 element.
    $('#labelsets').off('select2:select').on('select2:select', function () {
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

    /**
     * result is {lang: html} object.
     * Why copy both from preview modal and fetch HTML from server? Because row from server is empty.
     */
    fetchLabelSetPredefined(1, source).then((result) => {
      // Awkward solution to the problem with looping through langs.
      const langIds = {};

      const arr = Object.entries(result);
      arr.forEach(function(entry) {
        const lang = entry[0];
        const row = entry[1];
        /*
        if (!(row instanceof HTMLElement)) {
          alert('Internal error: row is not an HTMLElement but a ' + (typeof row));
          throw 'abort';
        }
        */

        // TODO: Answer options
        const tableId = `#${source}_${lang}_${scaleId}`;
        const $table = $(tableId);
        if ($table.length === 0) {
          alert('Internal error: Found no table to add labels to with id ' + tableId);
          throw 'abort';
        }

        var currentIds = [];
        if (type === 'replace') {
          $table.find('tbody').find('tr').each((i, tableRow) => {
            $(tableRow).remove();
          });
        } else {
          $table.find('tbody').find('tr').each((i, tableRow) => {
            currentIds.push($(tableRow).data('common-id').split('_').shift());
          });
        }

        // Answer option IDs are generated randomly, so they repeat sometimes.
        // We keep track of the generated numbers to make sure they don't repeat.
        var generatedIds = currentIds;

        // Loop the preview table and copy rows to destination (subquestions or answer options).
        let importedLabelset = $('#labelsetpreview').find(`#language_${lang}`).find('.selector_label-list').find('.selector_label-list-row');
        if (importedLabelset.length === 0) {
          importedLabelset = $('#labelsetpreview').find(`.tab-pane:first`).find('.selector_label-list').find('.selector_label-list-row');
        }
        importedLabelset.each((i, item) => {
          try {
            const label /*: {code: string, title: string} */ = $(item).data('label');
            const $row = $(row);
            let $tr;
            // TODO: Use classes instead of if-statements.
            if (source === 'subquestions') {
              $tr = $row.eq(4);
            } else if (source === 'answeroptions') {
              $tr = $row.eq(2);
              // Make sure codes are limited to 5 characters
              label.code = label.code.substr(0, 5);
            } else {
              throw 'source is not subquestions or answeroptions: ' + source;
            }
            if ($tr.length === 0) {
              throw 'Found no $tr in transferLabels';
            }
            // Only define random ids the FIRST language we loop for.
            // Different translations still use the same question code in the input name.
            if (langIds[i] === undefined) {
              var randId = `new${Math.floor(Math.random() * 99999)}`;
              var tries = 1;
              while (generatedIds.includes(randId)) {
                if (tries > 100) {
                  throw 'Couldn\'t generate a unique ID';
                }
                randId = `new${Math.floor(Math.random() * 99999)}`;
                tries++;
              }
              generatedIds.push(randId);
              langIds[i] = randId;
            }
            $tr.attr('data-common-id', $tr.attr('data-common-id').replace(/-QUIDPLACEHOLDER-/, langIds[i]));
            $tr.attr('id', $tr.attr('id').replace(/-QUIDPLACEHOLDER-/, langIds[i]));
            $tr.find('[name]').each((j /*: number */, nameElement) => {
              $(nameElement).attr('name', $(nameElement).attr('name').replace(/-QUIDPLACEHOLDER-/, langIds[i]));
            });
            $tr.find('[id]').each((j /*: number */, idElement) => {
              $(idElement).attr('id', $(idElement).attr('id').replace(/-QUIDPLACEHOLDER-/, langIds[i]));
            });
            $tr.find('[href]').each((j /*: number */, hrefElement) => {
              $(hrefElement).attr('href', $(hrefElement).attr('href').replace(/-QUIDPLACEHOLDER-/g, langIds[i]));
            });

            if ($tr.find('td.code-title').find('input[type=text]').length > 0) {
              $tr.find('td.code-title').find('input[type=text]').val(label.code);
            } else if ($row.find('td.code-title').length > 0) {
              $tr.find('td.code-title').text(label.code);
            } else {
              throw 'Found nowhere to put label.code';
            }

            if ($tr.find('td.relevance-equation').find('input[type=text]').length > 0) {
              $tr.find('td.relevance-equation').find('input[type=text]').val(1);
            } else {
              // ??
            }

            $tr.find('td.subquestion-text, td.answeroption-text').find('input[type=text]').val(label.title);
            $table.find('tbody').append($tr);

            if (source === 'subquestions') {
              $table.find('.btnaddsubquestion').off('click.subquestions').on('click.subquestions', addSubquestionInput);
              $table.find('.btndelsubquestion').off('click.subquestions').on('click.subquestions', deleteSubquestionInput);
            } else {
              $table.find('.btnaddanswer').off('click.subquestions').on('click.subquestions', addAnswerOptionInput);
              $table.find('.btndelanswer').off('click.subquestions').on('click.subquestions', deleteAnswerOptionInput);
            }
          } catch (e) {
            alert('Internal error in transferLabels: ' + e);
            throw 'abort';
          }
        });

      });

      // NB: Fails with Error: cannot call methods on sortable prior to initialization; attempted to call method 'refresh'
      // Not needed?
      //$('.answertable tbody').sortable('refresh');

      updateRowProperties();
      $('#labelsetbrowserModal').modal('hide');
      $('#current_scale_id').remove();
    }).catch(error => console.error(error));
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
   * @param {string} tableId - suqbuestions_$lang_$scaleid or answeroptions_$lang_$scaleid
   * @return {void}
   * @todo Unit-test this? How? With classes?
   * @todo Factor out functions.
   */
  function quickAddLabels(scaleId /*: number */, addOrReplace /*: string */, tableId /*: string */) {
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

    var currentIds = [];
    if ($closestTable.find('.code').length < 0) {
      $closestTable.find('.code-title').each(function () {
        codes.push($(this).text());
      });
    } else {
      $closestTable.find('.code').each(function () {
        codes.push($(this).val());
        currentIds.push($(this).closest('tr').data('common-id').split('_').shift());
      });
    }

    const languages = languageJson.langs.split(';');
    const promises = [];
    // TODO: Doc answers
    const answers = {};
    const lsrows = $('#quickaddarea').val().split('\n');
    const allrows = $closestTable.find('tbody tr').length;
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

    // Answer option IDs are generated randomly, so they repeat sometimes.
    // We keep track of the generated numbers to make sure they don't repeat.
    var generatedIds = currentIds;

    // TODO: Document value
    // NB: splitCSV is added to string prototype in adminbasics.
    lsrows.forEach((value /*: string & {splitCSV: string => Array<string>} */, k /*: number */) => {
      const thisrow = value.splitCSV(separatorchar);

      if (thisrow.length <= languages.length) {
        let numericCode = (parseInt(k) + 1);
        if (lsreplace === false) {
          numericCode += (parseInt(allrows));
        }
        let qCode = numericCode.toString();
        while (qCode.length < numericSuffix.length) {
          qCode = `0${qCode}`;
        }
        let prefix = codeSigil.slice(0, Math.max(0, 5 - qCode.length)).join('');
        thisrow.unshift(prefix + qCode);
      } else {
        thisrow[0] = thisrow[0].replace(/[^A-Za-z0-9]/g, '').substr(0, 20);
      }

      var quid = `new${Math.floor(Math.random() * 99999)}`;
      var tries = 1;
      while (generatedIds.includes(quid)) {
        if (tries > 100) {
          throw 'Couldn\'t generate a unique ID';
        }
        quid = `new${Math.floor(Math.random() * 99999)}`;
        tries++;
      }
      generatedIds.push(quid);

      // TODO: What's happening here?
      languages.forEach((language, x) => {
        if (typeof thisrow[parseInt(x) + 1] === 'undefined') {
          thisrow[parseInt(x) + 1] = thisrow[1];
        }

        if (!answers[language]) {
          answers[language] = [];
        }
        if (lsreplace) {
          if (tableId.indexOf('subquestion') > -1) {
            $(`#subquestions_${language}_${scaleId} tbody`).empty();
          } else {
            $(`#answeroptions_${language}_${scaleId} tbody`).empty();
          }
        }
        answers[language].push(
          { text: thisrow[(parseInt(x) + 1)], code: thisrow[0], quid },
        );
      });
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
        args.forEach((item /*: {lang: string, langtable: {}, html: string} */) => {
          answers[item.lang].forEach((row /*: {quid: string, text: string, code: string} */) => {
            try {
              const { html } = item;
              const htmlQuid = html.replace(/-QUIDPLACEHOLDER-/g, row.quid);
              // Create HTMLElement from HTML string.
              const wrapper = document.createElement('tbody');
              wrapper.innerHTML = htmlQuid;
              const tableRow = wrapper.firstElementChild;
              if (!tableRow) {
                throw 'Could not find tableRow with id ' + htmlQuid;
              }
              if (!(tableRow instanceof HTMLElement)) {
                throw 'tableRow is not an HTMLElement';
              }

              // Insert value and text into HTML.
              const inputText = tableRow.querySelector('input.answer');
              if (!(inputText instanceof HTMLInputElement)) {
                throw 'inputText is not an HTMLInputElement';
              }
              inputText.value = row.text;
              let inputCode = tableRow.querySelector('input.code');
              if (inputCode instanceof HTMLInputElement) {
                inputCode.value = row.code;
              } else {
                // If there is no input for the code, it's probably a "secondary" language.
                inputCode = tableRow.querySelector('td.code-title');
                if (inputCode instanceof HTMLElement) {
                  inputCode.textContent = row.code;
                }
              }
              const relevanceEquation = tableRow.querySelector('td.relevance-equation input');
              if (relevanceEquation instanceof HTMLInputElement) {
                relevanceEquation.value = '1';
              } else {
                // Do nothing.
              }
              $(item.langtable).find('tbody').append(tableRow);
            } catch (e) {
              alert('Internal error in quickAddLabels:' + e);
              throw 'abort';
            }
          });
        });
        $('#quickaddarea').val('');
        $('.answertable tbody').sortable('refresh');
        updateRowProperties();
        $('#quickaddModal').modal('hide');
        bindExpandRelevanceEquation();
      },
      function () {
        /* $('#quickadd').dialog('close'); */
        $('#quickaddarea').val('');
        $('.answertable tbody').sortable('refresh');
        updateRowProperties();
        $('#quickaddModal').modal('hide');
        bindExpandRelevanceEquation();

        // Unbind and bind events.
        $(`.answer`).off('focus');
        bindSubQuestionEvents();
        bindAnswerEvents();
      },
    );
  }

  /**
   * @return {void}
   */
  function getLabel() {
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
            template.innerHTML = `<div id="lasets" class="mb-3 label-name-wrapper">
                 <label class="form-label" for="laname">${languageJson.sLabelSetName}:</label>
                 <input class="form-control"" type="text" name="laname" id="laname">
               </div>`;
            child = template.content.firstElementChild;
            if (child) {
              targetParent.after(child);
            }
            break;
        // Replace an existing label set.
        case 'replacelabel':
            template.innerHTML = `
              <div id="laname" class="mb-3 label-name-wrapper">
                <select class="form-select" name="laname">
                  <option value=""></option>
                </select>
              </div>'
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
            $.getJSON(languageJson.lanrestrictedurl, (data) => {
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
        languages: languageJson.langs.split(';')
      },
      /**
       * @param {any} data
       * @return {void}
       */
      success(successMessage) {
        LS.LsGlobalNotifier.createAlert(successMessage, 'success', {showCloseButton: true});
      },
      /**
       * @param {any} data
       * @return {void}
       */
      error(data) {
        if (data.responseJSON) {
          LS.LsGlobalNotifier.createAlert(data.responseJSON.message, 'danger', {showCloseButton: true});
        } else {
          alert('Internal eror from Ajax call');
          throw 'abort';
        }
      }
    });

     /*
   }).fail((xhr, textStatus, errorThrown) => {
     if (xhr.status === 500) {
       LS.LsGlobalNotifier.create(
         errorThrown,
         'card-body bg-danger text-center'
       );
     } else if (xhr.status === 401) {
       LS.LsGlobalNotifier.create(
         "Not logged in",
         'card-body bg-warning text-center'
       );
     } else {
       LS.LsGlobalNotifier.create(
         xhr.responseJSON.message,
         'card-body bg-danger text-center'
       );
     }
   }).complete((xhr) => {
   }).success((xhr) => {
   });;
   */
  }

  /**
   * Update translation of subquestion/answer option after sort.
   *
   * @param {event} event
   * @param {object} ui ??
   * @return {void}
   */
  function aftermove(event, ui) {
    // But first we have change the sortorder in translations, too
    const $that = ui.item;
    const newindex = Number($that.parent().children().index($that) + 1);
    const oldindex = $that.data('oldindex');
    const scaleId = $that.closest('table').data('scaleid');

    // Process the corresponding scale id table for each extra language
    $that.closest('.tab-pane').find(".extra-lang  table[data-scaleid='" + scaleId + "']").each((idx, table) => {
      if (newindex < oldindex) {
        $(table).find(`tbody tr:nth-child(${newindex})`).before($(table).find(`tbody tr:nth-child(${oldindex})`));
      } else {
        $(table).find(`tbody tr:nth-child(${newindex})`).after($(table).find(`tbody tr:nth-child(${oldindex})`));
      }
    });
  }

  /**
   * @param {Event} event
   * @param {string} tableClassName 'subquestions-table' or 'answeroptions-table'
   * @return {void}
   */
  function onClickSaveLabelSet(event /*: Event */, tableClassName /*: string */) {
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
      const aLanguages = languageJson.langs.split(';');
      $.post(languageJson.sCheckLabelURL, { languages: aLanguages, lid, bCheckAssessments: 1 }, (data) => {
        $('#strReplaceMessage').html(data);
        const modal = new bootstrap.Modal(document.getElementById('dialog-confirm-replaceModal'), {});
        modal.show();
        $('#btnlconfirmreplace').click(() => {
          saveLabelSetAjax(event, tableClassName);
        });
      });
    }
  }

  /**
   * Check if table has any duplicated code.
   *
   * @param {HTMLElement} table
   * @return {boolean}
   * @todo Dual scale
   */
  function checkSubquestionCodeUnique(table /*: HTMLElement */, msg /*: string */) /*: boolean */ {
    const codeInputs = table.querySelectorAll('input.code');
    // Get all codes from code input node list.
    const codes = [...codeInputs].map((input) => {
      if (input instanceof HTMLInputElement) {
        return input.value;
      } else {
        throw 'input is not an HTMLInputElement';
      }
    });
    const duplicateCodes = codes.filter((value, index, self) => self.indexOf(value) !== index);
    codeInputs.forEach((input, key) => {
      if (input instanceof HTMLInputElement) {
        const parent = $(input.parentElement);
        if (duplicateCodes.includes(input.value)) {
          parent.addClass('has-error');
        } else {
          parent.removeClass('has-error');
        }
      }
    });
    return duplicateCodes.length == 0;
  }

  /**
   * Return a function that can be used to check code uniqueness.
   * Used by subquestions and answer options.
   *
   * @param {string} msg Error message to show.
   * @return {(HTMLInputElement) => boolean} HTMLInputElement is the table row or element.
   */
  function createCheckUniqueFunction(msg /*: string */) /*: (HTMLElement) => boolean */ {
    return (that) => {
      const table = that.closest('table');
      if (!(table instanceof HTMLElement)) {
        throw 'Found no table';
      }

      var hasError = false;

      // Check uniqueness.
      if (!checkSubquestionCodeUnique(table, msg)) {
        LS.LsGlobalNotifier.createAlert(msg, 'danger', {showCloseButton: true});
        hasError = true;
      }

      // Check too long subquestion code.
      // NB: Might not be input element when checking from "Save" button.
      // TODO: maxlength attribute
      if (that instanceof HTMLInputElement) {
        const code = that.value;
        if (code.length > 20) {
          $(that.parentElement).addClass('has-error');
          LS.LsGlobalNotifier.createAlert('Subquestion code is too long. Maximal number of characters is: 20.', 'danger', {showCloseButton: true});
          hasError = true;
        }
      }

      if (hasError) {
        return false;
      }

      $(that.parentElement).removeClass('has-error duplicate-code');
      return true;
    };
  }

  /**
   * Makes the answer's table sortable
   */
  function makeAnswersTableSortable() /*: void */ {
    $('.answertable tbody').sortable({
      containment: 'parent',
      start: startmove,
      stop: endmove,
      update: aftermove,
      handle: '.move-icon',
      distance: 3,
    });
  }

  // Public functions for LS.questionEditor module.
  LS.questionEditor = {
    /**
     * Update question attributes (general and advanced settings) when selecting question type.
     * Used by question selector modal.
     *
     * @param {string} questionType - One-letter string of question type
     * @param {string} questionTheme - One-letter string of question type
     * @param {string} generalSettingsUrl - URL to controller to fetch new HTML for General Settings
     * @param {string} advancedSettingsUrl - URL to controller to fetch new HTML for Advanced Settings
     * @param {string} extraOptionsUrl - URL to controller to fetch new HTML for Extra Options (Subquestions/Answers)
     * @return {Promise}
     */
    // eslint-disable-next-line no-unused-vars
    updateQuestionAttributes: async function (questionType, questionTheme, generalSettingsUrl, advancedSettingsUrl, extraOptionsUrl) {  // jshint ignore:line
      // If same question type, do nothing.
      // Else, fetch new HTML from server.
      $('#ls-loading').show();

      const generalSettingsPromise = new Promise((resolve, reject) => {
        $.ajax({
          url: generalSettingsUrl,
          method: 'GET',
          data: { questionType, questionTheme, code },
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
          data: { questionType, questionTheme },
          dataType: 'html',
          success: (data) => {
            resolve(data);
          },
          error: (data) => {
            reject(data);
          },
        });
      });
      const extraOptionsPromise = new Promise((resolve, reject) => {
        $.ajax({
          url: extraOptionsUrl,
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
        var code = $('#questionCode').val();
        const [generalSettingsHtml, advancedSettingsHtml, extraOptionsHtml] = await Promise.all([generalSettingsPromise, advancedSettingsPromise, extraOptionsPromise]);
        const currentGroup = $('#gid').children("option:selected").val();
        $('#general-settings').replaceWith(generalSettingsHtml);
        $('#questionCode').val(code);
        var wrapperEl = $('#question-type-selector-wrapper');
        var viewType = wrapperEl.data('viewtype');
        var debug = wrapperEl.data('debug');
        var runner_questionTypeSelector = new PreviewModalScript("questionTypeSelector", {
          "selectedClass": "text-short",
          "onUpdate": ["value", "theme", "$('#question_type').val(value); $('#question_theme_name').val(theme); LS.questionEditor.updateQuestionAttributes(value, theme, '" + generalSettingsUrl + "', '" + advancedSettingsUrl + "', '" + extraOptionsUrl + "');"],
          "value": questionType,
          "theme": questionTheme,
          "debugString": "Type: ",
          "debug": debug == 1,
          "viewType": viewType
        });
        runner_questionTypeSelector.bind();

        $('#gid').val(currentGroup);
        // TODO: Double check HTML injected here. Extra div?
        $('#advanced-options-container').replaceWith(advancedSettingsHtml);
        $('#extra-options-container').replaceWith(extraOptionsHtml);
        makeAnswersTableSortable();
        $('.question-option-help').hide();
        $('#ls-loading').hide();

        $('.lang-hide').hide();
        const languages = languageJson.langs.split(';');
        $('.lang-switch-button[data-lang="' + languages[0] + '"]').trigger('click');

        bindSubQuestionEvents();
        bindAnswerEvents();
      } catch (ex) {
        $('#ls-loading').hide();
        // TODO: How to show internal errors?
        // eslint-disable-next-line no-alert
        console.error(ex);
        alert(`Internal error in updateQuestionAttributes: ${ex}`);
      }
    },

    // Show summary page.
    showOverview: function () {
      $('#advanced-question-editor').hide();
      $('#question-create-edit-topbar').hide();
      $('#question-overview').show();
      $('#question-summary-topbar').show();
    },

    showEditor: function () {
      $('#question-overview').hide();
      $('#question-summary-topbar').hide();
      $('#advanced-question-editor').show();
      $('#question-create-edit-topbar').show();
    },

    /**
     * Used in onclick event in Twig template.
     *
     * @param {Event} event
     * @param {string} source
     */
    showLabelSetPicker: function(event /*: Event */, source /*: string */) {
      $('#btnlsreplace').off('click');
      $('#btnlsinsert').off('click');
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

      $('#labelsetbrowserModal').data('source', source);
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
    checkQuestionValidateTitle: function(code, qid) {
      $('#questionCode')[0].setCustomValidity('');
      $('#question-title-warning').text('');
      $('#question-title-warning').addClass('d-none');
      $.ajax({
        url: languageJson.checkQuestionValidateTitleURL,
        method: 'GET',
        data: {
          sid,
          qid,
          code
        },
        success: (data) => {
          const message = data.message;
          if (message !== null) {
              $('#question-title-warning').removeClass('d-none');
              $('#question-title-warning').text(message);
              $('#questionCode')[0].setCustomValidity(message); // must set customvalidity to avoid submit by another enter
          } else {
              // Continue
          }
        },
        error: (data) => {
          alert('Internal error in checkQuestionValidateTitle: ' + JSON.stringify(data));
          throw 'abort';
        }
      });
    },

    /**
     * When clicking save, first check if codes etc are valid.
     * Also post using Ajax.
     *
     * @param {Event} event
     * @param {string} tabQuestionEditor
     * @return {boolean}
     */
    checkIfSaveIsValid: function(event /*: Event */, tabQuestionEditor = 'editor') {
      $('#ls-loading').show();
      event.preventDefault();
      const qid = parseInt($('input[name="question[qid]"]').val());
      const code = $('input[name="question[title]"]').val();
      const target = event.currentTarget;
      if (!(target instanceof HTMLElement)) {
        alert('Internal error in checkIfSaveIsValid: target is not an HTMLElement, but ' + typeof target);
        $('#ls-loading').hide();
        return false;
      }
      const saveWithAjax = target.dataset.saveWithAjax === 'true';
      const form = document.getElementById('edit-question-form');
      if (!(form instanceof HTMLFormElement)) {
        $('#ls-loading').hide();
        throw 'form is not HTMLFormElement';
      }
      /* Check if input are HTML5 valid */
      if (!form.checkValidity() ) {
          // the form is invalid : show invalid part
          form.reportValidity();
          $('#ls-loading').hide();
          return false;
      }

      const firstSubquestionRow = document.querySelector('.subquestions-table tr');
      if (firstSubquestionRow) {
        // This will show error message if subquestion code is not unique.
        if (!LS.questionEditor.showSubquestionCodeUniqueError(firstSubquestionRow)) {
          $('#ls-loading').hide();
          return false;
        }
      }

      const firstAnsweroptionRow = document.querySelector('.answeroptions-table tr');
      if (firstAnsweroptionRow) {
        // This will show error message if answer option code is not unique.
        if (!LS.questionEditor.showAnswerOptionCodeUniqueError(firstAnsweroptionRow)) {
          $('#ls-loading').hide();
          return false;
        }
      }

      const updateQuestionSummary = () => {
        $('#ls-loading').show();
        $.ajax({
          url: form.dataset.summaryUrl,
          method: 'GET',
          data: {},
          dataType: 'html',
          success: (summaryHtml) => {
            $('#ls-loading').hide();
            const isVisible = $('#question-overview').is(':visible');
            const newSummary = $(summaryHtml);
            if (isVisible) {
              newSummary.show();
            } else {
              newSummary.hide();
            }
            $('#question-overview').replaceWith(newSummary);
            // Quick action buttons are hidden in the html, and normally made visible by panelsAnimation() function of adminbasics.js,
            // which is triggered on document ready or pjax:scriptcomplete. To avoid messing with other things, we just do the animation
            // again here.
            $('.card-body').each(function (i) {
              $(this).delay(i++ * 200).animate({
                opacity: 1,
                top: '0px'
              }, 200);
            });
          },
          error: (response) => {
            $('#ls-loading').hide();
            alert('Internal error in updateQuestionSummary: ' + response);
            return false;
          },
        });
      };

      const reloadExtraOptions = () => {
        // Show loading gif.
        $('#ls-loading').show();
        // Post complete form to controller.
        $.get({
            url: languageJson.lsextraoptionsurl,
            success: (response /*: string */, textStatus /*: string */) => {
              $('#extra-options-container').replaceWith( response );
              bindSubQuestionEvents();
              bindAnswerEvents();
              makeAnswersTableSortable();
              toggleLanguageElements();
              // Hide loading gif.
              $('#ls-loading').hide();
            },
            error: (data) => {
              $('#ls-loading').hide();
              alert('Internal error from saveFormWithAjax: no data.responseJSON found');
              throw 'abort';
            }
        });
      };

      // Helper function after unique check.
      const saveFormWithAjax /*: (void) => (void) */ = () => {
        const data = {};
        const form = document.getElementById('edit-question-form');
        if (!(form instanceof HTMLFormElement)) {
          $('#ls-loading').hide();
          throw 'form is not HTMLFormElement';
        }

        try {
          for (let instanceName in CKEDITOR.instances) {
            CKEDITOR.instances[instanceName].updateElement();
          }
        } catch(e) {
          console.error('Seems no CKEDITOR4 is loaded');
        }

        $('#edit-question-form').serializeArray().forEach((x /*: {name: string, value: string} */) => {
          data[x.name] = x.value;
        });
        // Signal to controller that we're posting via Ajax.
        data.ajax = 1;

        // Show loading gif.
        $('#ls-loading').show();

        // Post complete form to controller.
        $.post({
          data,
          url: form.action,
          success: (response /*: string */, textStatus /*: string */) => {
            const json = JSON.parse(response);

            // Hide loading gif.
            $('#ls-loading').hide();

            // Update the side-bar.
            LS.EventBus.$emit('updateSideBar', {'updateQuestions': true});
            reloadExtraOptions();

            if (textStatus === 'success') {
              // Show confirm message.
              LS.LsGlobalNotifier.createAlert(json.message, 'success', {showCloseButton: true});
            } else {
              // Show error message.
              LS.LsGlobalNotifier.createAlert(json.message, 'danger', {showCloseButton: true});
            }
            updateQuestionSummary();
          },
          error: (data) => {
            $('#ls-loading').hide();
            if (data.responseJSON) {
              LS.LsGlobalNotifier.createAlert(data.responseJSON.message, 'danger', {showCloseButton: true});
            } else {
              alert('Internal error from saveFormWithAjax: no data.responseJSON found');
              throw 'abort';
            }
          }
        });
      };

      $.ajax({
        url: languageJson.checkQuestionValidateTitleURL,
        method: 'GET',
        data: {
          sid,
          qid,
          code
        },
        success: (data) => {
          const message = data.message;
          if (message !== null) {
              $('#question-title-warning').removeClass('d-none');
              $('#question-title-warning').text(message);
              $('#questionCode')[0].setCustomValidity(message); // must set customvalidity to avoid submit by another enter
              $('#ls-loading').hide();
          } else {
            $('#question-title-warning').addClass('d-none');
            $('#question-title-warning').text('');
            $('#questionCode')[0].setCustomValidity('');
            // TODO: Check other things too.
            const button = document.getElementById('submit-create-question');
            if (button instanceof HTMLElement) {
              if(tabQuestionEditor === 'editor'){
                $('#tab-overview-editor-input').val('editor');
              }else{
                $('#tab-overview-editor-input').val('overview');
              }

              if (saveWithAjax) {
                saveFormWithAjax();
              } else {
                // Just submit form.
                $('#ls-loading').show();
                button.click();
              }
              return true;
            }
          }
        },
        error: (response) => {
          alert('Internal error in checkQuestionValidateTitle: ' + response);
          throw 'abort';
        }
      });
      return false;
    },

    /**
     * Mark subquestion code as error if it's not unique.
     * Bound to subquestion code onfocusout.
     *
     * @param {HTMLElement} table
     * @return {void}
     * @todo Dual scale
     * @todo Check length of subquestion code.
     */
    showSubquestionCodeUniqueError: createCheckUniqueFunction(languageJson.subquestions.duplicatesubquestioncode),
    showAnswerOptionCodeUniqueError: createCheckUniqueFunction(languageJson.answeroptions.duplicateanswercode)
  };

  /**
   * questionCode need specific ajax validation
   */
  /** deactivate the check when needed */
  function deActivateQuestionCodeChecker() {
    $('#questionCode').off('blur keypress');
  }
  /** activate the check when event happen on questionCode */
  function activateQuestionCodeChecker() {
    $('#questionCode').on('blur', function() {
      let qid = 0;
      if ($(this).data('qid')) {
        qid = $(this).data('qid');
      }
      LS.questionEditor.checkQuestionValidateTitle($(this).val(), qid);
    });
    /* Check question code validatiry when press ENTER mantis #19440 */
    $('#questionCode').on('keypress', function(e) {
      if (e.which == 13) {
        e.preventDefault();
        deActivateQuestionCodeChecker();
        /* Set CustomValidity to empty to allow check again by checkIfSaveIsValid */
        $('#questionCode')[0].setCustomValidity('');
        $('#question-title-warning').text('');
        $('#question-title-warning').addClass('d-none');
        if (!LS.questionEditor.checkIfSaveIsValid(e, 'enter')) {
          activateQuestionCodeChecker();
        }
      }
    });

  }
  /* Attach event when ready */
  activateQuestionCodeChecker();
  /** */
  function showConditionsWarning(e) {
    if (!$(this).data('hasConditions')) {
      return;
    }
    $('#general-setting-help-relevance').show();
  }

  function showSameScriptForAllLanguagesWarning() {
    if ($('#same_script').is(":checked")) {
      $('.same-script-alert').removeClass("d-none");
    } else {
      $('.same-script-alert').addClass("d-none");
    }
  }

  /**
   * Updates the answer/subquestion code on secondary languages
   */
  function syncAnswerSubquestionCode() {
    const itemCode = $(this).val();
    const commonId = $(this).closest('tr').data('common-id');

    $(this).closest('.tab-pane').find(".extra-lang tr[data-common-id='" + commonId + "'] td.code-title").text(itemCode);
  }

  // Below, things run on pjax:scriptcomplete.

    makeAnswersTableSortable();

    bindSubQuestionEvents();
    bindAnswerEvents();

    $('#labelsetbrowserModal').on('hidden.bs.modal.', labelSetDestruct);

    $('#quickaddModal').on('show.bs.modal', (e) => {
      const scaleId = parseInt($(e.relatedTarget).data('scale-id'));
      const tableId = $(e.relatedTarget).closest('div.action-buttons').parent().find('table.answertable').attr('id');
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
    /*$('.ace:not(.none)').ace({
      mode: 'javascript',
    });*/

    // Hide help tips by default.
    $('.question-option-help').hide();

    // Check Answer Code is unique.
     $('#answerCode').focusout( () => {
        // Answer code
     });

    // Hide all language except the selected one.
    $('.lang-switch-button').on('click', function langSwitchOnClick(e) {
      e.preventDefault();
      const lang = $(this).data('lang');
      const langClass = `.lang-${lang}`;
      $('.lang-hide').hide();
      $(langClass).show();
      $('#language-dropdown-text').text($(this).text());
      // Mark the selected option
      $(this).closest('ul').find('li').each(function removeActiveClassFromLanguageListItems() {
        $(this).removeClass('active');
      });
      $(this).closest('li').addClass('active');
    });

    // Hide all languages except main.
    $('.lang-hide').hide();
    const languages = languageJson.langs.split(';');
    $('.lang-' + languages[0]).show();

    // Show 'Use for all languages' warning
    $('#same_script').on('change', showSameScriptForAllLanguagesWarning);
    showSameScriptForAllLanguagesWarning();

    // Land on summary page if qid != 0 (not new question).
    // TODO: Fix

    if (!isCopyMode) {
      const qidInput = document.querySelector('input[name="question[qid]"]');
      if (qidInput === null) {
        alert('Internal error: Could not find qidInput');
        throw 'abort';
      }
      if (qidInput instanceof HTMLInputElement) {
        if (parseInt(qidInput.value) === 0) {
          $('#question-create-edit-topbar').show();
        } else {
          if($('#tab-overview-editor-input').val() === 'editor'){
              $('#question-create-edit-topbar').show();
              $('#question-summary-topbar').hide();
          }else{
              $('#question-summary-topbar').show();
              $('#question-create-edit-topbar').hide();
          }
        }
      } else {
        alert('Internal error: qidInput is not an HTMLInputElement');
        throw 'abort';
      }
    }

    // Fix ace editor size for script fields
    $('textarea.ace:not(.none)').each(function() {
      var id = $(this).attr('id') + '__ace';
      var width = '100%';
      var height = 225;
      $('#' + id).width(width).height(height);
      $('#' + id).closest('.jquery-ace-wrapper').width(width).height(height);
    });

    $('#relevance').on('keyup', showConditionsWarning);

    $(document).on('focusout', '#subquestions table.subquestions-table:first-of-type td.code-title input.code', syncAnswerSubquestionCode);
    $(document).on('focusout', '#answeroptions table.answeroptions-table:first-of-type td.code-title input.code', syncAnswerSubquestionCode);
});
