const fs = require("fs");
const path = require("path");
const rtlcss = require("rtlcss");
const _ = require('lodash');
const ConcatSource = require('webpack-sources').ConcatSource;

class LSRTLPlugin {
    constructor(options) {
        this.options = _.merge({
            suffix: 'rtl.css'
        }, options);
    }

    apply(compiler) {
        compiler.hooks.emit.tapAsync('LSRTLPlugin', (compilation, callback) => {
            const rtlFiles = [];
            _.forEach(compilation.chunks, (chunk) =>{
                _.forEach(chunk.files, (file) => {
                    if(path.extname(file) == '.css') {
                        const baseSource = compilation.assets[file].source();
                        const rtlResult = rtlcss.process(baseSource);
                        const pathObject =  path.parse(file);
                        const rtlName = path.join(pathObject.dir, pathObject.name + '.' + this.options.suffix);
                        compilation.assets[rtlName] = new ConcatSource(rtlResult);
                        rtlFiles.push(rtlName)
                    }
                    
                });
                rtlFiles.forEach((rtlFile) => chunk.files.push(rtlFile));
            });
            callback();
        })
    }
}

module.exports = LSRTLPlugin;
