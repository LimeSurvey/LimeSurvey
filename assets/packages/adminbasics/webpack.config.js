var webpack = require('webpack');
var path = require('path');


// Naming and path settings
var appName = 'adminbasics';
var entryPoint = ['babel-polyfill','./src/adminbasicsmain.js'];
var exportPath = path.resolve(__dirname, './build');

// Enviroment flag
var plugins = [
    new webpack.EnvironmentPlugin(['NODE_ENV'])
];

appName = appName + '.js';


// Main Settings config
module.exports = {
    entry: entryPoint,
    devtool: 'source-map',
    output: {
        path: exportPath,
        filename: appName
    },
    externals: {
        jquery: 'jQuery',
        pjax: 'Pjax',
    },
    module: {
        rules: [{
            test: /\.scss$/,
            use: [{
                loader: 'style-loader' // creates style nodes from JS strings
            }, {
                loader: 'css-loader' // translates CSS into CommonJS
            }, {
                loader: 'sass-loader' // compiles Sass to CSS
            }]
        }],
        loaders: [
        {
            test: /\.js$/,
            exclude: /(node_modules|bower_components)/,
            loader: [
                'eslint-loader',
                'babel-loader'
            ],
            options: {
                data: '$env: ' + process.env.NODE_ENV + ';'
            },
            query: {
                presets: [['env', {'targets' : { 'browsers' :  ['last 2 versions', 'ie 10'] }}]]
            }
        },
        {
            loader: 'sass-loader',
            options: {
                data: '$env: ' + process.env.NODE_ENV + ';'
            }
        }
        ]
    },
    plugins
};
