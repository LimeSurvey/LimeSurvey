(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";Object.defineProperty(exports, "__esModule", { value: true });exports.ThemeScripts = void 0;





























var _template_core_class = _interopRequireDefault(require("./template_core_class.js"));function _interopRequireDefault(obj) {return obj && obj.__esModule ? obj : { "default": obj };} /*
    LimeSurvey
    Copyright (C) 2007-2023
    The LimeSurvey Project Team / Patrick Teichmann
    All rights reserved.
    License: GNU/GPL License v3 or later, see LICENSE.php
    LimeSurvey is free software. This version may have been modified pursuant
    to the GNU General Public License, and as distributed it includes or
    is derivative of works licensed under the GNU General Public License or
    other free or open source software licenses.


    (¯`·._.·(¯`·._.·  Theme main JavaScript   ·._.·´¯)·._.·´¯)

     Javascript file for this template.

     You'd rather not touch it. This file can be overwritten by an update.


     ██████╗  ██████╗     ███╗   ██╗ ██████╗ ████████╗    ████████╗ ██████╗ ██╗   ██╗ ██████╗██╗  ██╗    ██╗
     ██╔══██╗██╔═══██╗    ████╗  ██║██╔═══██╗╚══██╔══╝    ╚══██╔══╝██╔═══██╗██║   ██║██╔════╝██║  ██║    ██║
     ██║  ██║██║   ██║    ██╔██╗ ██║██║   ██║   ██║          ██║   ██║   ██║██║   ██║██║     ███████║    ██║
     ██║  ██║██║   ██║    ██║╚██╗██║██║   ██║   ██║          ██║   ██║   ██║██║   ██║██║     ██╔══██║    ╚═╝
     ██████╔╝╚██████╔╝    ██║ ╚████║╚██████╔╝   ██║          ██║   ╚██████╔╝╚██████╔╝╚██████╗██║  ██║    ██╗
     ╚═════╝  ╚═════╝     ╚═╝  ╚═══╝ ╚═════╝    ╚═╝          ╚═╝    ╚═════╝  ╚═════╝  ╚═════╝╚═╝  ╚═╝    ╚═╝

     Please, use custom.js

*/ /**
 * The general Template closure.
 * This is to capsule eventual errors inside of the template function, so the general script all run as the should
 */ // Global constructor, used in multiple twig files
// eslint-disable-next-line no-unused-vars
var ThemeScripts = function ThemeScripts() {// defining the console object once. Also filtered by the global setting
  // eslint-disable-next-line no-console
  var logObject = console.ls ? window.debugState.frontend ? console.ls : console.ls.silent : console; /**
   * The function focusFirst puts the Focus on the first non-hidden element in the Survey.
   * Normally this is the first input field (the first answer).
   */var focusFirst = function focusFirst() {$('#limesurvey :input:visible:enabled:first').focus();}; /**
   * fix padding of body according to navbar-fixed-top
   * in endpage and in $(window).resize
   */var fixBodyPadding = function fixBodyPadding() {/* The 60 px is fixed in template.css */$('body').css('padding-top', Math.round($('.navbar-fixed-top').height()) + 'px');}; /**
   * Hide some part if empty
   * Some can be needed if contain only js
   * Some are not really needed : little margin only is shown
   * Allowed content -> <script> and <img/>
   */var hideEmptyPart = function hideEmptyPart() {$('.question-help-container').each(function () {/* Only if have only script tag inside or empty tag */if ($(this).text().trim() == '' && !/<img/.test($(this).html())) {$(this).addClass('d-none');}});$('.group-description').each(function () {/* Only if have only script tag inside or empty tag */if ($(this).text().trim() == '' && !/<img/.test($(this).html())) {$(this).addClass('d-none');}
    });
    $('.question-help-container.hidden').on('html:updated', function () {
      if ($(this).text().trim() != '' && !/<img/.test($(this).html())) {
        $(this).removeClass('d-none');
      }
    });
    // .question-help-container:not(.hidden) don't work ?
    $('.question-help-container').on('html:updated', function () {
      if ($(this).text().trim() == '' && !/<img/.test($(this).html())) {
        $(this).addClass('d-none');
      }
    });
  };

  var initTopMenuLanguageChanger = function initTopMenuLanguageChanger(selectorItem, selectorGlobalForm) {
    // $(selectorContainer).height($('#main-row').height());
    $(selectorItem).on('click', function () {
      var lang = $(this).data('limesurvey-lang');
      /* The limesurvey form exist in document, move select and button inside and click */
      $(selectorGlobalForm + ' [name=\'lang\']').remove(); // Remove existing lang selector
      $('<input type=\'hidden\'>').attr('name', 'lang').val(lang).appendTo($(selectorGlobalForm));
    });
  };

  var init = function init() {

    /**
     * Code included inside this will only run once the page Document Object Model (DOM) is ready for JavaScript code to execute
     * @see https://learn.jquery.com/using-jquery-core/document-ready/
     * Also it will run on a complete pageload via the internal pjax system
     */
    $(document).on('ready pjax:scriptcomplete', function () {
      /* Uncomment below if you want to use the focusFirst function */
      //focusFirst();
      /* Some function are launched in endpage.pstpl */
      hideEmptyPart();
      // If list of nav-bar action is empty: remove it (else .navbar-toggle is shown on small screen) //
      if (!$('#navbar li').length) {
        $('#navbar').remove();
        $('[data-target=\'#navbar\']').remove();
      }

      // Scroll to first error - After user close modal -
      if ($('.input-error').length > 0 || $('.ls-em-error').length > 0) {
        $('#bootstrap-alert-box-modal').on('hidden.bs.modal', function () {

          var $firstError = $('.input-error').length > 0 ? $('.input-error').first() : $('.ls-em-error').first();

          var $pixToScroll = $firstError.offset().top - 100;
          $('html, body').animate({
            scrollTop: $pixToScroll + 'px'
          }, 'fast');
        });
      }

      $('.language-changer').each(function () {
        var $that = $(this);
        if (!$.trim($that.children('div').html())) {
          $that.hide();
        }
      });

      // Hide the menu buttons at the end of the Survey
      if ($('.hidemenubutton').length > 0) {
        $('.navbar-right').hide();
      }

      //Survey list container
      if ($('#surveys-list-container').length > 0) {
        var footerHeight = $('#surveyListFooter').outerHeight();
        var headerHeight = 2 * $('#navbar').outerHeight();
        var bodyHeight = $(document).height() - (footerHeight + headerHeight);
        logObject.log({
          footerHeight: footerHeight,
          headerHeight: headerHeight,
          bodyHeight: bodyHeight
        });
        $('#surveys-list-container').css('min-height', bodyHeight + 'px');
      }

      // Captcha action
      if ($('#reloadCaptcha').length > 0) {
        $('#reloadCaptcha').on('click', function (e) {
          e.preventDefault();
          window.location.reload();
        });
      }

      // Survey list footer
      if ($('#surveyListFooter').length > 0) {
        var $surveyListFooter = $('#surveyListFooter');
        $('#outerframeContainer').after($surveyListFooter);
      }

      $(function () {
        //show tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      });

      // Bind language changer onclick event.
      // This function is defined in assets/survey_themes/ls6_surveytheme/core/template_core_pre.js
      // eslint-disable-next-line no-undef
      activateLanguageChanger();

      //Fix the navigation overflow
      fixBodyPadding();
    });

    /**
     * Code included inside this will run each time windows is resized
     * @see https://api.jquery.com/resize/
     */
    $(window).resize(function () {
      fixBodyPadding();
    });
    var onkeyEventInput = function onkeyEventInput(e) {
      var code = e.keyCode ? e.keyCode : e.which;
      if (code == 13 && e.ctrlKey != true) {
        e.preventDefault();
        if ($(this).closest('.question-container').hasClass('multiple-short-txt')) {
          if ($(this).closest('.question-item').next('.question-item').length > 0) {
            $(this).closest('.question-item').next('.question-item').find('input, textarea').first().focus();
            return;
          }
        }
        $(this).closest('.question-container').next('.question-container').find('input, textarea').first().focus();
      } else if (code == 13 && e.ctrlKey == true) {
        $('.action--ls-button-submit').trigger('click');
      }
    };

    var onkeyEventTextarea = function onkeyEventTextarea(e) {
      var code = e.keyCode ? e.keyCode : e.which;
      if (code == 13 && e.altKey == true) {
        e.preventDefault();
        if ($(this).closest('.question-container').hasClass('multiple-short-txt')) {
          if ($(this).closest('.question-item').next('.question-item').length > 0) {
            $(this).closest('.question-item').next('.question-item').find('input, textarea').first().focus();
            return;
          }
        }
        $(this).closest('.question-container').next('.question-container').find('input, textarea').first().focus();
      } else if (code == 13 && e.ctrlKey == true) {
        $('.action--ls-button-submit').trigger('click');
      }
    };

    $(document).on('keydown', '.answer-container input', onkeyEventInput);
    $(document).on('keydown', '.answer-container textarea', onkeyEventTextarea);
  };

  var initUserForms = function initUserForms() {
    fixBodyPadding();
    hideEmptyPart();
  };
  var initGlobal = function initGlobal() {
    fixBodyPadding();
    if (jQuery.isFunction(window.templateCore.hideQuestionWithRelevanceSubQuestion)) {
      window.templateCore.hideQuestionWithRelevanceSubQuestion();
    }
    if (jQuery.isFunction(window.templateCore.hideMultipleColumn)) {
      window.templateCore.hideMultipleColumn();
    }
    hideEmptyPart();
  };

  var initWelcomePage = function initWelcomePage() {

    /* Nothing to do : keep it for compatibility (pre 3.0.1) */};

  return {
    init: init,
    initUserForms: initUserForms,
    initGlobal: initGlobal,
    initWelcomePage: initWelcomePage,
    focusFirst: focusFirst,
    fixBodyPadding: fixBodyPadding,
    hideQuestionWithRelevanceSubQuestion: window.templateCore.hideQuestionWithRelevanceSubQuestion,
    hideEmptyPart: hideEmptyPart,
    initTopMenuLanguageChanger: initTopMenuLanguageChanger,
    log: logObject
  };

};

// register to global scope
exports.ThemeScripts = ThemeScripts;window.ThemeScripts = ThemeScripts;

},{"./template_core_class.js":2}],2:[function(require,module,exports){
"use strict";Object.defineProperty(exports, "__esModule", { value: true });exports.TemplateCoreClass = void 0;var _template_core_pre = require("./template_core_pre.js");

















/**
 * @file Default template functionnality
 * @copyright LimeSurvey <http://www.limesurvey.org>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

var TemplateCoreClass = function TemplateCoreClass() {
  return {
    /**
     * Dialog and confirm
     */
    /* showStartPopups : replace core function : allow HTML and use it. Unusuable with ajax */
    showStartPopups: function showStartPopups() {
      if (LSvar.showpopup == 1 && $.isArray(LSvar.startPopups)) {
        var startPopups = LSvar.startPopups.map(function (text) {
          return "<p>" + text + "</p>";
        });
        window.templateCore.alertSurveyDialog(startPopups.join(""));
      }
    },
    /* alertSurveyDialog @see application/core/package/limesurvey
     * @var string[] startPopups array of text to be shown
     * @var string title
     */
    alertSurveyDialog: function alertSurveyDialog(startPopups, title) {
      var text = "";
      if (LSvar.showpopup == 1 && $.isArray(startPopups)) {
        startPopups = startPopups.map(function (text) {
          return "<p>" + text + "</p>";
        });
        text = startPopups.join("");
      }
      if (text) {
        $("#bootstrap-alert-box-modal .modal-header .modal-title").text(title || "");
        $("#bootstrap-alert-box-modal .modal-body").html(text);
        var modal = new bootstrap.Modal(document.getElementById('bootstrap-alert-box-modal'), {});
        modal.show();
      }
    },
    /* confirmSurveyDialog @see application/core/package/limesurvey */
    confirmSurveyDialog: function confirmSurveyDialog(text, title, submits) {
      $("#bootstrap-alert-box-modal .modal-header .modal-title").text(title);
      $("#bootstrap-alert-box-modal .modal-body").html("<p>" + text + "</p>" + "<div class='btn-group btn-group-justified' role='group'><a class='btn btn-warning btn-confirm' data-bs-dismiss='modal'>" + LSvar.lang.yes + "</a><a class='btn btn-cancel' data-bs-dismiss='modal'>" + LSvar.lang.no + "</a></div>");
      var modal = new bootstrap.Modal(document.getElementById('bootstrap-alert-box-modal'), {});
      modal.show();
      $("#bootstrap-alert-box-modal .btn-confirm").on('click', function () {
        $.each(submits, function (name, value) {
          $("<input/>", {
            'type': "hidden",
            'name': name,
            'value': value
          }).appendTo('form#limesurvey');
        });
        $('form#limesurvey').submit();
      });
    },

    /**
     * Add class hover to column in table-col-hover
     * We can't use CSS solution : need no background
     */
    addHoverColumn: function addHoverColumn() {
      $(".table-col-hover").on({
        mouseenter: function mouseenter() {
          $(this).closest(".table-col-hover").find("col").eq($(this).parent(".answers-list").children().index($(this))).addClass("hover");
        },
        mouseleave: function mouseleave() {
          $(this).closest(".table-col-hover").find("col").removeClass("hover");
        }
      }, ".answer-item");
    },

    /**
     * Update some class when em-tips is success/error
     * @see assets/survey_themes/ls6_surveytheme/core/template_core_pre.js:triggerEmClassChange
     */
    triggerEmClassChangeTemplate: function triggerEmClassChangeTemplate() {
      $('.ls-em-tip').each(function () {
        $(this).on('classChangeError', function () {
          /* If user choose hide-tip : leave it */
          var parent = $(this).parent('div.hide-tip');
          if (parent.hasClass('hide-tip')) {
            parent.removeClass('hide-tip', 1);
            parent.addClass('tip-was-hidden', 1);
          }
          var questionContainer = $(this).parents('div.question-container');
          questionContainer.addClass('input-error'); /* No difference betwwen error after submit and error before submit : think (Shnoulle) it's better to have a difference */
        });

        $(this).on('classChangeGood', function () {
          /* If user choose hide-tip : leave it */
          var parent = $(this).parents('div.hide-tip');
          parent.removeClass('text-danger');
          parent.addClass('text-info');
          if (parent.hasClass('tip-was-hidden')) {
            parent.removeClass('tip-was-hidden').addClass('hide-tip');
          }
          var questionContainer = $(this).parents('div.question-container');
          questionContainer.removeClass('input-error'); /* Not working with mandatory question ... */
        });
      });


    },
    /**
     * Hide/show question if all subquestions is hidden
     * @see assets/survey_themes/ls6_surveytheme/core/template_core_pre.js:triggerEmRelevanceSubQuestion
     * @see https://bugs.limesurvey.org/view.php?id=10055 (partial)
     * Must be before ready (event happen before ready)
     */
    hideQuestionWithRelevanceSubQuestion: function hideQuestionWithRelevanceSubQuestion() {
      $(".question-container:not(.ls-irrelevant)").on('relevance:on', "[id^='javatbd']", function (event, data) {
        if (event.target != this) return; // not needed now, but after (2016-11-07)
        data = $.extend({
          style: 'hidden'
        }, data);
        if (data.style == 'hidden') {
          $(this).closest(".question-container").removeClass("ls-hidden");
        }
      });
      $(".question-container:not(.ls-hidden)").on('relevance:off', "[id^='javatbd']", function (event, data) {
        if (event.target != this) return; // not needed now, but after (2016-11-07)
        data = $.extend({
          style: 'hidden'
        }, data);
        if (data.style == 'hidden') {
          var questionContainer = $(this).closest(".question-container");
          $(this).addClass("ls-hidden");
          if ($(questionContainer).find("[id^='javatbd']").filter(':not(.ls-hidden)').length == 0) {
            $(questionContainer).addClass("ls-hidden");
          }
        }
      });
    },
    /**
     * Hide/show parent multiple list
     * @see assets/survey_themes/ls6_surveytheme/core/template_core_pre.js:triggerEmRelevanceSubQuestion
     * @see https://bugs.limesurvey.org/view.php?id=11787
     * Must be before ready (event happen before ready)
     */
    hideMultipleColumn: function hideMultipleColumn() {
      $("[id^='question']").on('relevance:on', ".multiple-list [id^='javatbd']", function (event, data) {
        if (event.target != this) return;
        data = $.extend({
          style: 'hidden'
        }, data);
        if (data.style == 'hidden') {
          $(this).closest(".list-unstyled").removeClass("ls-hidden");
        }
      });
      $("[id^='question']").on('relevance:off', ".multiple-list [id^='javatbd']", function (event, data) {
        if (event.target != this) return;
        data = $.extend({
          style: 'hidden'
        }, data);
        if (data.style == 'hidden') {
          if ($(this).closest(".list-unstyled").find("[id^='javatbd']:visible").length == 0) {
            $(this).closest(".list-unstyled").addClass("ls-hidden");
          }
        }
      });
    }
  };
};

// register to global scope
exports.TemplateCoreClass = TemplateCoreClass;window.TemplateCoreClass = TemplateCoreClass;


if (!window.templateCore) {
  window.templateCore = new TemplateCoreClass();
}

/* document ready function */
$(document).on('ready pjax:scriptcomplete', function () {
  window.templateCore.addHoverColumn();
  window.templateCore.triggerEmClassChangeTemplate();
});

},{"./template_core_pre.js":3}],3:[function(require,module,exports){
"use strict";Object.defineProperty(exports, "__esModule", { value: true });exports.activateActionLink = activateActionLink;exports.activateConfirmButton = activateConfirmButton;exports.activateLanguageChanger = activateLanguageChanger;exports.activateSoftMandatory = activateSoftMandatory;exports.confirmSurveyDialog = confirmSurveyDialog;exports.manageIndex = manageIndex;exports.resetQuestionTimers = resetQuestionTimers;exports.triggerEmClassChange = triggerEmClassChange;exports.triggerEmRelevance = triggerEmRelevance;exports.triggerEmRelevanceGroup = triggerEmRelevanceGroup;exports.triggerEmRelevanceQuestion = triggerEmRelevanceQuestion;exports.triggerEmRelevanceSubQuestion = triggerEmRelevanceSubQuestion;exports.updateLineClass = updateLineClass;exports.updateMandatoryErrorClass = updateMandatoryErrorClass;exports.updateRepeatHeading = updateRepeatHeading; /**
 * @file Javascript core function for public survey
 * @description loaded before template javascript : allow template to use own function (if function is called after template.js)
 * @copyright LimeSurvey <http://www.limesurvey.org/>
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/**
 * Always set an empty LSvar
 */
var LSvar = LSvar || {};

/**
 * Action to do when relevance is set to on or off
 */
function triggerEmRelevance() {
  triggerEmRelevanceQuestion();
  triggerEmRelevanceGroup();
  triggerEmRelevanceSubQuestion();
}
/* On question */
function triggerEmRelevanceQuestion() {
  /* Action on this question */
  $("[id^='question']").on('relevance:on', function (event, data) {
    /* @todo : attach only to this. Use http://stackoverflow.com/a/6411507/2239406 solution for now. 
    Don't want to stop propagation. */
    if (event.target != this) return;
    $(this).removeClass("ls-irrelevant ls-hidden");
  });
  $("[id^='question']").on('relevance:off', function (event, data) {
    if (event.target != this) return;
    $(this).addClass("ls-irrelevant ls-hidden");
  });
  /* In all in one mode : need updating group too */
  $(".allinone [id^='group-']:not(.ls-irrelevant) [id^='question']").on('relevance:on', function (event, data) {
    if (event.target != this) return;
    $(this).closest("[id^='group-']").removeClass("ls-hidden");
  });
  $(".allinone [id^='group-']:not(.ls-irrelevant) [id^='question']").on('relevance:off', function (event, data) {
    if (event.target != this) return;
    if ($(this).closest("[id^='group-']").find("[id^='question']").length == $(this).closest("[id^='group-']").find("[id^='question'].ls-hidden").length) {
      $(this).closest("[id^='group-']").addClass("ls-hidden");
    }
  });
}
/* On Group */
function triggerEmRelevanceGroup() {
  $("[id^='group-']").on('relevance:on', function (event, data) {
    if (event.target != this) return;
    $(this).removeClass("ls-irrelevant ls-hidden");
  });
  $("[id^='group-']").on('relevance:off', function (event, data) {
    if (event.target != this) return;
    $(this).addClass("ls-irrelevant ls-hidden");
  });
}
/* On subquestion and answers-list */
function triggerEmRelevanceSubQuestion() {
  $("[id^='question']").on('relevance:on', "[id^='javatbd']", function (event, data) {
    if (event.target != this) return; // not needed now, but after (2016-11-07)
    data = $.extend({ style: 'hidden' }, data);
    $(this).removeClass("ls-irrelevant ls-" + data.style);
    if (data.style == 'disabled') {
      if ($(event.target).hasClass("answer-item")) {
        $(event.target).find('input').each(function (itrt, item) {
          $(item).prop("disabled", false);
        });
      } else {
        $(event.target).find('.answer-item input').each(function (itrt, item) {
          $(item).prop("disabled", false);
        });
      }
    }
    if (data.style == 'hidden') {
      updateLineClass($(this));
      updateRepeatHeading($(this).closest(".ls-answers"));
    }
  });
  $("[id^='question']").on('relevance:off', "[id^='javatbd']", function (event, data) {
    if (event.target != this) return; // not needed now, but after (2016-11-07)
    data = $.extend({ style: 'hidden' }, data);
    $(this).addClass("ls-irrelevant ls-" + data.style);

    if (data.style == 'disabled') {
      $(event.target).find('input').
      each(function (itrt, item) {
        if ($(item).attr('type') == 'checkbox' && $(item).prop('checked')) {
          $(item).prop('checked', false).trigger('change');
        }
        $(item).prop("disabled", true);
      });
    }

    if (data.style == 'hidden') {
      updateLineClass($(this));
      updateRepeatHeading($(this).closest(".ls-answers"));
    }

    console.ls.log($(this).find('input[disabled]'));
  });
}

/**
 * relevance:(on|off) event
 */
/* Update lines class when relevance:(on|off)  */
function updateLineClass(line) {
  if ($(line).hasClass("ls-odd") || $(line).hasClass("ls-even")) {
    $(line).closest(".ls-answers").find(".ls-odd:visible,.ls-even:visible").each(function (index) {// not limited to table
      $(this).removeClass('ls-odd ls-even').addClass((index + 1) % 2 == 0 ? "ls-odd" : "ls-even");
    });
  }
}
/* Update repeat heading */
function updateRepeatHeading(answers) {
  /* Update only (at start) when all hidden line is done : @todo : do it only once */
  $(function () {
    if ($(answers).data("repeatHeading") || $(answers).find("tbody").find(".ls-heading").length) {
      /* set the data the first time */
      if (!$(answers).data("repeatHeading")) {
        var repeatHeading = $(answers).find("tbody:first tr").length; /* first body don't have heading */
        $(answers).data("repeatHeading", repeatHeading);
        $(answers).data("repeatHeader", $(answers).find("tbody .ls-heading").filter(":first")[0].outerHTML);
      } else {
        var repeatHeading = $(answers).data("repeatHeading");
      }
      /* can remove the heading and clone this one of thead */
      var header = $(answers).data("repeatHeader");
      $(answers).find("tbody .ls-heading").remove();
      var lines = $(answers).find('tr:visible');
      var max = $(answers).find('tr:visible').length - 1;
      $(lines).each(function (index) {
        if (index != 0 && index % repeatHeading == 0 && index < max)
        {
          $(header).insertAfter($(this));
        }
      });
    }
  });
}
/**
 * Manage the index
 */
function manageIndex() {
  /* only needed if it's not inside form (form#limesurvey) */
  $(".ls-index-buttons").on('click', '[name="move"]', function (e) {
    if (!$(this).closest('form').length && $('form#limesurvey').length == 1) {
      $(this).clone().addClass("d-none").appendTo('form#limesurvey').click();
    }
  });
}

/**
 * Reload page when participant selects a new language.
 * Sets input[name=lang] to new language and submits limesurvey form.
 */
function activateLanguageChanger() {
  var limesurveyForm = $('form#limesurvey');
  if (limesurveyForm.length == 0 && $('form[name="limesurvey"]').length == 1) {/* #form-token for example */
    limesurveyForm = $('form[name="limesurvey"]');
  }
  /**
   * @param {string} lang Language to change to.
   */
  var applyChangeAndSubmit = function applyChangeAndSubmit(lang) {
    // Remove existing onsubmitbuttoninput, no need to remove lang : last one is the submitted
    $("#onsubmitbuttoninput").remove();
    // Append new input.
    $('<input type="hidden">').
    attr('name', 'lang').
    val(lang).
    appendTo(limesurveyForm);
    // Append move type.
    /* onsubmitbuttoninput is related to template (and ajax) : MUST move to template with ajax … */
    $('<input type="hidden" id="onsubmitbuttoninput" name="move" value="changelang" />').appendTo(limesurveyForm);
    limesurveyForm.submit();
  };

  $('.form-change-lang a.ls-language-link').on('click', function () {
    var closestForm = $(this).closest('form');
    if (!closestForm.length) {
      /* we are not in a forum, can not submit directly */
      if (limesurveyForm.length == 1) {
        /* The limesurvey form exist in document, move select and button inside and click */
        var newLang = $(this).data('limesurvey-lang');
        applyChangeAndSubmit(newLang);
        // TODO: Check all code below. When does it happen?
      } else {
        // If there are no form : we can't use it */
        if ($(this).data('targeturl')) {
          /* If we have a target url : just move location to this url with lang set */
          /* possible usage : in clear all */
          var target = $(this).data('targeturl');
          /* adding lang in get param manually */
          if (target.indexOf("?") >= 0) {
            target += "&lang=" + $(this).val();
          } else {
            target += "?lang=" + $(this).val();
          }
          /* directly move to location */
          location.href = target;
          return false;
        } else {
          var lang = $(this).data('limesurvey-lang');
          /* No form, not targeturl : just see what happen */
          $("<form>", {
            "class": 'ls-js-hidden',
            "html": '<input type="hidden" name="lang" value="' + lang + '" />',
            "action": target,
            "method": 'get'
          }).appendTo(document.body).submit();
        }

      }
    } else {
      /* we are inside a form : just submit : but remove other lang input if exist : be sure it's this one send */
      $(this).closest('form').find("[name='lang']").not($(this)).remove();
      $(this).closest('.ls-language-changer-item').find(":submit").click();
    }
  });
  /* Language changer dropdown */
  /* Don't activate change when using key up / key down */
  $('.form-change-lang [name="lang"]').on('keypress keydown keyup', function (event) {
    var code = event.keyCode || event.which;
    /* packaje name : limesurvey */
    $(this).data("limesurvey-lastkey", code);
  });
  $('.form-change-lang [name="lang"]').on('click', function (event) {
    /* didn't work with chrome , chrom bug : onclick are an intrinsic event see https://www.w3.org/TR/html401/interact/forms.html#h-17.6 */
    /* Happen rarely (keyboard + mouse + still have the button */
    $(this).data("limesurvey-lastkey", null);
  });
  $('.form-change-lang [name="lang"]').on('change', function (event) {
    if ($(this).data("limesurvey-lastkey") == 38 || $(this).data("lastkey") == 40) {
      /* Last key is up or down : disable auto submit mantis #16024 */
      return;
    }
    var closestForm = $(this).closest('form');
    var newLang = $(this).val();
    if (!closestForm.length) {
      /* we are not in a form, can not submit directly */
      // Remind user can put language changer everywhere, not only in home page, but for example in clear all page etc … in form or not etc ...
      if (limesurveyForm.length == 1) {
        /* The limesurvey form exist in document, move select and button inside and click */
        applyChangeAndSubmit(newLang);
      } else {
        // If there are no form : we can't use it */
        if ($(this).parent().data('targeturl')) {
          /* If we have a target url : just move location to this url with lang set */
          /* targeturl was used for preview gropup and question in 2.6lts : check if still used/usable */
          var target = $(this).parent().data('targeturl');
          /* adding lang in get param manually */
          if (target.indexOf("?") >= 0) {
            target += "&lang=" + $(this).val();
          } else {
            target += "?lang=" + $(this).val();
          }
          /* directly move to location */
          location.href = target;
          return false;
        } else {
          /* No form, not targeturl : just see what happen */
          /* This must not happen : issue in theme */
          $("<form>", {
            "class": 'ls-js-hidden',
            "html": '<input type="hidden" name="lang" value="' + newLang + '" />',
            "action": target,
            "method": 'get'
          }).appendTo(document.body).submit();
        }

      }
    } else {
      /* we are inside a form : just submit : but remove other lang input if exist : be sure it's this one send */
      $(this).closest('form').find("[name='lang']").not(this).remove();
      $(this).closest('.form-change-lang').find(':submit').click();
    }
  });
}

/**
 * Action link with submit object (json) : add params to form and submit
 */
function activateActionLink() {
  /* If no limesurvey form : don't need it */
  if (!$('form#limesurvey').length) {
    $('[data-limesurvey-submit]').remove();
  }
  /* Submit limesurvey form on click */else
  {
    $('[data-limesurvey-submit]').on('click', function (event) {
      event.preventDefault();
      var submit = $(this).data('limesurvey-submit');
      var confirmedby = $(this).data('confirmedby');
      if (!confirmedby) {
        $.each(submit, function (name, value) {
          $("<input/>", {
            'type': "hidden",
            'name': name,
            'value': value
          }).appendTo('form#limesurvey');
        });
        $('form#limesurvey').submit();
      } else {
        var submits = $.extend(submit, confirmedby);
        confirmSurveyDialog($(this).data('confirmlabel'), $(this).text(), submits);
      }
    });
  }
}
/**
 * function for replacing submit after confirm
 * @var string text : the text to be shown
 * @var string optional title
 * @var object[] submits : name.value to submit
 */
function confirmSurveyDialog(text, title, submits) {
  if ($.bsconfirm !== undefined) {
    $.bsconfirm(text, LSvar.lang.confirm, function () {
      $.each(submits, function (name, value) {
        $("<input/>", {
          'type': "hidden",
          'name': name,
          'value': value
        }).appendTo('form#limesurvey');
      });
      $('form#limesurvey').submit();
    });
  } else {
    if (confirm(text)) {
      $.each(submits, function (name, value) {
        $("<input/>", {
          'type': "hidden",
          'name': name,
          'value': value
        }).appendTo('form#limesurvey');
      });
      $('form#limesurvey').submit();
    }
  }
}
/**
 *  Ask confirmation on click on .needconfirm
 */
function activateConfirmButton() {
  /* With ajax mode : using $(document).on attache X times the same event */
  $("button[data-confirmedby]").on('click', function (event) {
    var btnConfirm = $(this);
    var cbConfirm = $(this).parent().find("[name='" + $(this).data('confirmedby') + "']");
    if (!$(cbConfirm).is(":checked")) {
      event.preventDefault();
      var submits = {};
      submits[$(btnConfirm).attr('name')] = $(btnConfirm).val();
      submits[$(cbConfirm).attr('name')] = $(cbConfirm).val();
      confirmSurveyDialog($(cbConfirm).parent("label").text(), $(btnConfirm).text(), submits);
    }
  });
}
/**
 * Trigger tip class when classChangeGood/classChangeError happen
 */
function triggerEmClassChange() {
  /* The tips */
  $(document).on('classChangeError', '.ls-em-tip', function (event) {
    $(this).removeClass("ls-em-success").addClass("ls-em-error text-danger");
  });
  $(document).on('classChangeGood', '.ls-em-tip', function (event) {
    $(this).removeClass("ls-em-error text-danger").addClass("ls-em-success");
  });
  /* The dynamic sum */
  $(document).on('classChangeError', '.dynamic-total', function (event) {
    $(this).removeClass("ls-em-success text-success").addClass("ls-em-error text-danger");
  });
  $(document).on('classChangeGood', '.dynamic-total', function (event) {
    $(this).removeClass("ls-em-error text-danger").addClass("ls-em-success text-success");
  });
  /* The input */
  $(document).on('classChangeError', 'input,select,textarea', function (event) {
    $(this).closest(".form-control").addClass("has-warning"); // Use warning, not error : in multiple : if one input have error : it's apply to all input
  });
  $(document).on('classChangeGood', 'input,select,textarea', function (event) {
    $(this).closest(".form-control").removeClass("has-warning");
  });
}

/**
 * has-error management for ls-error-mandatory
 * Only add ls-error-mandatory in PHP currently, not in js : different behaviour after try next and don't try next
 * /!\ We can more easily doing without js ( usage of :empty in css with :text & select) but then no boostrap, for before submit : use only css in template
 */
function updateMandatoryErrorClass() {
  $(".ls-error-mandatory .has-error,.ls-error-mandatory.has-error").on("blur", ":text,textarea", function (event) {
    if ($(this).val() !== "") {
      $(this).closest(".has-error").removeClass("has-error");
      if (!$(this).closest(".ls-error-mandatory").find(".has-error").length) {
        $(this).closest(".ls-error-mandatory").find(".text-danger").removeClass("text-danger");
      }
    }
  });
  $(".ls-error-mandatory .has-error,.ls-error-mandatory.has-error").on("change", "select", function (event) {
    if ($(this).val() !== "") {
      $(this).closest(".has-error").removeClass("has-error");
      if (!$(this).closest(".ls-error-mandatory").find(".has-error").length) {
        $(this).closest(".ls-error-mandatory").find(".text-danger").removeClass("text-danger");
      }
    }
  });
  $(".ls-error-mandatory.has-error").on("change", ":radio", function (event) {
    if ($(this).closest(".array-flexible-dual-scale").length) {
      if ($(this).closest(".has-error").find("input:radio:checked").length > 1) {
        $(this).closest(".has-error").removeClass("has-error");
      }
    } else {
      $(this).closest(".has-error").removeClass("has-error");
    }
  });
  $(".ls-error-mandatory.has-error").on("change", ":checkbox", function (event) {
    $(this).closest(".has-error").removeClass("has-error");
  });
}

/**
 * Soft mandatory
 */
function activateSoftMandatory() {
  $('#mandatory-soft-alert-box-modal').on('click', function (ev) {
    ev.preventDefault();
    var $form = $('#limesurvey');
    $form.append('<input type="hidden" name="mandSoft" value="' + $(this).data('movenext') + '" />');
    $form.find('#ls-button-submit').trigger('click');
    $("#bootstrap-alert-box-modal").modal('hide');
  });
}

function resetQuestionTimers(sid) {
  var surveyTimersItemName = 'limesurvey_timers_by_sid_' + sid;
  var timers = JSON.parse(window.localStorage.getItem(surveyTimersItemName) || "[]");
  timers.forEach(function (timersessionname, idx) {
    window.localStorage.removeItem('limesurvey_timers_' + timersessionname);
  });
  window.localStorage.removeItem(surveyTimersItemName);
}

/**
 * Disable submit button to prevent multiple submits
 * This is done on 'document' instead of the '#limesurvey' form in order to allow
 * other scripts (custom themes?) to cancel the submit before we disable the button.
 */
$(document).on('submit', function (e) {
  // If the target is not the '#limesurvey' form, don't do anything.
  if (e.target.id != 'limesurvey') {
    return;
  }
  // We only care about the final submit, not normal forward/backward navigation.
  var submitter = e.originalEvent ? $(e.originalEvent.submitter) : null;
  if (!submitter || submitter.attr('value') != 'movesubmit') {
    return;
  }
  // Still, we disable all submit buttons to make sure the "back" button is not
  // pressed while submitting.
  $('#limesurvey button[type="submit"]').prop('disabled', true);

  // We also add a hidden input with the button's value, because it's not included
  // in the request when the button is disabled.
  $('#limesurvey').append('<input id="onsubmitbuttoninput" name=\'' + submitter.attr('name') + '\' value=\'' + submitter.attr('value') + '\' type=\'hidden\' />');
});

// register to global scope
window.triggerEmRelevance = triggerEmRelevance;
window.triggerEmRelevanceQuestion = triggerEmRelevanceQuestion;
window.triggerEmRelevanceGroup = triggerEmRelevanceGroup;
window.triggerEmRelevanceSubQuestion = triggerEmRelevanceSubQuestion;
window.updateLineClass = updateLineClass;
window.updateRepeatHeading = updateRepeatHeading;
window.manageIndex = manageIndex;
window.activateLanguageChanger = activateLanguageChanger;
window.activateActionLink = activateActionLink;
window.confirmSurveyDialog = confirmSurveyDialog;
window.activateConfirmButton = activateConfirmButton;
window.triggerEmClassChange = triggerEmClassChange;
window.updateMandatoryErrorClass = updateMandatoryErrorClass;
window.activateSoftMandatory = activateSoftMandatory;
window.resetQuestionTimers = resetQuestionTimers;

},{}],4:[function(require,module,exports){
"use strict";



var _ls6_core_theme = _interopRequireDefault(require("./core/ls6_core_theme.js"));
var _array = _interopRequireDefault(require("./questiontypes/array/array.js"));function _interopRequireDefault(obj) {return obj && obj.__esModule ? obj : { "default": obj };}

},{"./core/ls6_core_theme.js":1,"./questiontypes/array/array.js":5}],5:[function(require,module,exports){
"use strict";Object.defineProperty(exports, "__esModule", { value: true });exports.ArrayScripts = void 0;var ArrayScripts = function ArrayScripts() {
  var addEntryMarker = function addEntryMarker(element) {
    element.addClass('success-border');
  };

  var removeEntryMarker = function removeEntryMarker(element) {
    element.removeClass('success-border');
  };

  var initArrayEvents = function initArrayEvents() {
    $(document).on(
    "change",
    '.array-multi-flexi .multiflexitext.text-item, .array-multi-flexi .answer-item .form-select, .array-flexible-dual-scale .answer-item .form-select, .array-multi-flexi-text input.form-control',
    function () {
      var enteredValue = $(this).val();
      if (enteredValue !== undefined && enteredValue !== '') {
        addEntryMarker($(this));
      } else {
        removeEntryMarker($(this));
      }
    });
  };

  return {
    initArrayEvents: initArrayEvents
  };
};
// register to global scope
exports.ArrayScripts = ArrayScripts;window.ArrayScripts = ArrayScripts;

},{}]},{},[4]);
