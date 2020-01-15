import common from 'rollup-plugin-commonjs';
import resolve from 'rollup-plugin-node-resolve';
import babel from 'rollup-plugin-babel';
import replace from 'rollup-plugin-replace';
import scss from 'rollup-plugin-scss'
import { terser } from "rollup-plugin-terser";

const ENVIRONEMENT = process.env.NODE_ENV.trim();

console.log(`Building upload question type scripts for mode ${ENVIRONEMENT}`);

let plugins =  [];
let output = {};

if( ENVIRONEMENT=='production' ) {
    output = {
      file: 'build/uploadquestion.min.js',
      format: 'umd',
      sourcemap: true,
    };
    plugins = [
      replace({ENVENVIRONEMENT:ENVIRONEMENT}),
      babel({exclude: 'node_modules/**'}),
      resolve(),
      common(),
      scss({failOnError: true, outputStyle: 'compressed', output: 'build/uploadquestion.min.css'}),
      terser()
    ];
} else {
  output = {
    file: 'build/uploadquestion.js',
    format: 'umd',
    sourcemap: false,
  };
  plugins =  [
    replace({ENVENVIRONEMENT:ENVIRONEMENT}),
      babel({exclude: 'node_modules/**'}),
      resolve(),
      common(),
      scss({failOnError: true, outputStyle: 'expanded', output: 'build/uploadquestion.css'}),
    ];
}

module.exports = {
    input: 'src/uploader.js',
    output,
    plugins
  };
