import common from 'rollup-plugin-commonjs';
import resolve from 'rollup-plugin-node-resolve';
import babel from 'rollup-plugin-babel';
import replace from 'rollup-plugin-replace';
import scss from 'rollup-plugin-scss'
import { terser } from "rollup-plugin-terser";
import WriteRTLCSS from './buildplugins/rollup-plugin-writertlcss';
import "core-js";

const ENVIRONEMENT = process.env.NODE_ENV.trim();

console.log(`Building lstutorial for mode ${ENVIRONEMENT}`);

let plugins =  [];
let output = {};

if( ENVIRONEMENT=='production' ) {
    output = {
      file: 'build/lstutorial.min.js',
      format: 'umd',
      sourcemap: true,
    };
    plugins = [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': ENVIRONEMENT,
            'process.env.VUE_ENV': JSON.stringify('browser')
        }),
        babel({
            exclude: 'node_modules/**',
        }),
        resolve(),
        common(),
        scss({failOnError: true, outputStyle: 'compressed', output: 'build/lstutorial.min.css'}),
        WriteRTLCSS({input: 'build/lstutorial.min.css', output: 'build/lstutorial.rtl.min.css', compressed: true}),
        terser()
    ];
} else {
  output = {
    file: 'build/lstutorial.js',
    format: 'umd',
    sourcemap: false,
  };
  plugins =  [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT),
            'process.env.VUE_ENV': JSON.stringify('browser')
        }),
        babel({exclude: 'node_modules/**'}),
        resolve(),
        common(),
        scss({failOnError: true, outputStyle: 'expanded', output: 'build/lstutorial.css'}),
        WriteRTLCSS({input: 'build/lstutorial.css', output: 'build/lstutorial.rtl.css', compressed: false}),
    ];
}



module.exports = {
    input: 'src/lstutorialmain.js',
    external: ['$', "LS"],
    output,
    plugins
  };
