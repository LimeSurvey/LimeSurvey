/**
 * QuestionExplorer - Question groups explorer component
 * Matches original _questionsgroups.vue implementation
 */
import StateManager from '../StateManager.js';
import Actions from '../Actions.js';
import UIHelpers from '../UIHelpers.js';

class QuestionExplorer {
    constructor() {
        this.container = null;
        this.onOrderChange = null;

        // Drag and drop state - matching Vue component data()
        this.active = [];
        this.questiongroupDragging = false;
        this.draggedQuestionGroup = null;
        this.questionDragging = false;
        this.draggedQuestion = null;
        this.draggedQuestionsGroup = null;
        this.orderChanged = false; // Track if order actually changed during drag
        this.lastDragenterGid = null; // Prevent duplicate dragenter processing
        this.lastDragenterQid = null; // Prevent duplicate dragenter processing
        this.dragStartGroupOrder = null; // Group gids in display order at dragstart
        this.pendingFocusSelector = null; // Element to refocus after a keyboard move re-renders the list
    }

    /**
     * Render the question explorer
     */
    render(containerEl, loading, orderChangeCallback) {
        this.container = containerEl;
        this.onOrderChange = orderChangeCallback;

        if (!this.container) return;

        this.active = StateManager.get('questionGroupOpenArray') || [];

        this.ensureLiveRegion();
        this.renderExplorer();
    }

    /**
     * Check if group is open
     */
    isOpen(gid) {
        if (this.questiongroupDragging === true) return false;
        return LS.ld.indexOf(this.active, gid) !== -1;
    }

    /**
     * Check if group is active
     */
    isActive(gid) {
        return gid == StateManager.get('lastQuestionGroupOpen');
    }

    /**
     * Get question group item classes - matching Vue questionGroupItemClasses()
     */
    questionGroupItemClasses(questiongroup) {
        var classes = '';
        classes += this.isOpen(questiongroup.gid) ? ' selected ' : ' ';
        classes += this.isActive(questiongroup.gid) ? ' activated ' : ' ';
        if (this.draggedQuestionGroup !== null) {
            classes += this.draggedQuestionGroup.gid === questiongroup.gid ? ' dragged' : ' ';
        }
        return classes;
    }

    /**
     * Get question item classes - matching Vue questionItemClasses()
     */
    questionItemClasses(question) {
        var classes = '';
        classes += StateManager.get('lastQuestionOpen') === question.qid ? 'selected activated' : 'selected ';
        if (this.draggedQuestion !== null) {
            classes += this.draggedQuestion.qid === question.qid ? ' dragged' : ' ';
        }
        return classes;
    }

    /**
     * Render the explorer content - matching Vue template exactly
     */
    renderExplorer() {
        if (!this.container) return;

        var questiongroups = StateManager.get('questiongroups') || [];
        var allowOrganizer = StateManager.get('allowOrganizer') === null ? 1 :  StateManager.get('allowOrganizer') === 1;
        var surveyIsActive = window.SideMenuData.isActive;
        var createQuestionGroupLink = window.SideMenuData.createQuestionGroupLink;
        var createQuestionLink = window.SideMenuData.createQuestionLink;

        var createQuestionAllowed = questiongroups.length > 0 && createQuestionLink && createQuestionLink.length > 1;
        var createQuestionAllowedClass = createQuestionAllowed ? '' : 'disabled';
        var createQuestionGroupAllowedClass = (createQuestionGroupLink && createQuestionGroupLink.length > 1) ? '' : 'disabled';

        var orderedQuestionGroups = LS.ld.orderBy(
            questiongroups,
            function(a) { return UIHelpers.parseIntOr(a.group_order, 999999); },
            ['asc']
        );

        var html = '<div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane h-100 pt-2">';

        // Toolbar buttons
        html += '<div class="ls-flex-row button-sub-bar mb-2">';
        html += '<div class="scoped-toolbuttons-right me-2">';
        html += '<button class="btn btn-sm btn-outline-secondary toggle-organizer-btn" title="' + UIHelpers.translate(allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle') + '">';
        html += '<i class="' + (allowOrganizer ? 'ri-lock-unlock-fill' : 'ri-lock-fill') + '"></i>';
        html += '</button>';
        html += '<button class="btn btn-sm btn-outline-secondary me-2 expand-all-btn" title="' + UIHelpers.translate('expandAll') + '">';
        html += '<i class="ri-expand-up-down-line"></i>';
        html += '</button>';
        html += '<button class="btn btn-sm btn-outline-secondary me-2 collapse-all-btn" title="' + UIHelpers.translate('collapseAll') + '">';
        html += '<i class="ri-contract-up-down-line"></i>';
        html += '</button>';
        html += '</div>';
        html += '</div>';

        // Create buttons
        html += '<div class="ls-flex-row wrap align-content-center align-items-center button-sub-bar">';
        html += '<div class="scoped-toolbuttons-left mb-2 d-flex align-items-center">';

        var createQuestionTooltip = UIHelpers.translate(createQuestionAllowed ? '' : 'deactivateSurvey');
        html += '<div class="create-question px-3" data-bs-toggle="tooltip" data-bs-placement="top" title="' + createQuestionTooltip + '">';
        html += '<a id="adminsidepanel__sidebar--selectorCreateQuestion" href="' + this.createFullQuestionLink(createQuestionLink) + '" class="btn btn-primary pjax ' + createQuestionAllowedClass + '">';
        html += '<i class="ri-add-circle-fill"></i>&nbsp;' + UIHelpers.translate('createQuestion');
        html += '</a>';
        html += '</div>';

        html += '<div data-bs-toggle="tooltip" data-bs-placement="top" title="' + createQuestionTooltip + '">';
        html += '<a id="adminsidepanel__sidebar--selectorCreateQuestionGroup" href="' + createQuestionGroupLink + '" class="btn btn-secondary pjax ' + createQuestionGroupAllowedClass + '">';
        html += UIHelpers.translate('createPage');
        html += '</a>';
        html += '</div>';

        html += '</div>';
        html += '</div>';

        // Question groups list
        html += '<div class="ls-flex-row ls-space padding all-0">';
        html += '<ul class="list-group col-12 questiongroup-list-group">';

        orderedQuestionGroups.forEach((questiongroup, groupIndex) => {
            html += this.renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, groupIndex, orderedQuestionGroups.length);
        });

        html += '</ul>';
        html += '</div>';
        html += '</div>';

        this.container.innerHTML = html;
        this.bindEvents();
        UIHelpers.redoTooltips();
        this.initQuestionTooltips();
        this.restoreFocus();
    }

    /**
     * Re-initialize the question-item tooltips with a fixed positioning strategy.
     *
     * The links deliberately omit data-bs-toggle="tooltip" so the global
     * LS.doToolTip() (re-run on every pjax navigation) doesn't re-create them with
     * its default 'absolute' strategy, which mispositions them inside the sidebar.
     * We own these tooltips here and always use the 'fixed' strategy.
     */
    initQuestionTooltips() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip || !this.container) {
            return;
        }
        this.container.querySelectorAll('.question-link[title]').forEach(function (el) {
            var existing = bootstrap.Tooltip.getInstance(el);
            if (existing) {
                try { existing.dispose(); } catch (e) {}
            }
            new bootstrap.Tooltip(el, {
                placement: 'top',
                container: 'body',
                popperConfig: { strategy: 'fixed' }
            });
        });
    }

    createFullQuestionLink(baseLink) {
        if (!baseLink) return '#';
        if (LS.reparsedParameters && LS.reparsedParameters().combined && LS.reparsedParameters().combined.gid) {
            return baseLink + '&gid=' + LS.reparsedParameters().combined.gid;
        }
        return baseLink;
    }

    /**
     * Render question group - matching Vue template
     *
     * @param {Object} questiongroup the question group to render
     * @param {boolean} allowOrganizer whether reordering is unlocked
     * @param {boolean} surveyIsActive whether the survey is active
     * @param {number} groupIndex position of the group in display order (0-based)
     * @param {number} groupCount total number of groups
     * @returns {string} the group list item HTML
     */
    renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, groupIndex, groupCount) {
        var classes = 'list-group-item ls-flex-column' + this.questionGroupItemClasses(questiongroup);
        var isGroupOpen = this.isOpen(questiongroup.gid);
        var groupActivated = this.isActive(questiongroup.gid);
        var groupNameText = this.groupName(questiongroup);

        var html = '<li class="' + classes + '" data-gid="' + questiongroup.gid + '">';

        // Question group header
        html += '<div class="q-group d-flex nowrap ls-space padding right-5 bottom-5 bg-white ms-2 p-2" data-gid="' + questiongroup.gid + '">';

        // Drag handle
        html += '<div class="bigIcons dragPointer me-1 questiongroup-drag-handle ' + (allowOrganizer ? '' : 'disabled') + '" ';
        html += (allowOrganizer ? 'draggable="true"' : '') + ' data-gid="' + questiongroup.gid + '">';
        html += '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
        html += '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>';
        html += '</svg>';
        html += '</div>';

        // Expand/collapse toggle
        var rotateStyle = isGroupOpen ? 'transform: rotate(90deg)' : 'transform: rotate(0deg)';
        html += '<button type="button" class="btn btn-link p-0 border-0 align-self-start text-body cursor-pointer me-1 toggle-questiongroup" data-gid="' + questiongroup.gid + '"';
        html += ' aria-expanded="' + (isGroupOpen ? 'true' : 'false') + '" aria-label="' + UIHelpers.escapeHtml(UIHelpers.translate(isGroupOpen ? 'collapseGroup' : 'expandGroup') + ': ' + groupNameText) + '">';
        html += '<i class="ri-arrow-right-s-fill d-inline-block" aria-hidden="true" style="' + rotateStyle + '"></i>';
        html += '</button>';

        // Question group name
        html += '<div class="w-100 position-relative">';
        html += '<div class="cursor-pointer">';
        html += '<a class="d-flex pjax questiongroup-link" href="' + questiongroup.link + '" data-gid="' + questiongroup.gid + '">';
        html += '<span class="question_text_ellipsize">' + UIHelpers.escapeHtml(groupNameText) + '</span>';
        html += '</a>';
        html += '</div>';

        // Dropdown and badge
        html += '<div class="position-absolute top-0 d-flex align-items-center" style="right:5px">';
        html += '<div class="toggle-questiongroup" data-gid="' + questiongroup.gid + '">';
        html += '<span class="badge reverse-color ls-space margin right-5">' + (questiongroup.questions ? questiongroup.questions.length : 0) + '</span>';
        html += '</div>';

        // Dropdown menu - always render, 3-dot icon always visible
        if (questiongroup.groupDropdown || allowOrganizer) {
            var groupDropdown = questiongroup.groupDropdown || {};
            html += '<div class="dropdown questiongroup-dropdown' + (groupActivated ? ' active' : '') + '">';
            html += '<button type="button" id="qg-dropdown-' + questiongroup.gid + '" class="ls-questiongroup-tools questiongroup-dropdown-toggle cursor-pointer btn btn-link p-0 align-middle text-body" data-gid="' + questiongroup.gid + '" data-bs-toggle="dropdown" aria-expanded="false"';
            html += ' aria-label="' + UIHelpers.escapeHtml(UIHelpers.translate('pageActionsMenu') + ': ' + groupNameText) + '">';
            html += '<i class="ri-more-fill" aria-hidden="true"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu" aria-labelledby="qg-dropdown-' + questiongroup.gid + '">';

            if (allowOrganizer) {
                html += this.renderMoveItems('data-gid="' + questiongroup.gid + '"', groupIndex > 0, groupIndex < groupCount - 1);
            }

            for (var key in groupDropdown) {
                if (!groupDropdown.hasOwnProperty(key)) continue;
                var value = groupDropdown[key];

                if (key !== 'delete') {
                    html += '<li>';
                    html += '<a class="dropdown-item" id="' + (value.id || '') + '" href="' + value.url + '">';
                    html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
                    html += '</a>';
                    html += '</li>';
                } else {
                    html += '<li class="' + (value.disabled ? 'disabled' : '') + '">';
                    if (!value.disabled) {
                        html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-btnclass="btn-danger" data-title="' + UIHelpers.escapeHtml(value.dataTitle || '') + '" data-btntext="' + UIHelpers.escapeHtml(value.dataBtnText || '') + '" data-onclick="' + UIHelpers.escapeHtml(value.dataOnclick || '') + '" data-message="' + UIHelpers.escapeHtml(value.dataMessage || '') + '">';
                    } else {
                        html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + UIHelpers.escapeHtml(value.title || '') + '">';
                    }
                    html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
                    html += '</a>';
                    html += '</li>';
                }
            }
            html += '</ul>';
            html += '</div>';
        }

        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Questions list (if open) - matching Vue transition
        if (isGroupOpen && questiongroup.questions) {
            html += this.renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, groupIndex, groupCount);
        }

        html += '</li>';
        return html;
    }

    /**
     * Render questions list
     *
     * @param {Object} questiongroup the question group whose questions are rendered
     * @param {boolean} allowOrganizer whether reordering is unlocked
     * @param {boolean} surveyIsActive whether the survey is active
     * @param {number} groupIndex position of the group in display order (0-based)
     * @param {number} groupCount total number of groups
     * @returns {string} the question list HTML
     */
    renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, groupIndex, groupCount) {
        var orderedQuestions = LS.ld.orderBy(
            questiongroup.questions,
            function(a) { return UIHelpers.parseIntOr(a.question_order, 999999); },
            ['asc']
        );

        var html = '<ul class="list-group background-muted padding-left question-question-list" style="padding-right:15px">';

        orderedQuestions.forEach((question, questionIndex) => {
            // A question at either end of its group can still move into the neighbouring group
            var canMoveUp = questionIndex > 0 || groupIndex > 0;
            var canMoveDown = questionIndex < orderedQuestions.length - 1 || groupIndex < groupCount - 1;
            html += this.renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, canMoveUp, canMoveDown);
        });

        html += '</ul>';
        return html;
    }

    /**
     * Render single question - matching Vue template exactly
     *
     * @param {Object} question the question to render
     * @param {Object} questiongroup the group the question belongs to
     * @param {boolean} allowOrganizer whether reordering is unlocked
     * @param {boolean} surveyIsActive whether the survey is active
     * @param {boolean} canMoveUp whether the question can be moved up
     * @param {boolean} canMoveDown whether the question can be moved down
     * @returns {string} the question list item HTML
     */
    renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, canMoveUp, canMoveDown) {
        var classes = 'list-group-item question-question-list-item ls-flex-row align-items-flex-start ' + this.questionItemClasses(question);
        var itemActivated = Number(StateManager.get('lastQuestionOpen')) === Number(question.qid);
        // Always show dropdown HTML, use CSS/JS hover to control visibility
        var showDropdown = true;
        var questionHasCondition = question.relevance !== '1';

        var html = '<li class="' + classes + '" data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '" data-is-hidden="' + question.hidden + '" data-questiontype="' + question.type + '" data-has-condition="' + questionHasCondition + '">';

        // Drag handle (only if survey not active)
        if (!surveyIsActive) {
            html += '<div class="margin-right bigIcons dragPointer question-question-list-item-drag question-drag-handle ' + (allowOrganizer ? '' : 'disabled') + '" ';
            html += (allowOrganizer ? 'draggable="true"' : '') + ' data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '">';
            html += '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
            html += '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>';
            html += '</svg>';
            html += '</div>';
        }

        // Question link
        html += '<a href="' + question.link + '" class="pjax question-question-list-item-link display-as-container question-link" data-qid="' + question.qid + '" data-gid="' + question.gid + '" title="' + UIHelpers.escapeHtml(question.question_flat) + '">';
        html += '<span class="question_text_ellipsize ' + (question.hidden ? 'question-hidden' : '') + '">';
        html += '[' + UIHelpers.escapeHtml(question.title) + '] &rsaquo; ' + UIHelpers.escapeHtml(question.question_flat);
        html += '</span>';
        html += '</a>';

        // Question dropdown - always render, 3-dot icon always visible
        // Questions can only be reordered while the survey is inactive, same as dragging
        var canReorder = allowOrganizer && !surveyIsActive;
        if (question.questionDropdown || canReorder) {
            var questionDropdown = question.questionDropdown || {};
            var dropdownStyle = 'right:10px';
            html += '<div class="dropdown question-dropdown position-absolute' + (itemActivated ? ' active' : '') + '" style="' + dropdownStyle + '">';
            html += '<button type="button" id="q-dropdown-' + question.qid + '" class="ls-question-tools question-dropdown-toggle ms-auto position-relative cursor-pointer btn btn-link p-0 align-middle text-body" data-qid="' + question.qid + '" data-bs-toggle="dropdown" aria-expanded="false"';
            html += ' aria-label="' + UIHelpers.escapeHtml(UIHelpers.translate('questionActionsMenu') + ': [' + question.title + '] ' + question.question_flat) + '">';
            html += '<i class="ri-more-fill" aria-hidden="true"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu" aria-labelledby="q-dropdown-' + question.qid + '">';

            if (canReorder) {
                html += this.renderMoveItems('data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '"', canMoveUp, canMoveDown);
            }

            for (var key in questionDropdown) {
                if (!questionDropdown.hasOwnProperty(key)) continue;
                var value = questionDropdown[key];

                if (key !== 'delete' && !(key === 'language' && Array.isArray(value))) {
                    var isDisabled = key === 'editDefault' && value.active === 0;
                    html += '<li>';
                    html += '<a class="dropdown-item ' + (isDisabled ? 'disabled' : '') + '" id="' + (value.id || '') + '" href="' + (isDisabled ? '#' : value.url) + '">';
                    html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
                    html += '</a>';
                    html += '</li>';
                } else if (key === 'delete') {
                    html += '<li class="' + (value.disabled ? 'disabled' : '') + '">';
                    if (!value.disabled) {
                        html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-btnclass="btn-danger" data-title="' + UIHelpers.escapeHtml(value.dataTitle || '') + '" data-btntext="' + UIHelpers.escapeHtml(value.dataBtnText || '') + '" data-onclick="' + UIHelpers.escapeHtml(value.dataOnclick || '') + '" data-message="' + UIHelpers.escapeHtml(value.dataMessage || '') + '">';
                    } else {
                        html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + UIHelpers.escapeHtml(value.title || '') + '">';
                    }
                    html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
                    html += '</a>';
                    html += '</li>';
                } else if (key === 'language' && Array.isArray(value)) {
                    html += '<li role="separator" class="dropdown-divider"></li>';
                    html += '<li class="dropdown-header">Survey logic file</li>';
                    value.forEach(function(language) {
                        html += '<li>';
                        html += '<a class="dropdown-item" id="' + (language.id || '') + '" href="' + language.url + '">';
                        html += '<span class="' + (language.icon || '') + '"></span> ' + language.label;
                        html += '</a>';
                        html += '</li>';
                    });
                }
            }
            html += '</ul>';
            html += '</div>';
        }

        html += '</li>';
        return html;
    }

    /**
     * Get the display name of a question group, falling back to its number
     *
     * @param {Object} questiongroup the question group
     * @returns {string} the group name (unescaped)
     */
    groupName(questiongroup) {
        return (typeof questiongroup.group_name === 'string' && questiongroup.group_name.trim().length > 0)
            ? questiongroup.group_name
            : UIHelpers.translate('groupNumber').replace('%d', questiongroup.group_order);
    }

    /**
     * Render the "Move up" / "Move down" dropdown items, the keyboard and
     * single-pointer alternative to dragging (WCAG 2.1.1, 2.5.7)
     *
     * @param {string} dataAttributes data attributes identifying the item to move
     * @param {boolean} canMoveUp whether the item can be moved up
     * @param {boolean} canMoveDown whether the item can be moved down
     * @returns {string} the dropdown items HTML
     */
    renderMoveItems(dataAttributes, canMoveUp, canMoveDown) {
        var html = '';
        html += '<li><button type="button" class="dropdown-item questionexplorer-move" data-direction="up" ' + dataAttributes + (canMoveUp ? '' : ' disabled') + '>';
        html += '<span class="ri-arrow-up-line" aria-hidden="true"></span> ' + UIHelpers.escapeHtml(UIHelpers.translate('moveUp'));
        html += '</button></li>';
        html += '<li><button type="button" class="dropdown-item questionexplorer-move" data-direction="down" ' + dataAttributes + (canMoveDown ? '' : ' disabled') + '>';
        html += '<span class="ri-arrow-down-line" aria-hidden="true"></span> ' + UIHelpers.escapeHtml(UIHelpers.translate('moveDown'));
        html += '</button></li>';
        html += '<li role="separator" class="dropdown-divider"></li>';
        return html;
    }

    /**
     * Create the live region used to announce keyboard moves. It lives outside
     * the explorer container so re-rendering doesn't recreate it.
     *
     * @returns {void}
     */
    ensureLiveRegion() {
        if (document.getElementById('questionexplorer-live-region')) return;
        var region = document.createElement('div');
        region.id = 'questionexplorer-live-region';
        region.className = 'visually-hidden';
        region.setAttribute('role', 'status');
        region.setAttribute('aria-live', 'polite');
        document.body.appendChild(region);
    }

    /**
     * Announce a message to screen reader users
     *
     * @param {string} message the message to announce
     * @returns {void}
     */
    announce(message) {
        this.ensureLiveRegion();
        var region = document.getElementById('questionexplorer-live-region');
        region.textContent = '';
        // Change the content in a later tick so repeated identical messages are announced again
        setTimeout(function () { region.textContent = message; }, 100);
    }

    /**
     * Translate a message and fill its numbered %1$s, %2$s, ... placeholders
     *
     * @param {string} key translation key
     * @param {Array} values placeholder values, in placeholder order
     * @returns {string} the formatted message
     */
    formatMessage(key, values) {
        return UIHelpers.translate(key).replace(/%(\d+)\$s/g, function(match, position) {
            var value = values[position - 1];
            return value === undefined ? match : String(value);
        });
    }

    /**
     * Move focus back to the moved item after a re-render replaced its elements
     *
     * @returns {void}
     */
    restoreFocus() {
        if (!this.pendingFocusSelector || !this.container) return;
        var target = this.container.querySelector(this.pendingFocusSelector);
        if (target) {
            target.focus();
        }
    }

    /**
     * Get question groups sorted by their group order
     *
     * @param {Array} questiongroups the question groups
     * @returns {Array} a new array with the same group objects in display order
     */
    orderedGroups(questiongroups) {
        return LS.ld.orderBy(
            questiongroups,
            function(g) { return UIHelpers.parseIntOr(g.group_order, 999999); },
            ['asc']
        );
    }

    /**
     * Get the questions of a group sorted by their question order
     *
     * @param {Object} questiongroup the question group
     * @returns {Array} a new array with the same question objects in display order
     */
    orderedQuestions(questiongroup) {
        return LS.ld.orderBy(
            questiongroup.questions || [],
            function(q) { return UIHelpers.parseIntOr(q.question_order, 999999); },
            ['asc']
        );
    }

    /**
     * Move a question group one position up or down and save the new order
     *
     * @param {number} gid id of the group to move
     * @param {string} direction 'up' or 'down'
     * @returns {void}
     */
    moveQuestionGroup(gid, direction) {
        var questiongroups = StateManager.get('questiongroups') || [];
        var ordered = this.orderedGroups(questiongroups);
        var index = ordered.findIndex(function(g) { return g.gid === gid; });
        var targetIndex = index + (direction === 'up' ? -1 : 1);
        if (index === -1 || targetIndex < 0 || targetIndex >= ordered.length) return;

        var moved = ordered.splice(index, 1)[0];
        ordered.splice(targetIndex, 0, moved);
        ordered.forEach(function(g, idx) { g.group_order = idx + 1; });
        StateManager.commit('updateQuestiongroups', questiongroups);

        this.announce(this.formatMessage('movedToPosition', [targetIndex + 1, ordered.length]));
        this.saveKeyboardMove('.questiongroup-dropdown-toggle[data-gid="' + gid + '"]');
    }

    /**
     * Move a question one position up or down, into the neighbouring group when
     * it is already at the edge of its own group, and save the new order
     *
     * @param {number} qid id of the question to move
     * @param {number} gid id of the group the question currently belongs to
     * @param {string} direction 'up' or 'down'
     * @returns {void}
     */
    moveQuestion(qid, gid, direction) {
        var up = direction === 'up';
        var questiongroups = StateManager.get('questiongroups') || [];
        var groups = this.orderedGroups(questiongroups);
        var groupIndex = groups.findIndex(function(g) { return g.gid === gid; });
        if (groupIndex === -1) return;

        var sourceGroup = groups[groupIndex];
        var sourceQuestions = this.orderedQuestions(sourceGroup);
        var questionIndex = sourceQuestions.findIndex(function(q) { return q.qid === qid; });
        if (questionIndex === -1) return;

        var question = sourceQuestions[questionIndex];
        var targetGroup = sourceGroup;
        var targetQuestions = sourceQuestions;
        var targetIndex = questionIndex + (up ? -1 : 1);
        var changesGroup = up ? targetIndex < 0 : targetIndex >= sourceQuestions.length;

        if (changesGroup) {
            targetGroup = groups[groupIndex + (up ? -1 : 1)];
            if (!targetGroup) return;
            targetQuestions = this.orderedQuestions(targetGroup);
            targetIndex = up ? targetQuestions.length : 0;
            LS.ld.remove(sourceGroup.questions, function(q) { return q.qid === qid; });
            targetGroup.questions = targetGroup.questions || [];
            targetGroup.questions.push(question);
            question.gid = targetGroup.gid;
            this.addActive(targetGroup.gid);
        }

        sourceQuestions.splice(questionIndex, 1);
        targetQuestions.splice(targetIndex, 0, question);
        sourceQuestions.forEach(function(q, idx) { q.question_order = idx + 1; });
        targetQuestions.forEach(function(q, idx) { q.question_order = idx + 1; });
        StateManager.commit('updateQuestiongroups', questiongroups);

        this.announce(changesGroup
            ? this.formatMessage('movedToGroup', [this.groupName(targetGroup), targetIndex + 1, targetQuestions.length])
            : this.formatMessage('movedToPosition', [targetIndex + 1, targetQuestions.length])
        );
        this.saveKeyboardMove('.question-dropdown-toggle[data-qid="' + qid + '"]');
    }

    /**
     * Re-render and save after a keyboard move, keeping focus on the moved item
     * through all re-renders the save triggers
     *
     * @param {string} focusSelector selector of the element to keep focused
     * @returns {void}
     */
    saveKeyboardMove(focusSelector) {
        this.pendingFocusSelector = focusSelector;
        this.renderExplorer();
        var saving = this.onOrderChange ? this.onOrderChange() : null;
        Promise.resolve(saving).finally(() => {
            this.restoreFocus();
            this.pendingFocusSelector = null;
        });
    }

    /**
     * Add to active array
     */
    addActive(questionGroupId) {
        if (!this.isOpen(questionGroupId)) {
            this.active.push(questionGroupId);
        }
        StateManager.commit('questionGroupOpenArray', this.active);
    }

    /**
     * Toggle question group - matching Vue toggleQuestionGroup()
     */
    toggleQuestionGroup(questiongroup) {
        if (!this.isOpen(questiongroup.gid)) {
            this.addActive(questiongroup.gid);
            StateManager.commit('lastQuestionGroupOpen', questiongroup);
        } else {
            var newActive = this.active.filter(function(gid) { return gid !== questiongroup.gid; });
            this.active = newActive.slice();
            StateManager.commit('questionGroupOpenArray', this.active);
        }
        this.renderExplorer();
    }

    /**
     * Open question - matching Vue openQuestion()
     */
    openQuestion(question) {
        this.addActive(question.gid);
        StateManager.commit('lastQuestionOpen', question);
        $(document).trigger('pjax:load', { url: question.link });
    }

    /**
     * Collapse all
     */
    collapseAll() {
        this.active = [];
        StateManager.commit('questionGroupOpenArray', this.active);
        this.renderExplorer();
    }

    /**
     * Expand all
     */
    expandAll() {
        var questiongroups = StateManager.get('questiongroups') || [];
        this.active = questiongroups.map(function(questiongroup) { return questiongroup.gid; });
        StateManager.commit('questionGroupOpenArray', this.active);
        this.renderExplorer();
    }

    /**
     * Bind events
     */
    bindEvents() {
        if (!this.container) return;
        var $container = $(this.container);

        $container.off('.qe');

        // Toggle organizer
        $container.on('click.qe', '.toggle-organizer-btn', (e) => {
            e.preventDefault();

            // Update server and re-render
            Actions.unlockLockOrganizer().then(() => {
                // Toggle the state locally
                this.renderExplorer();
            });
        });

        // Collapse all
        $container.on('click.qe', '.collapse-all-btn', (e) => {
            e.preventDefault();
            this.collapseAll();
        });

        // Expand all
        $container.on('click.qe', '.expand-all-btn', (e) => {
            e.preventDefault();
            this.expandAll();
        });

        // Toggle question group
        $container.on('click.qe', '.toggle-questiongroup', (e) => {
            e.preventDefault();
            e.stopPropagation();
            var gid = $(e.currentTarget).data('gid');
            var questiongroups = StateManager.get('questiongroups') || [];
            var group = questiongroups.find(function(g) { return g.gid === gid; });
            if (group) {
                this.toggleQuestionGroup(group);
            }
        });

        // Question group link click - use PJAX navigation
        $container.on('click.qe', '.questiongroup-link', (e) => {
            e.preventDefault();
            e.stopPropagation();
            var gid = $(e.currentTarget).data('gid');
            var questiongroups = StateManager.get('questiongroups') || [];
            var group = questiongroups.find(function(g) { return g.gid === gid; });
            if (group) {
                this.addActive(group.gid);
                StateManager.commit('lastQuestionGroupOpen', group);
                $(document).trigger('pjax:load', { url: $(e.currentTarget).attr('href') });
            }
        });

        // Question link click - matching Vue @click.stop.prevent="openQuestion(question)"
        $container.on('click.qe', '.question-link', (e) => {
            e.preventDefault();
            e.stopPropagation();
            var qid = $(e.currentTarget).data('qid');
            var gid = $(e.currentTarget).data('gid');
            var questiongroups = StateManager.get('questiongroups') || [];
            var group = questiongroups.find(function(g) { return g.gid === gid; });
            if (group && group.questions) {
                var question = group.questions.find(function(q) { return q.qid === qid; });
                if (question) {
                    this.openQuestion(question);
                }
            }
        });

        // Keyboard / single-pointer reordering from the 3-dot menus
        $container.on('click.qe', '.questionexplorer-move', (e) => {
            e.preventDefault();
            var $button = $(e.currentTarget);
            var direction = $button.data('direction');
            if ($button.is('[data-qid]')) {
                this.moveQuestion($button.data('qid'), $button.data('gid'), direction);
            } else {
                this.moveQuestionGroup($button.data('gid'), direction);
            }
        });

        // The question / group 3-dot menu is always visible; on hover only shift it
        // clear of the resize button for the row the resize button actually overlaps
        // vertically. Measured against the row (not the dropdown) so shifting it can't
        // feed back.
        $container.on('mouseover.qe', '.question-question-list-item, .q-group', function(e) {
            var resizeBtn = document.querySelector('#sidebar .resize-btn');
            if (resizeBtn) {
                var b = resizeBtn.getBoundingClientRect();
                var r = this.getBoundingClientRect();
                this.classList.toggle('resize-covered', b.top < r.bottom && b.bottom > r.top);
            }
        });

        // Drag events
        this.bindDragEvents($container);
    }

    /**
     * Bind drag events - matching Vue drag methods exactly
     * IMPORTANT: Avoid calling renderExplorer() during active drag to maintain smooth operation
     */
    bindDragEvents($container) {
        // Question group drag start - matching startDraggingGroup
        $container.on('dragstart.qe', '.questiongroup-drag-handle[draggable="true"]', (e) => {
            var gid = $(e.currentTarget).data('gid');
            var questiongroups = StateManager.get('questiongroups') || [];
            this.draggedQuestionGroup = questiongroups.find(function(g) { return g.gid === gid; });
            this.questiongroupDragging = true;
            this.orderChanged = false; // Reset flag at start of drag
            this.lastDragenterGid = null; // Reset dragenter tracking
            this.dragStartGroupOrder = LS.ld.orderBy(
                questiongroups,
                function(g) { return UIHelpers.parseIntOr(g.group_order, 999999); },
                ['asc']
            ).map(function(g) { return g.gid; });
            e.originalEvent.dataTransfer.setData('text/plain', 'node');
            // Add dragged class directly without re-rendering
            $(e.currentTarget).closest('.list-group-item').addClass('dragged');
        });

        // Question group drag end - matching endDraggingGroup
        $container.on('dragend.qe', '.questiongroup-drag-handle', () => {
            if (this.draggedQuestionGroup !== null) {
                this.draggedQuestionGroup = null;
                this.questiongroupDragging = false;
                this.dragStartGroupOrder = null;
                // Only trigger order update if order actually changed
                if (this.orderChanged && this.onOrderChange) {
                    this.onOrderChange();
                }
                this.orderChanged = false; // Reset flag
                this.renderExplorer();
            }
        });

        // Question group dragenter - matching dragoverQuestiongroup
        $container.on('dragenter.qe', '.list-group-item[data-gid]', (e) => {
            e.preventDefault();
            var gid = $(e.currentTarget).data('gid');

            // Skip duplicate dragenter events for same target (fires multiple times for child elements)
            if (this.questiongroupDragging && gid === this.lastDragenterGid) return;
            this.lastDragenterGid = gid;

            var questiongroups = StateManager.get('questiongroups') || [];
            var questiongroupObject = questiongroups.find(function(g) { return g.gid === gid; });

            if (this.questiongroupDragging && this.draggedQuestionGroup && questiongroupObject) {
                $container.find('.list-group-item').removeClass('dragged');
                $(e.currentTarget).closest('.questiongroup-list-group > .list-group-item').addClass('dragged');

                var startOrder = this.dragStartGroupOrder || [];
                var draggedGid = this.draggedQuestionGroup.gid;
                var targetIndex = startOrder.indexOf(gid);
                if (targetIndex !== -1 && startOrder.indexOf(draggedGid) !== -1) {
                    var newOrder = startOrder.filter(function(g) { return g !== draggedGid; });
                    newOrder.splice(targetIndex, 0, draggedGid);
                    LS.ld.each(newOrder, function(orderedGid, idx) {
                        var group = questiongroups.find(function(g) { return g.gid === orderedGid; });
                        if (group) {
                            group.group_order = idx + 1;
                        }
                    });
                    StateManager.commit('updateQuestiongroups', questiongroups);
                    this.orderChanged = newOrder.some(function(g, idx) { return g !== startOrder[idx]; });
                    // Don't re-render during drag - wait for dragend
                }
            } else if (this.questionDragging && this.draggedQuestion && questiongroupObject) {
                if (window.SideMenuData.isActive) return;

                this.addActive(questiongroupObject.gid);

                if (this.draggedQuestion.gid !== questiongroupObject.gid) {
                    var removedFromInitial = LS.ld.remove(this.draggedQuestionsGroup.questions, (q) => {
                        return q.qid === this.draggedQuestion.qid;
                    });

                    if (removedFromInitial.length > 0) {
                        this.draggedQuestion.question_order = null;
                        questiongroupObject.questions.push(this.draggedQuestion);
                        this.draggedQuestion.gid = questiongroupObject.gid;

                        if (questiongroupObject.group_order > this.draggedQuestionsGroup.group_order) {
                            this.draggedQuestion.question_order = 0;
                            LS.ld.each(questiongroupObject.questions, function(q) {
                                q.question_order = parseInt(q.question_order) + 1;
                            });
                        } else {
                            this.draggedQuestion.question_order = this.draggedQuestionsGroup.questions.length + 1;
                        }

                        this.draggedQuestionsGroup = questiongroupObject;
                        StateManager.commit('updateQuestiongroups', questiongroups);
                        this.orderChanged = true; // Mark that order has changed
                        // Don't re-render during drag - wait for dragend
                    }
                }
            }
        });

        // Question drag start - matching startDraggingQuestion
        $container.on('dragstart.qe', '.question-drag-handle[draggable="true"]', (e) => {
            var qid = $(e.currentTarget).data('qid');
            var gid = $(e.currentTarget).data('gid');
            var questiongroups = StateManager.get('questiongroups') || [];
            var group = questiongroups.find(function(g) { return g.gid === gid; });

            if (group && group.questions) {
                this.draggedQuestion = group.questions.find(function(q) { return q.qid === qid; });
                this.draggedQuestionsGroup = group;
                this.questionDragging = true;
                this.orderChanged = false; // Reset flag at start of drag
                this.lastDragenterQid = null; // Reset dragenter tracking
                e.originalEvent.dataTransfer.setData('application/node', 'node');
                // Add dragged class directly without re-rendering
                $(e.currentTarget).closest('.question-question-list-item').addClass('dragged');
            }
        });

        // Question drag end - matching endDraggingQuestion
        $container.on('dragend.qe', '.question-drag-handle', () => {
            if (this.questionDragging) {
                this.questionDragging = false;
                this.draggedQuestion = null;
                this.draggedQuestionsGroup = null;
                // Only trigger order update if order actually changed
                if (this.orderChanged && this.onOrderChange) {
                    this.onOrderChange();
                }
                this.orderChanged = false; // Reset flag
                this.renderExplorer();
            }
        });

        // Question dragenter - matching dragoverQuestion
        $container.on('dragenter.qe', '.question-question-list-item', (e) => {
            e.preventDefault();
            e.stopPropagation();
            var qid = $(e.currentTarget).data('qid');
            var gid = $(e.currentTarget).data('gid');

            // Skip duplicate dragenter events for same target (fires multiple times for child elements)
            if (this.questionDragging && qid === this.lastDragenterQid) return;
            this.lastDragenterQid = qid;

            if (this.questionDragging && this.draggedQuestion) {
                // Highlight the drop destination
                $container.find('.question-question-list-item').removeClass('dragged');
                $(e.currentTarget).addClass('dragged');

                if (window.SideMenuData.isActive && this.draggedQuestion.gid !== gid) return;

                var questiongroups = StateManager.get('questiongroups') || [];
                var group = questiongroups.find(function(g) { return g.gid === gid; });

                if (group && group.questions) {
                    var questionObject = group.questions.find(function(q) { return q.qid === qid; });
                    if (questionObject && questionObject.qid !== this.draggedQuestion.qid) {
                        var orderSwap = questionObject.question_order;
                        questionObject.question_order = this.draggedQuestion.question_order;
                        this.draggedQuestion.question_order = orderSwap;
                        StateManager.commit('updateQuestiongroups', questiongroups);
                        this.orderChanged = true; // Mark that order has changed
                        // Don't re-render during drag - wait for dragend
                    }
                }
            }
        });

        // Allow drop
        $container.on('dragover.qe', '.list-group-item, .question-question-list-item', function(e) {
            e.preventDefault();
        });
    }
}

export default QuestionExplorer;
