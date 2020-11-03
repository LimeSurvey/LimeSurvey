export default {
  methods: {
    __runAjax(uri, data = {}, method = 'get', dataType = 'json') {
      const csrfObject = {};
      csrfObject[LS.data.csrfTokenName] = LS.data.csrfToken;
      const sendData = $.merge(data,csrfObject);
      return new Promise((resolve, reject) => {
        if ($ == undefined) {
          reject('JQUERY NOT AVAILABLE!');
        }
        $.ajax({
          url: uri,
          method: method || 'get',
          data: sendData,
          dataType,
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
    },
    $_post(uri, data) {
      return this.__runAjax(uri, data, 'post');
    },
    $_get(uri, data) {
      return this.__runAjax(uri, data, 'get');
    },
    $_load(uri, data, method='get') {
      return this.__runAjax(uri, data, method, "html");
    },
    $_delete(uri, data) {
      return this.__runAjax(uri, data, 'delete');
    },
    $_put(uri, data) {
      return this.__runAjax(uri, data, 'put');
    }
  }
};
