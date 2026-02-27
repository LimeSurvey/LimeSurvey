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
    }

    /**
     * Render the question explorer
     */
    render(containerEl, loading, orderChangeCallback) {
        this.container = containerEl;
        this.onOrderChange = orderChangeCallback;

        if (!this.container) return;

        this.active = StateManager.get('questionGroupOpenArray') || [];

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

        var itemWidth = (parseInt(StateManager.get('sidebarwidth')) - 120) + 'px';

        var html = '<div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane h-100 pt-2">';

        // Toolbar buttons
        html += '<div class="ls-flex-row button-sub-bar mb-2">';
        html += '<div class="scoped-toolbuttons-right me-2">';
        html += '<button class="btn btn-sm btn-outline-secondary toggle-organizer-btn" title="' + UIHelpers.translate(allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle') + '">';
        html += '<i class="' + (allowOrganizer ? 'ri-lock-unlock-fill' : 'ri-lock-fill') + '"></i>';
        html += '</button>';
        html += '<button class="btn btn-sm btn-outline-secondary me-2 collapse-all-btn" title="' + UIHelpers.translate('collapseAll') + '">';
        html += '<i class="ri-link-unlink"></i>';
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

        orderedQuestionGroups.forEach((questiongroup) => {
            html += this.renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, itemWidth);
        });

        html += '</ul>';
        html += '</div>';
        html += '</div>';

        this.container.innerHTML = html;
        this.bindEvents();
        UIHelpers.redoTooltips();
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
     */
    renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
        var classes = 'list-group-item ls-flex-column' + this.questionGroupItemClasses(questiongroup);
        var isGroupOpen = this.isOpen(questiongroup.gid);
        var groupActivated = this.isActive(questiongroup.gid);

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
        html += '<div class="cursor-pointer me-1 toggle-questiongroup" data-gid="' + questiongroup.gid + '" style="' + rotateStyle + '">';
        html += '<i class="ri-arrow-right-s-fill"></i>';
        html += '</div>';

        // Question group name
        html += '<div class="w-100 position-relative">';
        html += '<div class="cursor-pointer">';
        html += '<a class="d-flex pjax" href="' + questiongroup.link + '">';
        html += '<span class="question_text_ellipsize" style="max-width: ' + itemWidth + '">' + UIHelpers.escapeHtml(questiongroup.group_name) + '</span>';
        html += '</a>';
        html += '</div>';

        // Dropdown and badge
        html += '<div class="position-absolute top-0 d-flex align-items-center" style="right:5px">';
        html += '<div class="toggle-questiongroup" data-gid="' + questiongroup.gid + '">';
        html += '<span class="badge reverse-color ls-space margin right-5">' + (questiongroup.questions ? questiongroup.questions.length : 0) + '</span>';
        html += '</div>';

        // Dropdown menu - always render, visibility controlled by hover class
        if (questiongroup.groupDropdown) {
            var dropdownStyle = groupActivated ? '' : ' style="display:none"';
            html += '<div class="dropdown questiongroup-dropdown' + (groupActivated ? ' active' : '') + '"' + dropdownStyle + '>';
            html += '<div class="ls-questiongroup-tools cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="ri-more-fill"></i>';
            html += '</div>';
            html += '<ul class="dropdown-menu">';

            for (var key in questiongroup.groupDropdown) {
                if (!questiongroup.groupDropdown.hasOwnProperty(key)) continue;
                var value = questiongroup.groupDropdown[key];

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
            html += this.renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, itemWidth);
        }

        html += '</li>';
        return html;
    }

    /**
     * Render questions list
     */
    renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
        var orderedQuestions = LS.ld.orderBy(
            questiongroup.questions,
            function(a) { return UIHelpers.parseIntOr(a.question_order, 999999); },
            ['asc']
        );

        var html = '<ul class="list-group background-muted padding-left question-question-list" style="padding-right:15px">';

        orderedQuestions.forEach((question) => {
            html += this.renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, itemWidth);
        });

        html += '</ul>';
        return html;
    }

    /**
     * Render single question - matching Vue template exactly
     */
    renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
        var classes = 'list-group-item question-question-list-item ls-flex-row align-itmes-flex-start ' + this.questionItemClasses(question);
        var itemActivated = StateManager.get('lastQuestionOpen') === question.qid;
        // Always show dropdown HTML, use CSS/JS hover to control visibility
        var showDropdown = true;
        var questionHasCondition = question.relevance !== '1';

        var html = '<li class="' + classes + '" data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '" data-is-hidden="' + question.hidden + '" data-questiontype="' + question.type + '" data-has-condition="' + questionHasCondition + '" title="' + UIHelpers.escapeHtml(question.question_flat) + '" data-bs-toggle="tooltip">';

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
        html += '<a href="' + question.link + '" class="col-9 pjax question-question-list-item-link display-as-container question-link" data-qid="' + question.qid + '" data-gid="' + question.gid + '">';
        html += '<span class="question_text_ellipsize ' + (question.hidden ? 'question-hidden' : '') + '" style="width: ' + itemWidth + '">';
        html += '[' + UIHelpers.escapeHtml(question.title) + '] &rsaquo; ' + UIHelpers.escapeHtml(question.question_flat);
        html += '</span>';
        html += '</a>';

        // Question dropdown - always render, visibility controlled by hover class
        if (question.questionDropdown) {
            var dropdownStyle = itemActivated ? 'right:10px' : 'right:10px;display:none';
            html += '<div class="dropdown question-dropdown position-absolute' + (itemActivated ? ' active' : '') + '" style="' + dropdownStyle + '">';
            html += '<div class="ls-question-tools ms-auto position-relative cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="ri-more-fill"></i>';
            html += '</div>';
            html += '<ul class="dropdown-menu">';

            for (var key in question.questionDropdown) {
                if (!question.questionDropdown.hasOwnProperty(key)) continue;
                var value = question.questionDropdown[key];

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

        // Hover events for dropdown visibility - matching Vue mouseover/mouseleave behavior
        // Show dropdown on question group hover
        $container.on('mouseover.qe', '.q-group[data-gid]', function(e) {
            $(this).find('.questiongroup-dropdown:not(.active)').show();
        });

        $container.on('mouseleave.qe', '.q-group[data-gid]', function(e) {
            $(this).find('.questiongroup-dropdown:not(.active)').hide();
        });

        // Show dropdown on question hover - use mouseover to match Vue behavior
        $container.on('mouseover.qe', '.question-question-list-item', function(e) {
            $(this).find('.question-dropdown:not(.active)').show();
        });

        $container.on('mouseleave.qe', '.question-question-list-item', function(e) {
            $(this).find('.question-dropdown:not(.active)').hide();
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
            e.originalEvent.dataTransfer.setData('text/plain', 'node');
            // Add dragged class directly without re-rendering
            $(e.currentTarget).closest('.list-group-item').addClass('dragged');
        });

        // Question group drag end - matching endDraggingGroup
        $container.on('dragend.qe', '.questiongroup-drag-handle', () => {
            if (this.draggedQuestionGroup !== null) {
                this.draggedQuestionGroup = null;
                this.questiongroupDragging = false;
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
            var questiongroups = StateManager.get('questiongroups') || [];
            var questiongroupObject = questiongroups.find(function(g) { return g.gid === gid; });

            if (this.questiongroupDragging && this.draggedQuestionGroup && questiongroupObject) {
                // Highlight the drop destination
                $container.find('.list-group-item').removeClass('dragged');
                $(e.currentTarget).addClass('dragged');
                var targetPosition = parseInt(questiongroupObject.group_order);
                var currentPosition = parseInt(this.draggedQuestionGroup.group_order);
                if (Math.abs(targetPosition - currentPosition) === 1) {
                    questiongroupObject.group_order = currentPosition;
                    this.draggedQuestionGroup.group_order = targetPosition;
                    StateManager.commit('updateQuestiongroups', questiongroups);
                    this.orderChanged = true; // Mark that order has changed
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
