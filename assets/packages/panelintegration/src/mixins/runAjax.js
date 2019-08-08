export default {
  methods: {
    _runAjax(uri, data = {}, method = 'get') {
      return new Promise((resolve, reject) => {
        if ($ == undefined) {
          reject('JQUERY NOT AVAILABLE!');
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
            reject({
              success: false,
              error: error,
              transferStatus: status,
              xhr: xhr
            });
          }
        });
      });
    },
    $_post(uri, data) {
      return this._runAjax(uri, data, 'post');
    },
    $_get(uri, data) {
      return this._runAjax(uri, data, 'get');
    },
    $_delete(uri, data) {
      return this._runAjax(uri, data, 'delete');
    },
    $_put(uri, data) {
      return this._runAjax(uri, data, 'put');
    }
  }
};