var webpack = require('webpack');
var path = require('path');


// Naming and path settings
var appName = 'lstutorial';
var entryPoint = ['./src/main.js'];
var exportPath = path.resolve(__dirname, './build');

// Enviroment flag
var plugins = [
    new webpack.EnvironmentPlugin(['NODE_ENV'])
];

appName = appName + '.js';


// Main Settings config
module.exports = {
    entry: entryPoint,
    devtool: 'cheap-module-eval-source-map',
    output: {
        path: exportPath,
        filename: appName
    },
    externals: {
        jquery: 'jQuery',
        pjax: 'Pjax'
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
        }, ],
        loaders: [{
            test: /\.js$/,
            exclude: /(node_modules|bower_components)/,
            loader: [
                'babel'
            ],
            options: {
                data: '$env: ' + process.env.NODE_ENV + ';'
            },
            query: {
                plugins: ['lodash'],
                presets: ['es2015']
            }
        }
        ]
    },
    plugins
};
