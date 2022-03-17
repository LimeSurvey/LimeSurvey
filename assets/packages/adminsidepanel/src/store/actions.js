import ajax from '../mixins/runAjax';
import {LOG} from '../mixins/logSystem';

export default {
    updatePjax({commit}) {
        $(document).trigger('pjax:refresh');           
        commit('newToggleKey');
    },
    getSidemenus(context) {
        return new Promise((resolve, reject) => {
            ajax.methods.get(window.SideMenuData.getMenuUrl, { position: "side" }).then(
                result => {
                    LOG.log("sidemenues", result);
                    const newSidemenus = LS.ld.orderBy(
                        result.data.menues,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    context.commit('updateSidemenus', newSidemenus);
                    context.dispatch('updatePjax');
                    resolve();
                })
                .catch((error) => {reject(error)});
            }
        );
    },
    getCollapsedmenus(context) {
        return new Promise((resolve, reject) => {
            ajax.methods.get(window.SideMenuData.getMenuUrl, { position: "collapsed" }).then(
                result => {
                    LOG.log("quickmenu", result);
                    const newCollapsedmenus = LS.ld.orderBy(
                        result.data.menues,
                        a => {
                            return parseInt(a.order || 999999);
                        },
                        ["desc"]
                    );
                    context.commit('updateCollapsedmenus', newCollapsedmenus);
                    context.dispatch('updatePjax');
                    resolve();
                })
                .catch((error) => {reject(error)});
            }
        );
    },
    getQuestions(context) {
        return new Promise((resolve, reject) => {
            ajax.methods.get(window.SideMenuData.getQuestionsUrl).then(result => {
                LOG.log("Questions", result);
                const newQuestiongroups = result.data.groups;
                context.commit("updateQuestiongroups", newQuestiongroups);
                context.dispatch('updatePjax');
                resolve();
            })
            .catch((error) => {reject(error)});
        });
    },
    collectMenus(context) {
        return Promise.all([
            context.dispatch('getSidemenus'),
            context.dispatch('getCollapsedmenus'),
        ]);
    },
    unlockLockOrganizer(context) {
        //context.commit("setAllowOrganizer", context.state.allowOrganizer);
        return new Promise((resolve, reject) => {
            ajax.methods.post(
                window.SideMenuData.unlockLockOrganizerUrl,
                { 
                    setting : 'lock_organizer',
                    newValue :  context.state.allowOrganizer ? '0' : '1'
                }
            ).then(
                result => {
                    LOG.log('setUsersettingLog', result);
                    context.commit("setAllowOrganizer", parseInt(result.data.result));
            }).catch((error) => {reject(error)});}
        );
    },
    changeCurrentTab(context, payload) {
        context.commit("changeCurrentTab", payload);
        context.dispatch('collectMenus');
        context.dispatch('getQuestions');
    }
}
