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
    post(uri, data) {
      return this._runAjax(uri, data, 'post');
    },
    get(uri, data) {
      return this._runAjax(uri, data, 'get');
    },
    delete(uri, data) {
      return this._runAjax(uri, data, 'delete');
    },
    put(uri, data) {
      return this._runAjax(uri, data, 'put');
    }
  }
};