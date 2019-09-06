// rollup-plugin-my-example.js
const fs = require("fs");
const path = require("path");
const rtlcss = require('rtlcss');
const uglifycss = require("uglifycss");

export default function WriteRTLCSS(options) {
    return {
        name: 'rollup-plugin-writertlcss', // this name will show up in warnings and errors
        writeBundle(bundle) {
            return new Promise((resolve, reject)=>{
                console.log('Parsing css to create rtl version from ' + options.input + '');
                if(options.compress) { console.log("Running compression afterwards"); }
                fs.readFile(options.input, 'utf8', (err, data) => {
                    if(err) { return reject(err); }
                    let rtlSource = rtlcss.process(data);
                    if(options.compress) {
                        rtlSource = uglifycss.processString(rtlSource);
                    }
                    fs.writeFile(options.output, rtlSource, (err) => {
                        if(err) { return reject(err); }
                        resolve(bundle);
                    })
                })
            })

        }
    };
}
