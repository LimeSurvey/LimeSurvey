// $Id: saved.js 9330 2010-10-24 22:23:56Z c_schmitz $
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:complete', LS.onDocumentReady.Saved);
$(document).on(' pjax:complete',LS.onDocumentReady.Saved);

LS.onDocumentReady.Saved = function(){
    $(".browsetable").tablesorter({
                            widgets: ['zebra'],            
                            sortList: [[0,0]] });
});
