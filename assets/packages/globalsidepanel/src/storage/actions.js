import ajax from '../mixins/runAjax';
import {LOG} from '../mixins/logSystem';

export default {
    updatePjax() {
        $(document).trigger('pjax:refresh');           
    },
    getMenus(context) {
        return new Promise((resolve, reject) => {
            ajax.methods.get(window.GlobalSideMenuData.getUrl).then(
                result => {
                    LOG.log("menues", result);
                    context.commit('setMenu', result.data);
                    context.dispatch('updatePjax');
                    resolve();
                },reject);
            }
        );
    },
};