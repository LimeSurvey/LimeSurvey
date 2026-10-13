/**
 * Quick translation: Navigation, collapsing of the sections and filter of untranslated texts
 */
(function () {
    'use strict';

    /**
     * Returns the Bootstrap collapse instance of an element, without toggling it
     * @param {Element} element
     * @return {bootstrap.Collapse}
     */
    function getCollapse(element) {
        return bootstrap.Collapse.getOrCreateInstance(element, {toggle: false});
    }

    /**
     * Sets the space for the sticky top bar, which is higher in the classic layout (survey title above the
     * buttons) than in the editor layout; It is used for the sticky navigation and for scrolling to a part
     * @param {Element} root
     */
    function updateStickyTop(root) {
        var topbar = document.querySelector('.topbar.sticky-top');
        if (topbar) {
            var stickyTop = parseFloat(getComputedStyle(topbar).top) || 0;
            root.style.setProperty('--qt-sticky-top', (stickyTop + topbar.offsetHeight + 16) + 'px');
        }
    }

    /**
     * Shows a part of the page: Expands its section, scrolls to it and moves the focus to it
     * @param {Element} target
     */
    function showTarget(target) {
        updateStickyTop(document.getElementById('quick-translation'));
        var body = target.closest('.quick-translation-section').querySelector(':scope > .collapse');
        if (!body.classList.contains('show')) {
            body.classList.add('show');
            var toggle = document.querySelector('[data-bs-target="#' + body.id + '"]');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
                toggle.classList.remove('collapsed');
            }
        }
        target.scrollIntoView({block: 'start'});
        target.focus({preventScroll: true});
    }

    /**
     * Checks if texts of a part of the page were changed and not saved yet
     * @param {Element} part
     * @return {boolean}
     */
    function hasUnsavedChanges(part) {
        return Array.prototype.some.call(part.querySelectorAll('textarea[name*="_newvalue_"]'), function (textarea) {
            var oldValue = part.querySelector('input[name="' + textarea.name.replace('_newvalue_', '_oldvalue_') + '"]');
            return oldValue && oldValue.value !== textarea.value;
        });
    }

    /**
     * Saves the texts of a question, then opens its preview, which shows the saved texts
     * @param {Element} link the preview link
     * @param {Event} event
     */
    function saveAndPreview(link, event) {
        var question = link.closest('.quick-translation-question');
        var root = document.getElementById('quick-translation');
        if (!root.dataset.saveUrl) {
            return;
        }
        // Editors write their content into the textarea only when asked
        if (window.CKEDITOR) {
            question.querySelectorAll('textarea[name*="_newvalue_"]').forEach(function (textarea) {
                if (CKEDITOR.instances[textarea.name]) {
                    CKEDITOR.instances[textarea.name].updateElement();
                }
            });
        }
        if (!hasUnsavedChanges(question)) {
            return;
        }
        event.preventDefault();
        // Opened right away, a window opened after the saving would be blocked as a popup
        var preview = window.open('', '_blank');
        var form = $('#translateform');
        var data = $(question).find(':input')
            .add(form.find('input[name$="_size"], input[name="tolang"], input[name="baselang"], input[name="YII_CSRF_TOKEN"]'))
            .serialize();
        $.post(root.dataset.saveUrl, data).done(function () {
            // Saved: The current texts are the ones to compare with now
            question.querySelectorAll('textarea[name*="_newvalue_"]').forEach(function (textarea) {
                var oldValue = question.querySelector('input[name="' + textarea.name.replace('_newvalue_', '_oldvalue_') + '"]');
                if (oldValue) {
                    oldValue.value = textarea.value;
                }
            });
            if (preview) {
                preview.location.href = link.href;
            } else {
                window.open(link.href, '_blank');
            }
        }).fail(function () {
            if (preview) {
                preview.close();
            }
            LS.LsGlobalNotifier.createAlert(root.dataset.saveError, 'danger', {showCloseButton: true});
        });
    }

    /**
     * Copies the source text into the translation field of the button (and its inline editor)
     * @param {Element} button
     */
    function copySourceText(button) {
        var name = button.dataset.targetFieldId;
        var textarea = document.querySelector('textarea[name="' + name + '"]');
        if (!textarea) {
            return;
        }
        textarea.value = button.dataset.source;
        if (window.CKEDITOR && CKEDITOR.instances[name]) {
            CKEDITOR.instances[name].setData(button.dataset.source);
        }
        $(textarea).trigger('change');
    }

    $(document).on('ready pjax:scriptcomplete', function () {
        // The navigation, filter and expand buttons are for the survey tab, not for the email templates
        var root = document.getElementById('quick-translation-pane-survey');
        if (!root) {
            return;
        }

        $(document).off('.quicktranslation');
        $(window).off('.quicktranslation');

        updateStickyTop(document.getElementById('quick-translation'));
        $(window).on('resize.quicktranslation', function () {
            updateStickyTop(document.getElementById('quick-translation'));
        });

        $(document).on('change.quicktranslation', '#quick-translation-only-untranslated', function () {
            root.classList.toggle('quick-translation-only-untranslated', this.checked);
        });

        $(document).on('click.quicktranslation', '#quick-translation-expand-all, #quick-translation-collapse-all', function () {
            var expand = this.id === 'quick-translation-expand-all';
            root.querySelectorAll('.quick-translation-section > .collapse').forEach(function (body) {
                if (expand) {
                    getCollapse(body).show();
                } else {
                    getCollapse(body).hide();
                }
            });
        });

        $(document).on('click.quicktranslation', '.quick-translation-copy', function () {
            copySourceText(this);
        });

        $(document).on('click.quicktranslation', '.quick-translation-preview', function (event) {
            saveAndPreview(this, event);
        });

        $(document).on('click.quicktranslation', '.quick-translation-nav a[href^="#"]', function (event) {
            var target = document.getElementById(this.getAttribute('href').substring(1));
            if (target) {
                event.preventDefault();
                showTarget(target);
            }
        });
    });
})();
