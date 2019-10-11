
const appName = 'filemanager';
const entryPoint = ['./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

class EchoBuildTime {
    apply(compiler) {
        compiler.hooks.done.tapAsync('EchoBuildTime', (stats, cb) => {
            const date = new Date();
            const options = {
                day: '2-digit',
                weekday: 'short',
                year: 'numeric',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            };
            console.log("\n###############\n Build at -> " + date.toLocaleString('de-DE', options) + "\n###############\n");
            cb();
        });
    }
}

module.exports = {
    outputDir: process.env.NODE_ENV === 'production' ? 'build.min/' : 'build/',
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
            $: '$',
            pjax: 'Pjax',
        },
    },

    chainWebpack: config => {
                        
        config.plugin('timeStampAfterBuild')
            .use(EchoBuildTime, [])
        
        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload");
        
        config.optimization.delete('splitChunks');
    }
};
