/**
 * Webpack configuration for Global Sidebar Panel
 * Vanilla JavaScript implementation
 */

const path = require('path');

const appName = 'globalsidepanel';
const isDevelopment = process.env.NODE_ENV !== 'production';
const outputDir = isDevelopment ? 'build/' : 'build.min/';

module.exports = {
    mode: isDevelopment ? 'development' : 'production',
    entry: {
        [appName]: './src/globalsidepanelmain.js'
    },
    output: {
        path: path.resolve(__dirname, outputDir),
        filename: 'js/[name].js',
        library: 'GlobalSidePanel',
        libraryTarget: 'umd',
        libraryExport: 'default'
    },
    externals: {
        LS: 'LS',
        jquery: 'jQuery',
        pjax: 'Pjax',
        $: 'jQuery',
        jQuery: 'jQuery'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        configFile: false,
                        babelrc: false,
                        presets: [
                            ['@babel/preset-env', {
                                targets: {
                                    browsers: ['> 1%', 'last 2 versions']
                                }
                            }]
                        ]
                    }
                }
            }
        ]
    },
    resolve: {
        extensions: ['.js'],
        alias: {
            '@': path.resolve(__dirname, 'src')
        }
    },
    devtool: isDevelopment ? 'source-map' : false,
    optimization: {
        minimize: !isDevelopment
    }
};
