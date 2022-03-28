
const appName = 'adminsidepanel';
const outputDir = process.env.NODE_ENV === 'production' ? 'build.min/' : 'build/';
const entryPoint = ['./lib/surveysettings.js','./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

const RtlCSS = require("../meta/LSRTLPlugin/LSRTLPlugin.js");


module.exports = {
    outputDir: outputDir,
    filenameHashing: false,
    runtimeCompiler: true,
    productionSourceMap: false,
    configureWebpack: {
        entry: entryPoint,
        output: {
            filename: () => {return 'js/'+appName+'.js'}
        },
        externals: {
            LS: 'LS',
            jquery: 'jQuery',
            pjax: 'Pjax',
        },
    },

    chainWebpack: config => {
        const MiniCSS = require("mini-css-extract-plugin");

        if (config.plugins.has("extract-css")) {
            const extractCSSPlugin = config.plugin("extract-css");
            extractCSSPlugin &&
              extractCSSPlugin.tap(() => [
                {
                  filename:  "css/"+appName+".css",
                  chunkFilename:  "css/"+appName+".css"
                }
              ]);
          }
        
        if(process.env.NODE_ENV === 'development') {

            ['vue-modules', 'vue', 'normal-modules', 'normal'].forEach((type) => {
                config.module.rule('css')
                    .oneOf(type)
                    .use('extract-css')
                    .loader(MiniCSS.loader)
                    .options({publicPath: '../'})
                    .before('css-loader')
                config.module.rule('postcss')
                    .oneOf(type)
                    .use('extract-css')
                    .loader(MiniCSS.loader)
                    .options({publicPath: '../'})
                    .before('css-loader')
                config.module.rule('scss')
                    .oneOf(type)
                    .use('extract-css')
                    .loader(MiniCSS.loader)
                    .options({publicPath: '../'})
                    .before('css-loader')
            });
        }

        if (config.plugins.has("extract-css")) {
        const extractCSSPlugin = config.plugin("extract-css");
        extractCSSPlugin &&
            extractCSSPlugin.tap(() => [
                {
                    filename:  "css/"+appName+".css",
                    chunkFilename:  "css/"+appName+".css"
                }
            ]);
        } else {
            config.plugin('extract-css')
                .use(MiniCSS, [{
                    filename: 'css/'+appName+'.css',
                    chunkFilename: 'css/'+appName+'.css'
                }]);
        }

        config.plugin('rtlcss')
            .use(RtlCSS, [{
                stringMap: [
                    {
                      'name'    : 'left-right',
                      'priority': 100,
                      'search'  : ['left', 'Left', 'LEFT'],
                      'replace' : ['right', 'Right', 'RIGHT'],
                      'options' : {
                          'scope' : '*',
                          'ignoreCase' : false
                        }
                    },
                    {
                      'name'    : 'ltr-rtl',
                      'priority': 100,
                      'search'  : ['ltr', 'Ltr', 'LTR'],
                      'replace' : ['rtl', 'Rtl', 'RTL'],
                      'options' :	{
                          'scope' : '*',
                          'ignoreCase' : false
                        }
                    },
                    {
                        name: 'icon-direction',
                        search: ['fa-chevron-right', 'fa-chevron-left'],
                        replace: ['fa-chevron-left', 'fa-chevron-right'],
                    }
                  ]
            }]);

        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload");
        
        config.optimization.delete('splitChunks');
    }
};
