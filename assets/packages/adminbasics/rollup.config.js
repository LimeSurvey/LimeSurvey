// locate modules using the Node resolution algorithm, for using third party modules in node_modules
import nodeResolve from 'rollup-plugin-node-resolve';
// convert commonjs modules to ES6 imports
import commonjs from 'rollup-plugin-commonjs';
// Babel creates backwards compatible javascript transformations
import babel from 'rollup-plugin-babel';
// replace strings in input files with strings
import replace from 'rollup-plugin-replace';
// compile scss files
// import scss from 'rollup-plugin-scss';
import {terser} from 'rollup-plugin-terser';
// import WriteRTLCSS from './buildplugins/rollup-plugin-writertlcss';

const ENVIRONEMENT = process.env.NODE_ENV.trim();

console.log(`Building adminbasics for mode ${ENVIRONEMENT}`);

let plugins = [];
let output = {};

if (ENVIRONEMENT === 'production') {
    output = {
        file: 'build/adminbasics.min.js',
        format: 'umd',
        sourcemap: false,
    };
    plugins = [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT)
        }),
        nodeResolve(),
        commonjs(),
        babel(),
        // scss({
        //     failOnError: true,
        //     outputStyle: 'compressed',
        //     output: 'build/adminbasics.min.css'
        // }),
        // new WriteRTLCSS({
        //     input: 'build/adminbasics.min.css',
        //     output: 'build/adminbasics.rtl.min.css',
        //     compressed: true
        // }),
        terser()
    ];
} else {
    output = {
        file: 'build/adminbasics.js',
        format: 'umd',
        sourcemap: false,
    };
    plugins = [
        replace({
            ENVENVIRONEMENT: ENVIRONEMENT,
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT)
        }),
        nodeResolve(),
        commonjs(),
        babel(),
        // scss({
        //     failOnError: true,
        //     outputStyle: 'expanded',
        //     output: 'build/adminbasics.css'
        // }),
        // WriteRTLCSS({
        //     input: 'build/adminbasics.css',
        //     output: 'build/adminbasics.rtl.css',
        //     compressed: false
        // }),
    ];
}


module.exports = {
    input: 'src/adminbasicsmain.js',
    output,
    plugins
};
