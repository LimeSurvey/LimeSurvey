import ajax from '../mixins/runAjax.js';
import {LOG} from '../mixins/logSystem.js'

export default {
    getLabelSetData: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(window.LabelSetData.getDataUrl).then(
                (result) => {
                    context.commit('setLanguages', result.data.languages);
                    context.commit('setActiveLanguage', result.data.mainLanguage);
                    context.commit('setLabels', result.data.labels);
                    resolve();
                },
                (error) => {
                    LOG.error(error);
                    reject(error);
                }
            );
        });
    },
    saveLabelSetData: (context) => {
        if(context.state.inTransfer) {
            return Promise.resolve(false);
        }

        let transferObject = LS.ld.merge({
            'labelSetData': {
            languages: context.state.languages,
            labels: context.state.labels,
        }}, window.LS.data.csrfTokenData);

        LOG.log('OBJECT TO BE TRANSFERRED: ', {'labelSetData': transferObject});
        return new Promise((resolve, reject) => {
            context.commit('setInTransfer', true);
            ajax.methods.$_post(window.LabelSetData.setDataUrl, transferObject)
                .then(
                    (result) => {
                        context.commit('setInTransfer', false);
                        resolve(result);
                    },
                    reject
                )
        });
    },
    resetContentFromQuickEdit(context, payload) {},
    addToCurrentFromQuickEdit(context, payload) {},
};