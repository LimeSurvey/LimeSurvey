import common from 'rollup-plugin-commonjs';
import resolve from 'rollup-plugin-node-resolve';
import babel from 'rollup-plugin-babel';
import replace from 'rollup-plugin-replace';
import { terser } from "rollup-plugin-terser";

const ENVIRONEMENT = process.env.NODE_ENV.trim();
console.log(`Building lslog for mode ${ENVIRONEMENT}`);

let output, plugins;

if( ENVIRONEMENT=='production' ) {
    output = {
      file: 'build/lslog.min.js',
      format: 'umd',
      sourcemap: true,
    };
    plugins = [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT)
        }),
        babel({
            exclude: 'node_modules/**',
            "presets": [
                [
                    "@babel/preset-env",
                    {
                        targets: {
                            "browsers": "> 0.25%, not dead",
                            "ie": "11"
                        },
                        modules: 'false',
                        useBuiltIns: 'entry',
                        corejs: 3,
                    }
                ]
            ]
        }),
        resolve(),
        common(),
        terser()
    ];
} else {
  output = {
    file: 'build/lslog.js',
    format: 'umd',
    sourcemap: false,
  };
  plugins =  [
        replace({
            ENVENVIRONEMENT:ENVIRONEMENT,
            'process.env.NODE_ENV': JSON.stringify(ENVIRONEMENT)
        }),
        babel({
            exclude: 'node_modules/**',
            presets: [
                [
                    "@babel/preset-env",
                    {
                        targets: {
                            "browsers": "> 0.25%, not dead",
                            "ie": "11"
                        },
                        modules: 'false',
                        useBuiltIns: 'entry',
                        corejs: 3,
                    }
                ]
            ]
        }),
        resolve(),
        common()
    ];
}



module.exports = {
    input: 'src/lslog.js',
    output,
    plugins
  };
