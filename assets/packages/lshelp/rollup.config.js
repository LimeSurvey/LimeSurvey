// rollup.config.js
import resolve from 'rollup-plugin-node-resolve';
import babel from 'rollup-plugin-babel';
import uglify from 'rollup-plugin-uglify';

const plugins = [
    resolve(),
    babel({
        exclude: 'node_modules/**', // only transpile our source code
        presets: [['env', {modules: false, 'targets' : { 'browsers' :  ['last 2 versions', 'ie 10'] }}]],
        externalHelpers: true
    })
];

let outfile = 'build/lshelper.js';

if(process.env.NODE_ENV === 'production') {
    plugins.push(
        uglify()
    );
    outfile = 'build/lshelper.min.js';
}

export default {
    input: 'src/main.js',
    output: {
        file: outfile,
        format: 'iife'
    },
    plugins: plugins
};
