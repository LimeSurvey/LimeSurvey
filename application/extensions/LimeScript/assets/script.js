// Initial definition of Limesurvey javascript object.
// If LS was already defined (e.g. in a plugin), use that object.
var LS = LS || {};

LS.createUrl = function (route, params)
{
    if (typeof params === 'undefined') {
        params = {};
    }
    var result = LS.data.baseUrl;

    if (result.indexOf('/', result.length - 1) === -1)
    {
        result = result + '/';
    }

    if (LS.data.showScriptName)
    {
        result = result + 'index.php';
    }


    if (LS.data.urlFormat == 'get')
    {
        // Configure route.
        result += '?r=' + route;

        // Configure params.
        for (var key in params)
        {
            result = result + '&' + key+ '=' + params[key];
        }
    }
    else
    {
        if (LS.data.showScriptName)
        {
            result = result + '/';
        }
        // Configure route.
        result += route;

        // Configure params.
        for (var key in params)
        {
            result = result + '/' + key + '/' + params[key];
        }
    }

    return result;
};
