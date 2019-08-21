
const appName = 'admintoppanel';
const entryPoint = ['./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

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
        config.plugin('timeStampAfterBuild')
            .use(EchoBuildTime, [])
        
        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload")
        
        
        config.optimization.delete('splitChunks');
    }
};
