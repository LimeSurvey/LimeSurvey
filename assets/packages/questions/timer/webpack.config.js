// const prod = process.env.NODE_ENV === 'production';
const TerserPlugin = require('terser-webpack-plugin');


module.exports = (env, argv) => {
    'use strict';
    return {
        context: __dirname,
        mode: argv.mode,
        entry: './src/main.js',
        output: {
            path: __dirname,
            filename: argv.mode === 'production' ? 'timer.min.js' : 'timer.js'
        },
        module: {
            rules: [{
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            }]
        },
        optimization: {
            minimize: argv.mode === 'production',
            minimizer: [
                new TerserPlugin({ extractComments: false }),
            ],
        },
    };
};