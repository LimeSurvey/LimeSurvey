/**
 * Sidebar - Main sidebar component (vanilla JS)
 * Replaces sidebar.vue
 */
import StateManager from '../StateManager.js';
import Actions from '../Actions.js';
import UIHelpers from '../UIHelpers.js';
import SideMenu from './SideMenu.js';
import QuickMenu from './QuickMenu.js';
import QuestionExplorer from './QuestionExplorer.js';

class Sidebar {
    constructor() {
        this.container = null;
        this.sideBarWidth = '315';
        this.isMouseDown = false;
        this.isMouseDownTimeOut = null;
        this.smallScreenHidden = false;
        this.showLoader = false;
        this.loading = true;

        // Component instances
        this.sideMenu = new SideMenu();
        this.quickMenu = new QuickMenu();
        this.questionExplorer = new QuestionExplorer();

        // Bind methods
        this.handleMouseDown = this.handleMouseDown.bind(this);
        this.handleMouseUp = this.handleMouseUp.bind(this);
        this.handleMouseLeave = this.handleMouseLeave.bind(this);
        this.handleMouseMove = this.handleMouseMove.bind(this);
        this.handleQuestionGroupOrderChange = this.handleQuestionGroupOrderChange.bind(this);
        this.controlActiveLink = this.controlActiveLink.bind(this);
        this.handleUpdateSideBar = this.handleUpdateSideBar.bind(this);
        this.handleVueReloadRemote = this.handleVueReloadRemote.bind(this);
        this.handleVueRedraw = this.handleVueRedraw.bind(this);
        this.handlePjaxSend = this.handlePjaxSend.bind(this);
        this.handlePjaxRefresh = this.handlePjaxRefresh.bind(this);
        this.handleStateChange = this.handleStateChange.bind(this);
    }

    /**
     * Initialize the sidebar
     * @param {HTMLElement} containerEl
     */
    init(containerEl) {
        this.container = containerEl;

        // Set initial collapse state for mobile
        if (window.innerWidth < 768) {
            StateManager.commit('changeIsCollapsed', false);
        }

        // Set survey active state
        StateManager.commit('setSurveyActiveState', parseInt(window.SideMenuData.isActive) === 1);

        // Initialize sidebar width (always as a number)
        if (StateManager.getComputed('isCollapsed')) {
            this.sideBarWidth = 98;
        } else {
            const savedWidth = StateManager.get('sidebarwidth');
            this.sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
        }

        // Subscribe to state changes to keep sideBarWidth in sync
        StateManager.subscribe(this.handleStateChange);

        // Process base menus from SideMenuData
        if (window.SideMenuData && window.SideMenuData.basemenus) {
            LS.ld.each(window.SideMenuData.basemenus, (entries, position) => {
                this.setBaseMenuPosition(entries, position);
            });
        }

        this.render();
        this.bindEvents();
        this.calculateHeight();

        // Initial data load - check if menus are already loaded from basemenus
        const sidemenus = StateManager.get('sidemenus');
        if (sidemenus && sidemenus.length > 0) {
            // Menus already loaded from basemenus, no need to show loading
            this.loading = false;
        } else {
            this.loading = true;
        }
        this.renderContent();

        // Trigger sidebar mounted event
        $(document).trigger('sidebar:mounted');
    }

    /**
     * Handle state changes
     */
    handleStateChange(key, newValue, oldValue) {
        if (key === 'sidebarwidth' && !StateManager.getComputed('isCollapsed')) {
            // Ensure we store as a number
            this.sideBarWidth = typeof newValue === 'string' ? parseInt(newValue) : newValue;
            // Update the DOM directly for smooth resize
            const sidebar = document.getElementById('sidebar');
            if (sidebar && !this.isMouseDown) {
                sidebar.style.width = this.sideBarWidth + 'px';
            }
        } else if (key === 'isCollapsed') {
            if (newValue) {
                this.sideBarWidth = 98;
            } else {
                const savedWidth = StateManager.get('sidebarwidth');
                this.sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
            }
            this.render();
        }
    }

    /**
     * Set base menu position
     */
    setBaseMenuPosition(entries, position) {
        const orderedEntries = LS.ld.orderBy(
            entries,
            function(a) { return parseInt(a.order || 999999); },
            ['desc']
        );

        switch (position) {
            case 'side':
                StateManager.commit('updateSidemenus', orderedEntries);
                break;
            case 'collapsed':
                StateManager.commit('updateCollapsedmenus', orderedEntries);
                break;
        }
    }

    /**
     * Calculate sidebar height based on viewport
     */
    calculateHeight() {
        const height = $('#in_survey_common').height();
        if (height) {
            StateManager.commit('changeSideBarHeight', height);
        }
    }

    /**
     * Get current sidebar width (returns numeric value without 'px')
     */
    getSideBarWidth() {
        const width = StateManager.getComputed('isCollapsed') ? 98 : this.sideBarWidth;
        // Ensure we always return a number by parsing if needed
        return typeof width === 'string' ? parseInt(width) : width;
    }

    /**
     * Calculate sidebar menu height
     */
    calculateSideBarMenuHeight() {
        const currentSideBar = StateManager.get('sideBarHeight');
        return LS.ld.min([currentSideBar, Math.floor(screen.height * 2)]) + 'px';
    }

    /**
     * Toggle collapse state
     */
    toggleCollapse() {
        const isCollapsed = StateManager.get('isCollapsed');
        StateManager.commit('changeIsCollapsed', !isCollapsed);

        if (StateManager.getComputed('isCollapsed')) {
            this.sideBarWidth = 98;
        } else {
            const savedWidth = StateManager.get('sidebarwidth');
            this.sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
        }

        this.render();
    }

    /**
     * Toggle small screen hidden state
     */
    toggleSmallScreenHide() {
        this.smallScreenHidden = !this.smallScreenHidden;
        this.render();
    }

    /**
     * Change current tab
     */
    changeCurrentTab(tab) {
        // Normalize tab name - 'structure' is alias for 'questiontree'
        if (tab === 'structure') {
            tab = 'questiontree';
        }
        // Only allow valid tab values
        if (tab !== 'settings' && tab !== 'questiontree') {
            tab = 'settings';
        }

        StateManager.commit('changeCurrentTab', tab);
        this.render();
    }

    /**
     * Handle mouse down for resize
     */
    handleMouseDown(e) {
        if (UIHelpers.useMobileView()) {
            StateManager.commit('changeIsCollapsed', false);
            this.smallScreenHidden = !this.smallScreenHidden;
            this.render();
            return;
        }

        this.isMouseDown = !StateManager.getComputed('isCollapsed');
        $('#sidebar').removeClass('transition-animate-width');
        $('#pjax-content').removeClass('transition-animate-width');
    }

    /**
     * Handle mouse up for resize
     */
    handleMouseUp(e) {
        if (this.isMouseDown) {
            this.isMouseDown = false;
            const widthNum = typeof this.sideBarWidth === 'string' ? parseInt(this.sideBarWidth) : this.sideBarWidth;
            if (widthNum < 250 && !StateManager.getComputed('isCollapsed')) {
                this.toggleCollapse();
                StateManager.commit('changeSidebarwidth', 340);
            } else {
                StateManager.commit('changeSidebarwidth', widthNum);
            }
            $('#sidebar').addClass('transition-animate-width');
            $('#pjax-content').removeClass('transition-animate-width');
        }
    }

    /**
     * Handle mouse leave for resize
     */
    handleMouseLeave(e) {
        if (this.isMouseDown) {
            this.isMouseDownTimeOut = setTimeout(() => {
                this.handleMouseUp(e);
            }, 1000);
        }
    }

    /**
     * Handle mouse move for resize
     */
    handleMouseMove(e) {
        if (!this.isMouseDown) return;

        const isRTL = StateManager.getComputed('isRTL');

        // Prevent emitting unwanted value on dragend
        if (e.screenX === 0 && e.screenY === 0) {
            return;
        }

        if (isRTL) {
            if ((window.innerWidth - e.clientX) > screen.width / 2) {
                StateManager.commit('maxSideBarWidth', true);
                return;
            }
            this.sideBarWidth = (window.innerWidth - e.pageX) - 8;
        } else {
            if (e.clientX > screen.width / 2) {
                StateManager.commit('maxSideBarWidth', true);
                return;
            }
            this.sideBarWidth = e.pageX - 4;
        }

        StateManager.commit('changeSidebarwidth', this.sideBarWidth);
        StateManager.commit('maxSideBarWidth', false);

        window.clearTimeout(this.isMouseDownTimeOut);
        this.isMouseDownTimeOut = null;

        // Update sidebar width in real-time (sideBarWidth is a number, add px)
        $('#sidebar').css('width', this.sideBarWidth + 'px');
    }

    /**
     * Control active link highlighting
     */
    controlActiveLink() {
        const currentUrl = window.location.href;
        const sidemenus = StateManager.get('sidemenus') || [];
        const collapsedmenus = StateManager.get('collapsedmenus') || [];
        const questiongroups = StateManager.get('questiongroups') || [];

        // Check for corresponding menuItem
        let lastMenuItemObject = false;
        LS.ld.each(sidemenus, function(itm) {
            LS.ld.each(itm.entries, function(itmm) {
                if (LS.ld.endsWith(currentUrl, itmm.link)) {
                    lastMenuItemObject = itmm;
                }
            });
        });

        // Check for quickmenu menuLinks
        let lastQuickMenuItemObject = false;
        LS.ld.each(collapsedmenus, function(itm) {
            LS.ld.each(itm.entries, function(itmm) {
                if (LS.ld.endsWith(currentUrl, itmm.link)) {
                    lastQuickMenuItemObject = itmm;
                }
            });
        });

        // Check for corresponding question group object
        let lastQuestionGroupObject = false;
        LS.ld.each(questiongroups, function(itm) {
            const regTest = new RegExp(
                'questionGroupsAdministration/view\\?surveyid=\\d*&gid=' + itm.gid +
                '|questionGroupsAdministration/edit\\?surveyid=\\d*&gid=' + itm.gid +
                '|questionGroupsAdministration/view/surveyid/\\d*/gid/' + itm.gid +
                '|questionGroupsAdministration/edit/surveyid/\\d*/gid/' + itm.gid
            );
            if (regTest.test(currentUrl) || LS.ld.endsWith(currentUrl, itm.link)) {
                lastQuestionGroupObject = itm;
                return false;
            }
        });

        // Check for corresponding question
        let lastQuestionObject = false;
        const questionIdInput = document.querySelector('#edit-question-form [name="question[qid]"]');
        if (questionIdInput !== null) {
            const questionId = questionIdInput.value;
            LS.ld.each(questiongroups, function(itm) {
                LS.ld.each(itm.questions, function(itmm) {
                    if (questionId === itmm.qid) {
                        lastQuestionObject = itmm;
                        lastQuestionGroupObject = itm;
                        return false;
                    }
                });
                if (lastQuestionObject !== false) {
                    return false;
                }
            });
        }

        // Unload every selection
        StateManager.commit('closeAllMenus');

        if (lastMenuItemObject !== false && !StateManager.getComputed('isCollapsed')) {
            StateManager.commit('lastMenuItemOpen', lastMenuItemObject);
        }
        if (lastQuickMenuItemObject !== false && StateManager.getComputed('isCollapsed')) {
            StateManager.commit('lastMenuItemOpen', lastQuickMenuItemObject);
        }
        if (lastQuestionGroupObject !== false) {
            StateManager.commit('lastQuestionGroupOpen', lastQuestionGroupObject);
            StateManager.commit('addToQuestionGroupOpenArray', lastQuestionGroupObject);
        }
        if (lastQuestionObject !== false) {
            StateManager.commit('lastQuestionOpen', lastQuestionObject);
        }
    }

    /**
     * Handle question group order change
     */
    handleQuestionGroupOrderChange() {
        this.showLoader = true;
        this.render();

        const questiongroups = StateManager.get('questiongroups');
        const surveyid = StateManager.get('surveyid');

        Actions.updateQuestionGroupOrder(questiongroups, surveyid)
            .then(() => {
                return Actions.getQuestions();
            })
            .then(() => {
                this.showLoader = false;
                this.render();
            })
            .catch((error) => {
                console.ls.error('questiongroups updating error!', error);
                Actions.getQuestions().then(() => {
                    this.showLoader = false;
                    this.render();
                });
            });
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // Window resize
        window.addEventListener('resize', LS.ld.debounce(() => this.calculateHeight(), 300));

        // Body mouse move for resize
        $('body').on('mousemove', this.handleMouseMove);

        // Custom events
        $(document).on('vue-sidemenu-update-link', this.controlActiveLink);
        $(document).on('vue-reload-remote', this.handleVueReloadRemote);
        $(document).on('vue-redraw', this.handleVueRedraw);
        $(document).on('pjax:send', this.handlePjaxSend);
        $(document).on('pjax:refresh', this.handlePjaxRefresh);

        // EventBus equivalent for updateSideBar
        $(document).on('updateSideBar', this.handleUpdateSideBar);
    }

    /**
     * Handle vue-reload-remote event
     */
    handleVueReloadRemote() {
        Promise.all([
            Actions.getQuestions(),
            Actions.collectMenus()
        ]).then(() => {
            this.controlActiveLink();
            this.renderContent();
        });
        StateManager.commit('newToggleKey');
    }

    /**
     * Handle vue-redraw event
     */
    handleVueRedraw() {
        Promise.all([
            Actions.getQuestions(),
            Actions.collectMenus()
        ]).then(() => {
            this.controlActiveLink();
            this.renderContent();
        });
    }

    /**
     * Handle pjax:send event
     */
    handlePjaxSend() {
        if (UIHelpers.useMobileView() && this.smallScreenHidden) {
            this.smallScreenHidden = false;
            this.render();
        }
    }

    /**
     * Handle pjax:refresh event
     */
    handlePjaxRefresh() {
        this.controlActiveLink();
    }

    /**
     * Handle updateSideBar event
     */
    handleUpdateSideBar(e, payload) {
        this.loading = true;
        this.renderContent();

        const promises = [Promise.resolve()];

        if (payload && payload.updateQuestions) {
            promises.push(Actions.getQuestions());
        }
        if (payload && payload.collectMenus) {
            promises.push(Actions.collectMenus());
        }
        if (payload && payload.activeMenuIndex) {
            this.controlActiveLink();
        }

        Promise.all(promises)
            .catch((errors) => {
                console.ls.error(errors);
            })
            .finally(() => {
                this.loading = false;
                this.renderContent();
            });
    }

    /**
     * Render the sidebar HTML
     */
    render() {
        if (!this.container) return;

        const isCollapsed = StateManager.getComputed('isCollapsed');
        const currentTab = StateManager.get('currentTab');
        const isRTL = StateManager.getComputed('isRTL');
        const inSurveyViewHeight = StateManager.get('inSurveyViewHeight');
        const currentSidebarWidth = this.getSideBarWidth();

        let classes = 'd-flex col-lg-4 ls-ba position-relative transition-animate-width';
        if (this.smallScreenHidden) {
            classes += ' toggled';
        }

        const showMainContent = (UIHelpers.useMobileView() && this.smallScreenHidden) || !UIHelpers.useMobileView();
        const showPlaceholder = UIHelpers.useMobileView() && this.smallScreenHidden;
        const showResizeOverlay = this.isMouseDown;

        let html = '<div id="sidebar" class="' + classes + '" style="width: ' + currentSidebarWidth + 'px; max-height: ' + inSurveyViewHeight + 'px; display: flex;">';

        if (showMainContent) {
            // Loader overlay
            if (this.showLoader) {
                html += '<div class="sidebar_loader" style="width: ' + this.getSideBarWidth() + 'px; height: ' + $('#sidebar').height() + 'px;">' +
                    '<div class="ls-flex ls-flex-column fill align-content-center align-items-center">' +
                        '<i class="ri-loader-2-fill remix-2x remix-spin"></i>' +
                    '</div>' +
                '</div>';
            }

            html += '<div class="col-12 mainContentContainer">';
            html += '<div class="mainMenu col-12 position-relative">';

            // Sidebar state toggle (tabs)
            html += this.renderStateToggle(isCollapsed, currentTab, isRTL);

            // Side menu content
            html += '<div id="sidemenu-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'settings' ? 'block' : 'none') + '; min-height: ' + this.calculateSideBarMenuHeight() + ';"></div>';

            // Question explorer content
            html += '<div id="questionexplorer-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'questiontree' ? 'block' : 'none') + '; min-height: ' + this.calculateSideBarMenuHeight() + ';"></div>';

            // Quick menu (collapsed state)
            html += '<div id="quickmenu-container" style="display: ' + (isCollapsed ? 'block' : 'none') + ';"></div>';

            // Resize handle
            if ((UIHelpers.useMobileView() && !this.smallScreenHidden) || !UIHelpers.useMobileView()) {
                html += '<div class="resize-handle ls-flex-column" style="height: ' + this.calculateSideBarMenuHeight() + ';">';
                if (!isCollapsed) {
                    html += '<button class="btn resize-btn" type="button">' +
                        '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>' +
                        '</svg>' +
                    '</button>';
                }
                html += '</div>';
            }

            html += '</div>'; // .mainMenu
            html += '</div>'; // .mainContentContainer
        }

        // Placeholder for mobile
        if (showPlaceholder) {
            html += '<div class="scoped-placeholder-greyed-area"> </div>';
        }

        // Resize overlay to prevent mouse issues
        if (showResizeOverlay) {
            html += '<div style="position: fixed; inset: 0;"></div>';
        }

        html += '</div>'; // #sidebar

        this.container.innerHTML = html;

        // Bind internal events after render
        this.bindInternalEvents();

        // Render sub-components
        this.renderContent();
    }

    /**
     * Render state toggle (tabs)
     */
    renderStateToggle(isCollapsed, currentTab, isRTL) {
        let html = '<div class="ls-space col-12">';
        html += '<div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 bottom-0 top-0">';

        if (!isCollapsed) {
            html += '<div class="ls-flex-item grow-10 col-12">' +
                '<ul class="nav nav-tabs" id="surveysystem" role="tablist">' +
                    '<li class="nav-item">' +
                        '<a id="adminsidepanel__sidebar--selectorSettingsButton" class="nav-link sidebar-tab-link' + (currentTab === 'settings' ? ' active' : '') + '" href="#settings" data-tab="settings" role="tab">' +
                            UIHelpers.translate('settings') +
                        '</a>' +
                    '</li>' +
                    '<li class="nav-item">' +
                        '<a id="adminsidepanel__sidebar--selectorStructureButton" class="nav-link sidebar-tab-link' + (currentTab === 'questiontree' ? ' active' : '') + '" href="#structure" data-tab="questiontree" role="tab">' +
                            UIHelpers.translate('structure') +
                        '</a>' +
                    '</li>' +
                '</ul>' +
            '</div>';
        } else {
            const arrowClass = isRTL ? 'ri-arrow-left-s-line' : 'ri-arrow-right-s-line';
            html += '<button class="btn btn-outline-secondary ls-space padding left-15 right-15 expand-sidebar-btn">' +
                '<i class="' + arrowClass + '"></i>' +
            '</button>';
        }

        html += '</div>';
        html += '</div>';

        return html;
    }

    /**
     * Render content for sub-components
     */
    renderContent() {
        const sidemenuContainer = document.getElementById('sidemenu-container');
        const questionExplorerContainer = document.getElementById('questionexplorer-container');
        const quickmenuContainer = document.getElementById('quickmenu-container');

        if (sidemenuContainer) {
            this.sideMenu.render(sidemenuContainer, this.loading);
        }

        if (questionExplorerContainer) {
            this.questionExplorer.render(questionExplorerContainer, this.loading, this.handleQuestionGroupOrderChange);
        }

        if (quickmenuContainer) {
            this.quickMenu.render(quickmenuContainer, this.loading);
        }

        // Re-initialize tooltips
        UIHelpers.redoTooltips();
    }

    /**
     * Bind internal events after render
     */
    bindInternalEvents() {
        // Tab switching
        $(this.container).off('click', '.sidebar-tab-link').on('click', '.sidebar-tab-link', (e) => {
            e.preventDefault();
            const tab = $(e.currentTarget).data('tab');
            this.changeCurrentTab(tab);
        });

        // Expand button (collapsed state)
        $(this.container).off('click', '.expand-sidebar-btn').on('click', '.expand-sidebar-btn', (e) => {
            e.preventDefault();
            this.toggleCollapse();
        });

        // Resize handle
        $(this.container).off('mousedown', '.resize-btn').on('mousedown', '.resize-btn', this.handleMouseDown);
        $(this.container).off('mouseup').on('mouseup', this.handleMouseUp);
        $(this.container).off('mouseleave', '#sidebar').on('mouseleave', '#sidebar', this.handleMouseLeave);

        // Placeholder click (mobile)
        $(this.container).off('click', '.scoped-placeholder-greyed-area').on('click', '.scoped-placeholder-greyed-area', () => this.toggleSmallScreenHide());
    }

    /**
     * Update sidebar (called externally)
     */
    update(options) {
        options = options || {};

        this.loading = true;
        this.renderContent();

        const promises = [];

        if (options.updateQuestions) {
            promises.push(Actions.getQuestions());
        }
        if (options.collectMenus) {
            promises.push(Actions.collectMenus());
        }

        Promise.all(promises)
            .then(() => {
                if (options.activeMenuIndex) {
                    this.controlActiveLink();
                }
            })
            .catch((error) => {
                console.ls.error(error);
            })
            .finally(() => {
                this.loading = false;
                this.renderContent();
            });
    }
}

export default Sidebar;
