import common from 'rollup-plugin-commonjs';
import resolve from 'rollup-plugin-node-resolve';
import babel from 'rollup-plugin-babel';
import replace from 'rollup-plugin-replace';
import scss from 'rollup-plugin-scss'
import { terser } from "rollup-plugin-terser";
import WriteRTLCSS from './buildplugins/rollup-plugin-writertlcss';

const ENVIRONEMENT = process.env.NODE_ENV.trim();

console.log(`Building adminbasics for mode ${ENVIRONEMENT}`);


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
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT),
            'process.env.VUE_ENV': JSON.stringify('browser')
        }),
        babel({
            exclude: 'node_modules/**',
            "presets": [
                [
                    "@babel/preset-env",
                    {
                        targets: "> 0.25%, not dead",
                        modules: 'false',
                        useBuiltIns: "entry",
                        corejs: 3,
                    }
                ]
            ]
        }),
        resolve(),
        common(),
        scss({failOnError: true, outputStyle: 'compressed', output: 'build/adminbasics.min.css'}),
        WriteRTLCSS({input: 'build/adminbasics.min.css', output: 'build/adminbasics.rtl.min.css', compressed: true}),
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
            'process.env.VUE_ENV': JSON.stringify('browser')
        }),
        babel({
            exclude: 'node_modules/**',
            presets: [
                [
                    "@babel/preset-env",
                    {
                        targets: "> 0.25%, not dead",
                        modules: 'false',
                        useBuiltIns: "entry",
                        corejs: 3,
                    }
                ]
            ]
        }),
        resolve(),
        common(),
        scss({failOnError: true, outputStyle: 'expanded', output: 'build/adminbasics.css'}),
        WriteRTLCSS({input: 'build/adminbasics.css', output: 'build/adminbasics.rtl.css', compressed: false}),
    ];
}



module.exports = {
    input: 'src/adminbasicsmain.js',
    output,
    plugins
  };
