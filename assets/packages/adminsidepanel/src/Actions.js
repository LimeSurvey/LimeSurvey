/**
 * Actions - Vanilla JS replacement for Vuex actions
 * Handles async operations and API calls
 */
import AjaxHelper from './AjaxHelper.js';
import StateManager from './StateManager.js';

const Actions = (function() {
    'use strict';

    /**
     * Logger utility
     */
    function log() {
        if (console.ls && console.ls.log) {
            console.ls.log.apply(console.ls, arguments);
        }
    }

    /**
     * Trigger pjax refresh and update toggle key
     */
    function updatePjax() {
        $(document).trigger('pjax:refresh');
        StateManager.commit('newToggleKey');
    }

    /**
     * Fetch side menus from server
     * @returns {Promise}
     */
    function getSidemenus() {
        return new Promise(function(resolve, reject) {
            AjaxHelper.get(window.SideMenuData.getMenuUrl, { position: 'side' })
                .then(function(result) {
                    log('sidemenues', result);
                    const newSidemenus = LS.ld.orderBy(
                        result.data.menues,
                        function(a) {
                            return parseInt(a.order || 999999);
                        },
                        ['desc']
                    );
                    StateManager.commit('updateSidemenus', newSidemenus);
                    updatePjax();
                    resolve(newSidemenus);
                })
                .catch(function(error) {
                    reject(error);
                });
        });
    }

    /**
     * Fetch collapsed/quick menus from server
     * @returns {Promise}
     */
    function getCollapsedmenus() {
        return new Promise(function(resolve, reject) {
            AjaxHelper.get(window.SideMenuData.getMenuUrl, { position: 'collapsed' })
                .then(function(result) {
                    log('quickmenu', result);
                    const newCollapsedmenus = LS.ld.orderBy(
                        result.data.menues,
                        function(a) {
                            return parseInt(a.order || 999999);
                        },
                        ['desc']
                    );
                    StateManager.commit('updateCollapsedmenus', newCollapsedmenus);
                    updatePjax();
                    resolve(newCollapsedmenus);
                })
                .catch(function(error) {
                    reject(error);
                });
        });
    }

    /**
     * Fetch questions from server
     * @returns {Promise}
     */
    function getQuestions() {
        return new Promise(function(resolve, reject) {
            AjaxHelper.get(window.SideMenuData.getQuestionsUrl)
                .then(function(result) {
                    log('Questions', result);
                    const newQuestiongroups = result.data.groups;
                    StateManager.commit('updateQuestiongroups', newQuestiongroups);
                    updatePjax();
                    resolve(newQuestiongroups);
                })
                .catch(function(error) {
                    reject(error);
                });
        });
    }

    /**
     * Collect both side menus and collapsed menus
     * @returns {Promise}
     */
    function collectMenus() {
        return Promise.all([
            getSidemenus(),
            getCollapsedmenus()
        ]);
    }

    /**
     * Toggle lock/unlock organizer setting
     * @returns {Promise}
     */
    function unlockLockOrganizer() {
        return new Promise(function(resolve, reject) {
            const newAllowOrganizer = StateManager.get('allowOrganizer') ? 0 : 1;
            const lockValue = newAllowOrganizer ? '0' : '1';
            AjaxHelper.post(
                window.SideMenuData.unlockLockOrganizerUrl,
                {
                    setting: 'lock_organizer',
                    newValue: lockValue
                }
            )
                .then(function(result) {
                    log('setUsersettingLog', result);
                    StateManager.commit('setAllowOrganizer', newAllowOrganizer);
                    resolve(result);
                })
                .catch(function(error) {
                    reject(error);
                });
        });
    }

    /**
     * Change current tab and reload data
     * @param {string} tab
     * @returns {Promise}
     */
    function changeCurrentTab(tab) {
        StateManager.commit('changeCurrentTab', tab);
        return Promise.all([
            collectMenus(),
            getQuestions()
        ]);
    }

    /**
     * Update question group order on server
     * @param {Array} questiongroups
     * @param {string} surveyid
     * @returns {Promise}
     */
    function updateQuestionGroupOrder(questiongroups, surveyid) {
        const onlyGroupsArray = LS.ld.map(questiongroups, function(questiongroup) {
            const questions = LS.ld.map(questiongroup.questions, function(question) {
                return {
                    qid: question.qid,
                    question: question.question,
                    gid: question.gid,
                    question_order: question.question_order
                };
            });
            return {
                gid: questiongroup.gid,
                group_name: questiongroup.group_name,
                group_order: questiongroup.group_order,
                questions: questions
            };
        });

        return AjaxHelper.post(window.SideMenuData.updateOrderLink, {
            grouparray: onlyGroupsArray,
            surveyid: surveyid
        });
    }

    return {
        updatePjax: updatePjax,
        getSidemenus: getSidemenus,
        getCollapsedmenus: getCollapsedmenus,
        getQuestions: getQuestions,
        collectMenus: collectMenus,
        unlockLockOrganizer: unlockLockOrganizer,
        changeCurrentTab: changeCurrentTab,
        updateQuestionGroupOrder: updateQuestionGroupOrder
    };
})();

export default Actions;
