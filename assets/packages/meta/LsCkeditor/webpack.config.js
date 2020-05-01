/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

'use strict';

/* eslint-env node */

const path = require('path');
const webpack = require('webpack');
const {
    bundler,
    styles
} = require('@ckeditor/ckeditor5-dev-utils');
const CKEditorWebpackPlugin = require('@ckeditor/ckeditor5-dev-webpack-plugin');

module.exports = (env, argv) => {

    const config = {
        performance: {
            hints: false
        },

        entry: path.resolve(__dirname, 'src', 'LsCkEditor.js'),

        optimization: {},

        plugins: [
            new CKEditorWebpackPlugin({
                // UI language. Language codes follow the https://en.wikipedia.org/wiki/ISO_639-1 format.
                // When changing the built-in language, remember to also change it in the editor's configuration (src/ckeditor.js).
                language: 'en',
                additionalLanguages: 'all'
            }),
        ],

        module: {
            rules: [{
                    test: /\.m?js$/,
                    exclude: /(node_modules|bower_components)/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env'],
                        }
                    }
                },
                {
                    test: /\.svg$/,
                    use: ['raw-loader']
                },
                {
                    test: /\.scss$/,
                    use: [
                        'style-loader',
                        'css-loader',
                        'sass-loader',
                    ]
                },
                {
                    test: /\.css$/,
                    use: [{
                            loader: 'style-loader',
                        },
                        {
                            loader: 'postcss-loader',
                            options: styles.getPostCssConfig({
                                themeImporter: {
                                    themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
                                },
                            })
                        },
                    ]
                }
            ]
        }
    };
    if (argv.mode === 'development') {
        config.devtool = 'eval-source-map';
        config.output = {
            filename: 'LsCkEditorBind.js',
            path: path.resolve(__dirname, 'build'),
            library: 'LSCK',
            libraryTarget: 'umd'
        };
    } else if (argv.mode === 'production') {
        config.devtool = false;
        config.output = {
            filename: 'LsCkEditorBind.min.js',
            path: path.resolve(__dirname, 'build'),
            library: 'LSCK',
            libraryTarget: 'umd'
        };
        
    }
    return config;
};
