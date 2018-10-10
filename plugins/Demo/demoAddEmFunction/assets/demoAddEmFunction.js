var demoAddEmFunction = {
    sayHello : function (message) {
        return "Hello " + message;
    },
    doHtmlList : function () {
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
}
