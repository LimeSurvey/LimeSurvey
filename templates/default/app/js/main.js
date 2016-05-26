var assetsPath = '/assets/';

// Retrieve GET parameters
var pageUrl = window.location.search.substring(1);
var getVars = pageUrl.split('&');
var getParams = {};

for (var i = 0; i < getVars.length; i++) {
    var parameter = getVars[i].split('=');
    getParams[parameter[0]] = parameter[1];
}

$(document).ready(function(){
    // Load logo+stylesheet
    // Random justification : http://stackoverflow.com/questions/12992717/how-to-prevent-css-caching-on-a-web-page
    var rand = Math.floor((Math.random() * 1000) + 1);
    $('#scoreway-stylesheet').attr('href', assetsPath + getParams.ens + '/style.css?id='+rand);
    $('.logo').attr('src', assetsPath + getParams.ens + '/logo');

    // Keep the same parameters all survey long
    var targetUrl = $('#limesurvey').attr('action');
    targetUrl += '?ens=' + getParams.ens;
    $('#limesurvey').attr('action', targetUrl);
});

