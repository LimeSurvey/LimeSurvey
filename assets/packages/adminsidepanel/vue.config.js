
const appName = 'adminsidepanel';
const outputDir = process.env.NODE_ENV === 'production' ? 'build.min/' : 'build/';
const entryPoint = ['./lib/surveysettings.js','./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

const RtlCSS = require("../meta/LSRTLPlugin/LSRTLPlugin.js");


module.exports = {
    outputDir: outputDir,
    filenameHashing: false,
    runtimeCompiler: true,
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

        config.plugin('rtlcss')
            .use(RtlCSS, '{}');

        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload");
        
        config.optimization.delete('splitChunks');
    }
};
