function sayHello(message) {
    return "Hello " + message;
}
function doHtmlList() {
    if(!arguments.length){
        return "";
    }
    var returnHtml = "";
    for (i=0;i<arguments.length;++i) {
        var string = String(arguments[i]);
        if (string) {
            returnHtml = returnHtml + "<li>" + string + "</li>";
        }
    }
    if(returnHtml) {
        returnHtml = "<ul>" + returnHtml + "</ul>";
    }
    return returnHtml;
}
