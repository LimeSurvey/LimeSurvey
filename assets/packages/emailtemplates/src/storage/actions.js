import ajax from '../mixins/runAjax.js';
import _ from 'lodash';
import {LOG} from '../mixins/logSystem.js'

export default {
    getDataSet: (context) => {
        return new Promise((resolve, reject) => {
            const requestUrl = LS.createUrl('admin/emailtemplates', {'sa': 'getEmailTemplateData', 'iSurveyId': window.EmailTemplateData.surveyid});
            ajax.methods.$_get(requestUrl)
            .then((result) => {
                    LOG.log('Getting Data', result);
                    
                    context.commit('setUseHtml', result.data.useHtml);
                    context.commit('setTemplateTypes', result.data.templateTypes);
                    context.commit('setCurrentTemplateType', _.keys(result.data.templateTypes)[0]);
                    context.commit('setLanguages', result.data.languages);
                    context.commit('setActiveLanguage', _.keys(result.data.languages)[0]);

                    context.commit('setTemplateTypeContents', result.data.templateTypeContents);
                    context.commit('setPermissions', result.data.permissions);
                    resolve();
                }
            )
            .catch((error) => {
                reject(error);
            });
        });
    },
    saveData: (context) => {
        let transferObject = _.merge({changes: context.state.templateTypeContents}, window.LS.data.csrfTokenData);

        LOG.log('OBJECT TO BE TRANSFERRED: ', {'postObject': transferObject});
        const requestUrl = LS.createUrl('admin/emailtemplates', {'sa': 'saveEmailTemplateData', 'iSurveyId': window.EmailTemplateData.surveyid});
        return ajax.methods.$_post(requestUrl, transferObject)
    }
};
