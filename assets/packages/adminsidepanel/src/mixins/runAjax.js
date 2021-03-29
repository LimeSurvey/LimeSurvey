export default {
  methods: {
    _runAjax: function(uri, data, method) {
    data =  data || {};
    method =  method || 'get';
      return new Promise(function(resolve, reject) {
        if ($ == undefined) {
          reject('JQUERY NOT AVAILABLE!');
        }
        $.ajax({
          url: uri,
          method: method || 'get',
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
    },
    post: function(uri, data) {
      return this._runAjax(uri, data, 'post');
    },
    get: function(uri, data) {
      return this._runAjax(uri, data, 'get');
    },
    delete: function(uri, data) {
      return this._runAjax(uri, data, 'delete');
    },
    put: function(uri, data) {
      return this._runAjax(uri, data, 'put');
    }
  }
};
