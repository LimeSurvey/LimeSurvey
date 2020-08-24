import ajax from '../mixins/runAjax.js';
import _ from 'lodash';
import {LOG} from '../mixins/logSystem.js'

export default {
    updateObjects: (context, newObjectBlock) => {
    },
    loadData: (context) => {
        return new Promise((resolve,reject) => {
            ajax.methods.$_get(LS.createUrl('surveyAdministration/getDataSecTextSettings', {
               sid: window.DataSecTextEditData.sid
            }))
            .then((result) => {
                    LOG.log('AjaxCall: ',result);
                    context.commit('setShowsurveypolicynotice', parseInt(result.data.showsurveypolicynotice) );
                    context.commit('setDataseclabel', result.data.textdata.dataseclabel);
                    context.commit('setDatasecmessage', result.data.textdata.datasecmessage );
                    context.commit('setDatasecerror', result.data.textdata.datasecerror );
                    
                    context.commit('setPermissions', result.data.permissions );
                    context.commit('setLanguages', result.data.languages);
                    context.commit('setActiveLanguage', _.keys(result.data.languages)[0]);
                    context.commit('toggleVisible', true);
                    resolve();
                }
            )
            .catch((error) => {
                reject(error);
            });
        });
    },
    saveData: (context) => {
        context.commit('toggleVisible', false);
        let transferObject = _.merge({
            'changes': {
            showsurveypolicynotice: context.state.showsurveypolicynotice,
            dataseclabel: context.state.dataseclabel,
            datasecmessage: context.state.datasecmessage,
            datasecerror: context.state.datasecerror,
        }}, window.LS.data.csrfTokenData);

        LOG.log('OBJECT TO BE TRANSFERRED: ', {'dataSecTextData': transferObject});
        return ajax.methods.$_post(LS.createUrl('surveyAdministration/saveDataSecTextData' ,{'sid' : window.DataSecTextEditData.sid}), transferObject);
    }
};
