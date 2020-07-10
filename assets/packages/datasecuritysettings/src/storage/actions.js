import ajax from '../mixins/runAjax.js';
import _ from 'lodash';
import {LOG} from '../mixins/logSystem.js'

export default {
    updateObjects: (context, newObjectBlock) => {
    },
    loadData: (context) => {
        return new Promise((resolve,reject) => {
            const subAction = window.DataSecTextEditData.connectorBaseUrl.slice(-1) == '=' ? 'getDataSecTextSettings' : '/getDataSecTextSettings';
            ajax.methods.$_get(
                window.DataSecTextEditData.connectorBaseUrl+subAction, {}
            )
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
        const subAction = window.DataSecTextEditData.connectorBaseUrl.slice(-1) == '=' ? 'saveDataSecTextData' : '/saveDataSecTextData';
        return ajax.methods.$_post(window.DataSecTextEditData.connectorBaseUrl+subAction, transferObject)
    }
};
