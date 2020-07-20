import ajax from '../mixins/runAjax.js';

export default {
    getTopBarButtonsQuestion: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('questionEditor/getQuestionTopbar', {
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
                gid: context.state.gid || LS.reparsedParameters().combined.gid || 0,
                qid: context.state.qid || LS.reparsedParameters().combined.qid || 0
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
                .catch((error) => {reject(error);});
        });
    },

    getTopBarButtonsGroup: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/questiongroups/sa/getQuestionGroupTopBar', {
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
                gid: context.state.gid || LS.reparsedParameters().combined.gid || 0
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
                .catch((error) => {reject(error);});
        });
    },

    getTopBarButtonsSurvey: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/survey/sa/getSurveyTopBar', {
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
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
                .catch((error) => {reject(error);});
        });
    },

    getTopBarButtonsTokens: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/survey/sa/getTokenTopBar', {
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
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
                .catch((error) => {reject(error);});
        });
    },

    getTopBarButtonsResponses: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/responses/sa/getResponsesTopBarData', {
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
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
                    reject(error);
                });
        });
    },

    getTopBarButtonsConditions: (context) => {
        context.commit('clean');
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/conditions/sa/getConditionsTopBarData', {
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
                gid: context.state.gid || LS.reparsedParameters().combined.gid || 0,
                qid: context.state.qid || LS.reparsedParameters().combined.qid || 0
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
                .catch((error) => {reject(error);});
        });
    },


    getCustomTopbarContent: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(LS.createUrl('admin/survey/sa/getAjaxMenuArray', {
                position: 'top',
                sid: context.state.sid || LS.reparsedParameters().combined.sid,
                saveButton: context.state.showSaveButton
            }))
                .then((data) => {
                    const topbarLeft = context.state.topbar_left_buttons;
                    LS.ld.forEach(data, ()=> {

                    });
                    context.commit('setTopBarLeft', topbarLeft);
                    resolve(data);
                })
                .catch((error) => {reject(error);})
        });
    },

};
