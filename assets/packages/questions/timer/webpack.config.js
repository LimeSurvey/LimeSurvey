const debug = process.env.NODE_ENV !== "production";
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')


module.exports = {
    context: __dirname,
    mode: debug ? "development" : "production",
    devtool: debug ? "inline-sourcemap" : false,
    entry: "./src/main.js",
    output: {
        path: __dirname,
        filename: "timer" + (debug ? '' : '.min') + ".js"
    },
    plugins: debug ? [] : [
        new UglifyJsPlugin(),
    ],
    module: {
        rules: [{
            test: /\.js$/,
            exclude: /(node_modules|bower_components)/,
            use: {
                loader: 'babel-loader',
                options: {
                    presets: ['env']
                }
            }
        }]
    }
};
