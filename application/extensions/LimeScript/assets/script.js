// Initial definition of Limesurvey javascript object.
function LimeSurvey(data) {
    var that = this;
    this.createUrl = function (route, params) {
        if (typeof params === 'undefined') {
            params = {};
        }
        var result = data.baseUrl;

        if (result.indexOf('/', result.length - 1) === -1) {
            result = result + '/';
        }

        if (data.showScriptName) {
            result = result + 'index.php';
        }


        if (data.urlFormat == 'get') {
            // Configure route.
            result += '?r=' + route;

            // Configure params.
            for (var key in params) {

                result = result + '&' + key + '=' + urlParamToString(params[key]);
            }
        }
        else {
            if (data.showScriptName) {
                result = result + '/';
            }
            // Configure route.
            result += route;

            // Configure params.
            for (var key in params) {
                result = result + '/' + key + '/' + urlParamToString(params[key]);
            }
        }

        return result;
    };

    var urlParamToString = function(value) {
        if (typeof value == "string") {
            return value;
        } else if (typeof value == "boolean") {
            return value ? 1 : 0;
        }
    }

    this.getToken = function () {
        return data.csrfToken;
    }

    this.getBaseUrl = function () {
        return data.baseUrl;
    }
}