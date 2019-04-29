import ajax from '../mixins/runAjax.js';
import _ from 'lodash';
import {LOG} from '../mixins/logSystem.js'

export default {
    getDataSet: (context) => {
        ajax.methods.$_get(
            window.TextEditData.connectorBaseUrl+'/getCurrentEditorValues'
        ).then((result) => {
            LOG.log('Getting Data', result);
            context.dispatch('updateObjects', result.data.textdata);
            context.commit('setLanguages', result.data.languages);
            context.commit('setActiveLanguage', _.keys(result.data.languages)[0]);
        });
    },
    updateObjects: (context, newObjectBlock) => {
        LOG.log('UPDATING AJAX', newObjectBlock);
        context.commit('setSurveyTitle', newObjectBlock.surveyTitle);
        context.commit('setWelcome', newObjectBlock.welcome);
        context.commit('setDescription', newObjectBlock.description);
        context.commit('setEndText', newObjectBlock.endText);
        context.commit('setEndUrl', newObjectBlock.endUrl);
        context.commit('setEndUrlDescription', newObjectBlock.endUrlDescription);
        context.commit('setDateFormat', newObjectBlock.dateFormat);
        context.commit('setDecimalDivider', newObjectBlock.decimalDivider);
    },
    getDateFormatOptions: (context) => {
        ajax.methods.$_get(
            window.TextEditData.connectorBaseUrl+'/getDateFormatOptions'
        ).then((result) => {
            context.commit('setDateFormatOptions', result.data);
        });
    },
    saveData: (context) => {
        let postObject = {};
        _.each(_.keys(context.state.languages), (lngKey) => {
            postObject[lngKey] = {
                surveyTitle: context.state.surveyTitle[lngKey],
                welcome: context.state.welcome[lngKey],
                description: context.state.description[lngKey],
                endText: context.state.endText[lngKey],
                endUrl: context.state.endUrl[lngKey],
                endUrlDescription: context.state.endUrlDescription[lngKey],
                dateFormat: context.state.dateFormat[lngKey],
                decimalDivider: context.state.decimalDivider[lngKey],
            }
        });

        let transferObject = _.merge({changes: postObject}, window.LS.data.csrfTokenData);

        LOG.log('OBJECT TO BE TRANSFERRED: ', {'postObject': transferObject});
        return ajax.methods.$_post(window.TextEditData.connectorBaseUrl+'/saveTextData', transferObject)
    }
};