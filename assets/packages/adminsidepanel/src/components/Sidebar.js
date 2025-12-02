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

const Sidebar = (function() {
    'use strict';

    let container = null;
    let sideBarWidth = '315';
    let isMouseDown = false;
    let isMouseDownTimeOut = null;
    let smallScreenHidden = false;
    let showLoader = false;
    let loading = true;

    /**
     * Initialize the sidebar
     * @param {HTMLElement} containerEl
     */
    function init(containerEl) {
        container = containerEl;

        // Set initial collapse state for mobile
        if (window.innerWidth < 768) {
            StateManager.commit('changeIsCollapsed', false);
        }

        // Set survey active state
        StateManager.commit('setSurveyActiveState', parseInt(window.SideMenuData.isActive) === 1);

        // Initialize sidebar width (always as a number)
        if (StateManager.getComputed('isCollapsed')) {
            sideBarWidth = 98;
        } else {
            const savedWidth = StateManager.get('sidebarwidth');
            sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
        }

        // Subscribe to state changes to keep sideBarWidth in sync
        StateManager.subscribe(function(key, newValue, oldValue) {
            if (key === 'sidebarwidth' && !StateManager.getComputed('isCollapsed')) {
                // Ensure we store as a number
                sideBarWidth = typeof newValue === 'string' ? parseInt(newValue) : newValue;
                // Update the DOM directly for smooth resize
                const sidebar = document.getElementById('sidebar');
                if (sidebar && !isMouseDown) {
                    sidebar.style.width = sideBarWidth + 'px';
                }
            } else if (key === 'isCollapsed') {
                if (newValue) {
                    sideBarWidth = 98;
                } else {
                    const savedWidth = StateManager.get('sidebarwidth');
                    sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
                }
                render();
            }
        });

        // Process base menus from SideMenuData
        if (window.SideMenuData && window.SideMenuData.basemenus) {
            LS.ld.each(window.SideMenuData.basemenus, setBaseMenuPosition);
        }

        render();
        bindEvents();
        calculateHeight();

        // Initial data load - check if menus are already loaded from basemenus
        const sidemenus = StateManager.get('sidemenus');
        if (sidemenus && sidemenus.length > 0) {
            // Menus already loaded from basemenus, no need to show loading
            loading = false;
        } else {
            loading = true;
        }
        renderContent();

        // Trigger sidebar mounted event
        $(document).trigger('sidebar:mounted');
    }

    /**
     * Set base menu position
     */
    function setBaseMenuPosition(entries, position) {
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
    function calculateHeight() {
        const height = $('#in_survey_common').height();
        if (height) {
            StateManager.commit('changeSideBarHeight', height);
        }
    }

    /**
     * Get current sidebar width (returns numeric value without 'px')
     */
    function getSideBarWidth() {
        const width = StateManager.getComputed('isCollapsed') ? 98 : sideBarWidth;
        // Ensure we always return a number by parsing if needed
        return typeof width === 'string' ? parseInt(width) : width;
    }

    /**
     * Calculate sidebar menu height
     */
    function calculateSideBarMenuHeight() {
        const currentSideBar = StateManager.get('sideBarHeight');
        return LS.ld.min([currentSideBar, Math.floor(screen.height * 2)]) + 'px';
    }

    /**
     * Toggle collapse state
     */
    function toggleCollapse() {
        const isCollapsed = StateManager.get('isCollapsed');
        StateManager.commit('changeIsCollapsed', !isCollapsed);

        if (StateManager.getComputed('isCollapsed')) {
            sideBarWidth = 98;
        } else {
            const savedWidth = StateManager.get('sidebarwidth');
            sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
        }

        render();
    }

    /**
     * Toggle small screen hidden state
     */
    function toggleSmallScreenHide() {
        smallScreenHidden = !smallScreenHidden;
        render();
    }

    /**
     * Change current tab
     */
    function changeCurrentTab(tab) {
        // Normalize tab name - 'structure' is alias for 'questiontree'
        if (tab === 'structure') {
            tab = 'questiontree';
        }
        // Only allow valid tab values
        if (tab !== 'settings' && tab !== 'questiontree') {
            tab = 'settings';
        }

        StateManager.commit('changeCurrentTab', tab);
        render();
    }

    /**
     * Handle mouse down for resize
     */
    function handleMouseDown(e) {
        if (UIHelpers.useMobileView()) {
            StateManager.commit('changeIsCollapsed', false);
            smallScreenHidden = !smallScreenHidden;
            render();
            return;
        }

        isMouseDown = !StateManager.getComputed('isCollapsed');
        $('#sidebar').removeClass('transition-animate-width');
        $('#pjax-content').removeClass('transition-animate-width');
    }

    /**
     * Handle mouse up for resize
     */
    function handleMouseUp(e) {
        if (isMouseDown) {
            isMouseDown = false;
            const widthNum = typeof sideBarWidth === 'string' ? parseInt(sideBarWidth) : sideBarWidth;
            if (widthNum < 250 && !StateManager.getComputed('isCollapsed')) {
                toggleCollapse();
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
    function handleMouseLeave(e) {
        if (isMouseDown) {
            isMouseDownTimeOut = setTimeout(function() {
                handleMouseUp(e);
            }, 1000);
        }
    }

    /**
     * Handle mouse move for resize
     */
    function handleMouseMove(e) {
        if (!isMouseDown) return;

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
            sideBarWidth = (window.innerWidth - e.pageX) - 8;
        } else {
            if (e.clientX > screen.width / 2) {
                StateManager.commit('maxSideBarWidth', true);
                return;
            }
            sideBarWidth = e.pageX - 4;
        }

        StateManager.commit('changeSidebarwidth', sideBarWidth);
        StateManager.commit('maxSideBarWidth', false);

        window.clearTimeout(isMouseDownTimeOut);
        isMouseDownTimeOut = null;

        // Update sidebar width in real-time (sideBarWidth is a number, add px)
        $('#sidebar').css('width', sideBarWidth + 'px');
    }

    /**
     * Control active link highlighting
     */
    function controlActiveLink() {
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
    function handleQuestionGroupOrderChange() {
        showLoader = true;
        render();

        const questiongroups = StateManager.get('questiongroups');
        const surveyid = StateManager.get('surveyid');

        Actions.updateQuestionGroupOrder(questiongroups, surveyid)
            .then(function() {
                return Actions.getQuestions();
            })
            .then(function() {
                showLoader = false;
                render();
            })
            .catch(function(error) {
                console.ls.error('questiongroups updating error!', error);
                Actions.getQuestions().then(function() {
                    showLoader = false;
                    render();
                });
            });
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Window resize
        window.addEventListener('resize', LS.ld.debounce(calculateHeight, 300));

        // Body mouse move for resize
        $('body').on('mousemove', handleMouseMove);

        // Custom events
        $(document).on('vue-sidemenu-update-link', controlActiveLink);

        $(document).on('vue-reload-remote', function() {
            Actions.getQuestions();
            Actions.collectMenus();
            StateManager.commit('newToggleKey');
        });

        $(document).on('vue-redraw', function() {
            Actions.getQuestions();
            Actions.collectMenus();
        });

        $(document).on('pjax:send', function() {
            if (UIHelpers.useMobileView() && smallScreenHidden) {
                smallScreenHidden = false;
                render();
            }
        });

        $(document).on('pjax:refresh', controlActiveLink);

        // EventBus equivalent for updateSideBar
        $(document).on('updateSideBar', function(e, payload) {
            loading = true;
            renderContent();

            const promises = [Promise.resolve()];

            if (payload && payload.updateQuestions) {
                promises.push(Actions.getQuestions());
            }
            if (payload && payload.collectMenus) {
                promises.push(Actions.collectMenus());
            }
            if (payload && payload.activeMenuIndex) {
                controlActiveLink();
            }

            Promise.all(promises)
                .catch(function(errors) {
                    console.ls.error(errors);
                })
                .finally(function() {
                    loading = false;
                    renderContent();
                });
        });
    }

    /**
     * Render the sidebar HTML
     */
    function render() {
        if (!container) return;

        const isCollapsed = StateManager.getComputed('isCollapsed');
        const currentTab = StateManager.get('currentTab');
        const isRTL = StateManager.getComputed('isRTL');
        const inSurveyViewHeight = StateManager.get('inSurveyViewHeight');
        const currentSidebarWidth = getSideBarWidth();

        let classes = 'd-flex col-lg-4 ls-ba position-relative transition-animate-width';
        if (smallScreenHidden) {
            classes += ' toggled';
        }

        const showMainContent = (UIHelpers.useMobileView() && smallScreenHidden) || !UIHelpers.useMobileView();
        const showPlaceholder = UIHelpers.useMobileView() && smallScreenHidden;
        const showResizeOverlay = isMouseDown;

        let html = '<div id="sidebar" class="' + classes + '" style="width: ' + currentSidebarWidth + 'px; max-height: ' + inSurveyViewHeight + 'px; display: flex;">';

        if (showMainContent) {
            // Loader overlay
            if (showLoader) {
                html += '<div class="sidebar_loader" style="width: ' + getSideBarWidth() + 'px; height: ' + $('#sidebar').height() + 'px;">' +
                    '<div class="ls-flex ls-flex-column fill align-content-center align-items-center">' +
                        '<i class="ri-loader-2-fill remix-2x remix-spin"></i>' +
                    '</div>' +
                '</div>';
            }

            html += '<div class="col-12 mainContentContainer">';
            html += '<div class="mainMenu col-12 position-relative">';

            // Sidebar state toggle (tabs)
            html += renderStateToggle(isCollapsed, currentTab, isRTL);

            // Side menu content
            html += '<div id="sidemenu-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'settings' ? 'block' : 'none') + '; min-height: ' + calculateSideBarMenuHeight() + ';"></div>';

            // Question explorer content
            html += '<div id="questionexplorer-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'questiontree' ? 'block' : 'none') + '; min-height: ' + calculateSideBarMenuHeight() + ';"></div>';

            // Quick menu (collapsed state)
            html += '<div id="quickmenu-container" style="display: ' + (isCollapsed ? 'block' : 'none') + ';"></div>';

            // Resize handle
            if ((UIHelpers.useMobileView() && !smallScreenHidden) || !UIHelpers.useMobileView()) {
                html += '<div class="resize-handle ls-flex-column" style="height: ' + calculateSideBarMenuHeight() + ';">';
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

        container.innerHTML = html;

        // Bind internal events after render
        bindInternalEvents();

        // Render sub-components
        renderContent();
    }

    /**
     * Render state toggle (tabs)
     */
    function renderStateToggle(isCollapsed, currentTab, isRTL) {
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
    function renderContent() {
        const sidemenuContainer = document.getElementById('sidemenu-container');
        const questionExplorerContainer = document.getElementById('questionexplorer-container');
        const quickmenuContainer = document.getElementById('quickmenu-container');

        if (sidemenuContainer) {
            SideMenu.render(sidemenuContainer, loading);
        }

        if (questionExplorerContainer) {
            QuestionExplorer.render(questionExplorerContainer, loading, handleQuestionGroupOrderChange);
        }

        if (quickmenuContainer) {
            QuickMenu.render(quickmenuContainer, loading);
        }

        // Re-initialize tooltips
        UIHelpers.redoTooltips();
    }

    /**
     * Bind internal events after render
     */
    function bindInternalEvents() {
        // Tab switching
        $(container).off('click', '.sidebar-tab-link').on('click', '.sidebar-tab-link', function(e) {
            e.preventDefault();
            const tab = $(this).data('tab');
            changeCurrentTab(tab);
        });

        // Expand button (collapsed state)
        $(container).off('click', '.expand-sidebar-btn').on('click', '.expand-sidebar-btn', function(e) {
            e.preventDefault();
            toggleCollapse();
        });

        // Resize handle
        $(container).off('mousedown', '.resize-btn').on('mousedown', '.resize-btn', handleMouseDown);
        $(container).off('mouseup').on('mouseup', handleMouseUp);
        $(container).off('mouseleave', '#sidebar').on('mouseleave', '#sidebar', handleMouseLeave);

        // Placeholder click (mobile)
        $(container).off('click', '.scoped-placeholder-greyed-area').on('click', '.scoped-placeholder-greyed-area', toggleSmallScreenHide);
    }

    /**
     * Update sidebar (called externally)
     */
    function update(options) {
        options = options || {};

        loading = true;
        renderContent();

        const promises = [];

        if (options.updateQuestions) {
            promises.push(Actions.getQuestions());
        }
        if (options.collectMenus) {
            promises.push(Actions.collectMenus());
        }

        Promise.all(promises)
            .then(function() {
                if (options.activeMenuIndex) {
                    controlActiveLink();
                }
            })
            .catch(function(error) {
                console.ls.error(error);
            })
            .finally(function() {
                loading = false;
                renderContent();
            });
    }

    return {
        init: init,
        render: render,
        update: update,
        toggleCollapse: toggleCollapse,
        controlActiveLink: controlActiveLink
    };
})();

export default Sidebar;
