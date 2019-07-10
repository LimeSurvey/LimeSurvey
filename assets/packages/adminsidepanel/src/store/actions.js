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
                },reject);
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
                },reject);
            }
        );
    },
    // getTopmenus(context) {
    //     return ajax.methods.get(window.SideMenuData.getMenuUrl, { position: "top" }).then(
    //         result => {
    //             LOG.log("topmenus", result);
    //             const newTopmenus = LS.ld.orderBy(
    //                 result.data.menues,
    //                 a => {
    //                     return parseInt(a.order || 999999);
    //                 },
    //                 ["desc"]
    //             );
    //             context.commit('updateTopmenus', newTopmenus);
    //             context.dispatch('updatePjax');
    //         }
    //     );
    // },
    // getBottommenus(context) {
    //     return ajax.methods.get(window.SideMenuData.getMenuUrl, { position: "bottom" }).then(
    //         result => {
    //             LOG.log("bottommenus", result);
    //             const newBottommenus = LS.ld.orderBy(
    //                 result.data.menues,
    //                 a => {
    //                     return parseInt(a.order || 999999);
    //                 },
    //                 ["desc"]
    //             );
    //             context.commit('updateBottommenus', newBottommenus);
    //             context.dispatch('updatePjax');
    //         }
    //     );
    // },
    getQuestions(context) {
        return new Promise((resolve, reject) => {
            ajax.methods.get(window.SideMenuData.getQuestionsUrl).then(result => {
                LOG.log("Questions", result);
                const newQuestiongroups = result.data.groups;
                context.commit("updateQuestiongroups", newQuestiongroups);
                context.dispatch('updatePjax');
                resolve();
            },reject);
        });
    },
    collectMenus(context) {
        return Promise.all([
            context.dispatch('getSidemenus'),
            context.dispatch('getCollapsedmenus'),
            // context.dispatch('getTopmenus'),
            // context.dispatch('getBottommenus')
        ]);
    }
}