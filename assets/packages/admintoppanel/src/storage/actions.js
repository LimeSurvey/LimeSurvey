import ajax from '../mixins/runAjax.js';
import {
    LOG
} from '../mixins/logSystem.js'

export default {
    getTopBarButtonsQuestion: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/questioneditor/sa/getQuestionTopbar', {
                    qid: context.state.qid
                }))
                .then((data) => {
                    context.commit('clean');
                    context.commit('setTopBarRight', data.data.topbar.alignment.right ? data.data.topbar.alignment.right.buttons : []);
                    context.commit('setTopBarLeft', data.data.topbar.alignment.left ? data.data.topbar.alignment.left.buttons : []);
                    context.commit('setTopBarExtendedRight', data.data.topbarextended.alignment.right ? data.data.topbarextended.alignment.right.buttons : []);
                    context.commit('setTopBarExtendedLeft', data.data.topbarextended.alignment.left ? data.data.topbarextended.alignment.left.buttons : []);
                    context.commit('setPermissions', data.data.permissions);

                    resolve(data.data.topbar);
                })
                .catch((error) => {
                    reject({
                        error: error
                    });
                })
        })
    },

    getTopBarButtonsGroup: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/questiongroups/sa/getQuestionGroupTopBar', {
                    gid: context.state.gid
                }))
                .then((data) => {
                    context.commit('clean');
                    context.commit('setTopBarRight', data.data.topbar.alignment.right ? data.data.topbar.alignment.right.buttons : []);
                    context.commit('setTopBarLeft', data.data.topbar.alignment.left ? data.data.topbar.alignment.left.buttons : []);
                    context.commit('setTopBarExtendedRight', data.data.topbarextended.alignment.right ? data.data.topbarextended.alignment.right.buttons : []);
                    context.commit('setTopBarExtendedLeft', data.data.topbarextended.alignment.left ? data.data.topbarextended.alignment.left.buttons : []);
                    context.commit('setPermissions', data.data.permissions);

                    resolve(data.data.topbar);
                })
                .catch((error) => {
                    reject({
                        error: error
                    });
                })
        })
    },

    getTopBarButtonsSurvey: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/survey/sa/getSurveyTopBar', {
                    sid: context.state.sid,
                    saveButton: context.state.showSaveButton
                }))
                .then((data) => {
                    context.commit('clean');
                    context.commit('setTopBarRight', data.data.topbar.alignment.right.buttons);
                    context.commit('setTopBarLeft', data.data.topbar.alignment.left.buttons);
                    context.commit('setTopBarExtendedRight', []);
                    context.commit('setTopBarExtendedLeft', []);
                    context.commit('setPermissions', data.data.permissions);

                    resolve(data.data.topbar);
                })
                .catch((error) => {
                    reject({
                        error: error
                    });
                })
        })
    },

    getTopBarButtonsTokens: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/survey/sa/getTokenTopBar', {
                    sid: context.state.sid,
                    saveButton: context.state.showSaveButton
                }))
                .then((data) => {
                    context.commit('clean');
                    context.commit('setTopBarRight', data.data.topbar.alignment.right.buttons);
                    context.commit('setTopBarLeft', data.data.topbar.alignment.left.buttons);
                    context.commit('setTopBarExtendedRight', []);
                    context.commit('setTopBarExtendedLeft', []);
                    context.commit('setPermissions', data.data.permissions);

                    resolve(data.data.topbar);
                })
                .catch((error) => {
                    reject({
                        error: error
                    });
                })
        })
    },


    getCustomTopbarContent: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/survey/sa/getAjaxMenuArray', {
                    position: 'top',
                    sid: context.state.sid,
                    saveButton: context.state.showSaveButton
                }))
                .then((data) => {
                    const topbarLeft = context.state.topbar_left_buttons;
                    LS.ld.forEach(data, ()=> {

                    });
                    context.commit('setTopBarLeft', topbarLeft);
                    resolve(data);
                })
                .catch((error) => {
                    reject({
                        error: error
                    });
                })
        })
    },

};
