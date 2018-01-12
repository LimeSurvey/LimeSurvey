var dummyConsole = {
    log : function(){},
    error : function(){}
};
console = dummyConsole;
window.console = dummyConsole;