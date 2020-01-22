const fs = require('fs');
const path = require('path');
const Terser = require('terser');


const options = {
    warnings: true,
};

console.log("Reading input files");
const pjaxJSFile = fs.readFileSync(path.join(process.cwd(), 'pjax.js'), 'utf8');
const loadPjaxJSFile = fs.readFileSync(path.join(process.cwd(), 'loadPjax.js'), 'utf8');

console.log("Running terser");
const terserResultPjax = Terser.minify(pjaxJSFile, options);
const terserResultCombined = Terser.minify({
    'pjax.js' : pjaxJSFile,
    'loadPjax.js' : loadPjaxJSFile
}, options);
//Errors
if(terserResultPjax.error) {
    console.error(terserResultPjax.error)
}
if(terserResultCombined.error) {
    console.error(terserResultPjax.error)
}
// Warnings
if(terserResultCombined.warnings) {
    console.warn(terserResultPjax.warnings.join("\n"));
}
if(terserResultCombined.warnings) {
    console.warn(terserResultPjax.warnings.join("\n"));
}

console.log("Writing output");
fs.writeFileSync(path.join(process.cwd(),'min','pjax.min.js'), terserResultPjax.code);
fs.writeFileSync(path.join(process.cwd(),'min','pjax.combined.min.js'), terserResultCombined.code);

console.log("Done");
