export default function (route, params = {}) {

    let result = LS.data.baseUrl;

    if (result.indexOf('/', result.length - 1) === -1) {
        result = result + '/';
    }

    if (LS.data.showScriptName) {
        result = result + 'index.php';
    }

    if (LS.data.urlFormat == 'get') {
        // Configure route.
        result += '?r=' + route;

        // Configure params.
        for (let key in params) {
            result = result + '&' + key + '=' + params[key];
        }
    } else {
        if (LS.data.showScriptName) {
            result = result + '/';
        }
        // Configure route.
        result += route;

        // Configure params.
        for (let key in params) {
            result = result + '/' + key + '/' + params[key];
        }
    }

    return result;
};
