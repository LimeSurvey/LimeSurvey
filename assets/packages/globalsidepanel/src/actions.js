/**
 * Actions for Global Sidebar Panel
 * Handles async operations
 */

import ConsoleShim from '../../meta/lib/ConsoleShim.js';

const LOG = new ConsoleShim('globalsidepanel');

class Actions {
    constructor(StateManager) {
        this.StateManager = StateManager;
    }

    /**
     * Run AJAX request
     */
    _runAjax(uri, data = {}, method = 'get') {
        return new Promise((resolve, reject) => {
            if (typeof $ === 'undefined') {
                reject('JQUERY NOT AVAILABLE!');
                return;
            }
            $.ajax({
                url: uri,
                method: method || 'get',
                data: data,
                dataType: 'json',
                success: (response, status, xhr) => {
                    resolve({
                        success: true,
                        data: response,
                        transferStatus: status,
                        xhr: xhr
                    });
                },
                error: (xhr, status, error) => {
                    const responseData = xhr.responseJSON || xhr.responseText;
                    reject({
                        success: false,
                        error: error,
                        data: responseData,
                        transferStatus: status,
                        xhr: xhr
                    });
                }
            });
        });
    }

    get(uri, data) {
        return this._runAjax(uri, data, 'get');
    }

    post(uri, data) {
        return this._runAjax(uri, data, 'post');
    }

    updatePjax() {
        $(document).trigger('pjax:refresh');
    }

    getMenus() {
        return new Promise((resolve, reject) => {
            this.get(window.GlobalSideMenuData.getUrl).then(
                result => {
                    LOG.log("menues", result);
                    this.StateManager.commit('setMenu', result.data);
                    this.updatePjax();
                    resolve();
                },
                reject
            );
        });
    }
}

export default Actions;
