import common from 'rollup-plugin-commonjs';
import resolve from 'rollup-plugin-node-resolve';
import babel from 'rollup-plugin-babel';
import replace from 'rollup-plugin-replace';
import scss from 'rollup-plugin-scss'
import { terser } from "rollup-plugin-terser";

const ENVIRONEMENT = process.env.NODE_ENV.trim();
const DIRECTION = process.env.DIRECTION_ENV.trim();

console.log(`Building adminbasics for mode ${ENVIRONEMENT} and direction ${DIRECTION}`);


let plugins =  [];
let output = {};

if( ENVIRONEMENT=='production' ) {
    output = {
      file: 'build/adminbasics.min.js',
      format: 'umd',
      sourcemap: true,
    };
    plugins = [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': ENVIRONEMENT,
            ENVDIRECTION:DIRECTION,
            'process.env.VUE_ENV': JSON.stringify('browser')
        }),
        babel({exclude: 'node_modules/**'}),
        resolve(),
        common(),
        scss({failOnError: true, outputStyle: 'compressed', output: DIRECTION=='ltr' ? 'build/adminbasics.min.css' : 'build/adminbasics.rtl.min.css'}),
        terser()
    ];
} else {
  output = {
    file: 'build/adminbasics.js',
    format: 'umd',
    sourcemap: false,
  };
  plugins =  [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT),
            ENVDIRECTION:DIRECTION,
            'process.env.VUE_ENV': JSON.stringify('browser')
        }),
        babel({exclude: 'node_modules/**'}),
        resolve(),
        common(),
        scss({failOnError: true, outputStyle: 'expanded', output: DIRECTION=='ltr' ? 'build/adminbasics.css' : 'build/adminbasics.rtl.css'}),
    ];
}



module.exports = {
    input: 'src/adminbasicsmain.js',
    output,
    plugins
  };