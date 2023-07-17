import TemplateCoreClass from './old_template_core_class.js';

/**
 * The general Template closure.
 * This is to capsule eventual errors inside of the template function, so the general script all run as the should
 */
// Global constructor, used in multiple twig files
// eslint-disable-next-line no-unused-vars
export var ThemeScripts = function () {
    // defining the console object once. Also filtered by the global setting
    // eslint-disable-next-line no-console
    var logObject = console.ls ? (window.debugState.frontend ? console.ls : console.ls.silent) : console;
    /**
     * The function focusFirst puts the Focus on the first non-hidden element in the Survey.
     * Normally this is the first input field (the first answer).
     */
    var focusFirst = function focusFirst() {
        $('#limesurvey :input:visible:enabled:first').focus();
    };

    /**
     * fix padding of body according to navbar-fixed-top
     * in endpage and in $(window).resize
     */
    var fixBodyPadding = function fixBodyPadding() {
        $('body').css('padding-top', Math.round($('#survey-nav').outerHeight()) + 'px');
    };

    /**
     * Hide some part if empty
     * Some can be needed if contain only js
     * Some are not really needed : little margin only is shown
     * Allowed content -> <script> and <img/>
     */
    var hideEmptyPart = function hideEmptyPart() {
        $('.question-help-container').each(function () {
            /* Only if have only script tag inside or empty tag */
            if ($(this).text().trim() == '' && !/<img/.test($(this).html())) {
                $(this).addClass('d-none');
            }
        });
        $('.group-description').each(function () {
            /* Only if have only script tag inside or empty tag */
            if ($(this).text().trim() == '' && !/<img/.test($(this).html())) {
                $(this).addClass('d-none');
            }
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

    var initTopMenuLanguageChanger = function (selectorItem, selectorGlobalForm) {
        // $(selectorContainer).height($('#main-row').height());
        $(document).on('click', selectorItem, function () {
            var lang = $(this).data('limesurvey-lang');
            /* The limesurvey form exist in document, move select and button inside and click */
            $(selectorGlobalForm + ' [name=\'lang\']').remove();                        // Remove existing lang selector
            $('<input type=\'hidden\'>').attr('name', 'lang').val(lang).appendTo($(selectorGlobalForm));
        });
    };

    var init = function () {

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

                    var $firstError = ($('.input-error').length > 0) ? $('.input-error').first() : $('.ls-em-error').first();

                    var $pixToScroll = ($firstError.offset().top - 100);
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
                var headerHeight = 2 * $('#survey-nav').outerHeight();
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
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });

            // Bind language changer onclick event.
            // This function is defined in assets/survey_themes/fruity_twentythree/core/old_template_core_pre.js
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
        var onkeyEventInput = function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
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

        var onkeyEventTextarea = function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
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

    var initUserForms = function () {
        fixBodyPadding();
        hideEmptyPart();
    };
    var initGlobal = function () {
        fixBodyPadding();
        if (jQuery.isFunction(window.templateCore.hideQuestionWithRelevanceSubQuestion)) {
            window.templateCore.hideQuestionWithRelevanceSubQuestion();
        }
        if (jQuery.isFunction(window.templateCore.hideMultipleColumn)) {
            window.templateCore.hideMultipleColumn();
        }
        hideEmptyPart();
    };

    var initWelcomePage = function () {
        /* Nothing to do : keep it for compatibility (pre 3.0.1) */
    };

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
window.ThemeScripts = ThemeScripts;
