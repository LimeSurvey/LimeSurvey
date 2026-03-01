/**
 * AjaxHelper - Vanilla JS replacement for runAjax mixin
 * Provides Promise-based AJAX methods using jQuery
 */
const AjaxHelper = (function() {
    'use strict';

    /**
     * Core AJAX request method
     * @param {string} uri - Request URL
     * @param {Object} data - Request data
     * @param {string} method - HTTP method
     * @returns {Promise}
     */
    function _runAjax(uri, data, method) {
        data = data || {};
        method = method || 'get';

        return new Promise(function(resolve, reject) {
            if (typeof $ === 'undefined') {
                reject('JQUERY NOT AVAILABLE!');
                return;
            }

            $.ajax({
                url: uri,
                method: method,
                data: data,
                dataType: 'json',
                success: function(response, status, xhr) {
                    resolve({
                        success: true,
                        data: response,
                        transferStatus: status,
                        xhr: xhr
                    });
                },
                error: function(xhr, status, error) {
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

    /**
     * POST request
     * @param {string} uri
     * @param {Object} data
     * @returns {Promise}
     */
    function post(uri, data) {
        return _runAjax(uri, data, 'post');
    }

    /**
     * GET request
     * @param {string} uri
     * @param {Object} data
     * @returns {Promise}
     */
    function get(uri, data) {
        return _runAjax(uri, data, 'get');
    }

    /**
     * DELETE request
     * @param {string} uri
     * @param {Object} data
     * @returns {Promise}
     */
    function deleteRequest(uri, data) {
        return _runAjax(uri, data, 'delete');
    }

    /**
     * PUT request
     * @param {string} uri
     * @param {Object} data
     * @returns {Promise}
     */
    function put(uri, data) {
        return _runAjax(uri, data, 'put');
    }

    return {
        post: post,
        get: get,
        delete: deleteRequest,
        put: put
    };
})();

export default AjaxHelper;
