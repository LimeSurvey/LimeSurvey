/**
 * AdminSidePanel - Main entry point (vanilla JS)
 * Replaces Vue-based adminsidepanelmain.js
 */
import StateManager from './StateManager.js';
import { createDefaultState, createMutations, createGetters } from './stateConfig.js';
import Actions from './Actions.js';
import Sidebar from './components/Sidebar.js';

/**
 * Main AdminSidePanel factory function
 * @param {string|number} userid
 * @param {string|number} surveyid
 * @returns {Function}
 */
const Lsadminsidepanel = function(userid, surveyid) {
    'use strict';

    const panelNameSpace = {};

    /**
     * Apply survey ID to state
     */
    function applySurveyId() {
        if (surveyid !== 0 && surveyid !== '0' && surveyid !== 'newSurvey') {
            StateManager.commit('updateSurveyId', surveyid);
        }
    }

    /**
     * Control window size and adjust layout
     */
    function controlWindowSize() {
        const adminmenuHeight = $('body').find('nav').first().height() || 0;
        const footerHeight = $('body').find('footer').last().height() || 0;
        const menuHeight = $('.menubar').outerHeight() || 0;
        const inSurveyOffset = adminmenuHeight + footerHeight + menuHeight + 25;
        const windowHeight = window.innerHeight;
        const inSurveyViewHeight = windowHeight - inSurveyOffset;

        const sidebarWidth = $('#sidebar').width() || 0;
        const containerWidth = $('#vue-apps-main-container').width() || 1;
        const bodyWidth = (1 - (parseInt(sidebarWidth) / containerWidth)) * 100;
        const collapsedBodyWidth = (1 - (parseInt('98px') / containerWidth)) * 100;
        const inSurveyViewWidth = Math.floor($('#sidebar').data('collapsed') ? bodyWidth : collapsedBodyWidth) + '%';

        panelNameSpace.surveyViewHeight = inSurveyViewHeight;
        panelNameSpace.surveyViewWidth = inSurveyViewWidth;

        $('#fullbody-container').css({
            'max-width': inSurveyViewWidth,
            'overflow-x': 'auto'
        });
    }

    /**
     * Create the side menu
     */
    function createSideMenu() {
        const containerEl = document.getElementById('vue-sidebar-container');
        if (!containerEl) return null;

        // Initialize state manager with unified API
        StateManager.init({
            storagePrefix: 'limesurveyadminsidepanel',
            userid: userid,
            surveyid: surveyid,
            defaultState: createDefaultState(userid, surveyid),
            mutations: createMutations(StateManager),
            getters: createGetters(StateManager)
        });

        // Apply survey ID
        applySurveyId();

        // Set max height
        const maxHeight = $('#in_survey_common').height() - 35 || 400;
        StateManager.commit('changeMaxHeight', maxHeight);

        // Set allow organizer - default to unlocked (1) unless explicitly locked
        if (window.SideMenuData && window.SideMenuData.allowOrganizer !== undefined) {
            // Only lock if explicitly set to 0, otherwise keep unlocked
            StateManager.commit('setAllowOrganizer', window.SideMenuData.allowOrganizer === 0 ? 0 : 1);
        } else {
            // No server value, default to unlocked
            StateManager.commit('setAllowOrganizer', 1);
        }

        // Initialize sidebar component (now as a class)
        const sidebar = new Sidebar();
        sidebar.init(containerEl);

        // Bind Vue-style events
        $(document).on('vue-redraw', function() {
            StateManager.commit('newToggleKey');
        });

        $(document).trigger('vue-reload-remote');

        return sidebar;
    }

    /**
     * Apply Pjax methods for AJAX navigation
     */
    function applyPjaxMethods() {
        panelNameSpace.reloadcounter = 5;

        $(document)
            .off('pjax:send.panelloading')
            .on('pjax:send.panelloading', function() {
                $('<div id="pjaxClickInhibitor"></div>').appendTo('body');
                $('.ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-draggable.ui-resizable').remove();
                $('#pjax-file-load-container')
                    .find('div')
                    .css({
                        width: '20%',
                        display: 'block'
                    });
                LS.adminsidepanel.reloadcounter--;
            });

        $(document)
            .off('pjax:error.panelloading')
            .on('pjax:error.panelloading', function(event) {
                if (console.ls && console.ls.log) {
                    console.ls.log(event);
                }
            });

        $(document)
            .off('pjax:complete.panelloading')
            .on('pjax:complete.panelloading', function() {
                if (LS.adminsidepanel.reloadcounter === 0) {
                    location.reload();
                }
            });

        $(document)
            .off('pjax:scriptcomplete.panelloading')
            .on('pjax:scriptcomplete.panelloading', function() {
                $('#pjax-file-load-container')
                    .find('div')
                    .css('width', '100%');

                $('#pjaxClickInhibitor').fadeOut(400, function() {
                    $(this).remove();
                });

                $(document).trigger('vue-resize-height');
                $(document).trigger('vue-reload-remote');

                setTimeout(function() {
                    $('#pjax-file-load-container')
                        .find('div')
                        .css({
                            width: '0%',
                            display: 'none'
                        });
                }, 2200);
            });
    }

    /**
     * Create panel appliance
     */
    function createPanelAppliance() {
        // Initialize singleton Pjax
        if (window.singletonPjax) {
            window.singletonPjax();
        }

        // Create side menu
        if (document.getElementById('vue-sidebar-container')) {
            panelNameSpace.sidemenu = createSideMenu();
        }

        // Pagination click handler
        $(document).on('click', 'ul.pagination>li>a', function() {
            $(document).trigger('pjax:refresh');
        });

        // Window resize handling
        controlWindowSize();
        window.addEventListener('resize', LS.ld.debounce(controlWindowSize, 300));
        $(document).on('vue-resize-height', LS.ld.debounce(controlWindowSize, 300));

        // Apply Pjax methods
        applyPjaxMethods();
    }

    // Add to LS admin namespace
    if (LS && LS.adminCore && LS.adminCore.addToNamespace) {
        LS.adminCore.addToNamespace(panelNameSpace, 'adminsidepanel');
    }

    return createPanelAppliance;
};

// Document ready handler
$(document).ready(function() {
    let surveyid = 'newSurvey';

    if (window.LS !== undefined) {
        surveyid = window.LS.parameters.$GET.surveyid || window.LS.parameters.keyValuePairs.surveyid;
    }

    if (window.SideMenuData) {
        surveyid = window.SideMenuData.surveyid;
    }

    const userid = (window.LS && window.LS.globalUserId) ? window.LS.globalUserId : null;
    window.adminsidepanel = window.adminsidepanel || Lsadminsidepanel(userid, surveyid);

    window.adminsidepanel();
});

export default Lsadminsidepanel;
