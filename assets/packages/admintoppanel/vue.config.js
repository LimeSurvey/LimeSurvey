
const appName = 'admintoppanel';
const entryPoint = ['./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

const RtlCSS = require("../meta/LSRTLPlugin/LSRTLPlugin.js");

const EchoBuildTime = function(){
    return function(){
        this.hooks.done.tap('EchoBuildTime', function () {
            const date = new Date();
            console.log("\n###############\n Build at -> " + date.toLocaleString('de-DE') + "\n###############\n");
        });
    }
}

module.exports = {
    outputDir: process.env.NODE_ENV === 'production' ? 'build.min/' : 'build/',
    filenameHashing: false,
    runtimeCompiler: true,
    productionSourceMap: false,
    
    configureWebpack: {
        entry: entryPoint,
        output: {
            filename: () => {return 'js/'+appName+'.js'}
        },
        devtool: process.env.NODE_ENV === 'production' ? 'none' : 'source-map',
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
            .use(RtlCSS, '{}');

        config.plugin('timeStampAfterBuild')
            .use(EchoBuildTime, [])
        
        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload")
        
        
        config.optimization.delete('splitChunks');
    }
};
