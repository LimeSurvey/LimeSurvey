const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const RtlCssPlugin = require('rtlcss-webpack-plugin');

module.exports = (env, argv) => {
    const isProduction = argv.mode === 'production';
    const outputPath = isProduction ? 'build.min' : 'build';

    return {
        entry: {
            adminsidepanel: [
                './lib/surveysettings.js',
                './src/adminsidepanelmain.js',
                './scss/adminsidepanelmain.scss'
            ]
        },
        output: {
            path: path.resolve(__dirname, outputPath),
            filename: 'js/[name].js',
            clean: false
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['@babel/preset-env', {
                                    targets: {
                                        browsers: ['> 0.25%', 'not dead', 'not ie <= 8']
                                    },
                                    useBuiltIns: 'usage',
                                    corejs: 3
                                }]
                            ]
                        }
                    }
                },
                {
                    test: /\.scss$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: !isProduction
                            }
                        },
                        {
                            loader: 'sass-loader',
                            options: {
                                sourceMap: !isProduction
                            }
                        }
                    ]
                },
                {
                    test: /\.css$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader'
                    ]
                }
            ]
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: 'css/[name].css'
            }),
            new RtlCssPlugin({
                filename: 'css/[name]-rtl.css'
            })
        ],
        optimization: {
            minimize: isProduction,
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        format: {
                            comments: false
                        }
                    },
                    extractComments: false
                }),
                new CssMinimizerPlugin()
            ]
        },
        externals: {
            jquery: 'jQuery',
            LS: 'LS',
            Pjax: 'Pjax'
        },
        devtool: isProduction ? false : 'source-map',
        resolve: {
            extensions: ['.js', '.json']
        },
        stats: {
            colors: true,
            modules: false,
            children: false,
            chunks: false,
            chunkModules: false
        }
    };
};
