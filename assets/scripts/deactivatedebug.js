var dummyConsole = {};
var realConsole = console || window.console
for (var consoleFunction in realConsole) {
    dummyConsole[consoleFunction] = function(){};
}

console = dummyConsole;
window.console = dummyConsole;
