/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */
import "core-js/features/symbol";
import "core-js/features/array";
import ConsoleShim from "../../meta/lib/ConsoleShim";

window.ConsoleShim = ConsoleShim;

if(window.debugState.backend || window.debugState.frontend){
    const globalLSConsole = new ConsoleShim('LSLOG');
    window.console.ls = globalLSConsole;
} else {
    const globalLSConsole = new ConsoleShim('LSLOG', true);
    window.console.ls = globalLSConsole;
}
