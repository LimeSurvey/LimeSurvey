const CKEditorWebpackPlugin = require( '@ckeditor/ckeditor5-dev-webpack-plugin' );
const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );

const appName = 'labelsets';
const entryPoint = ['./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

module.exports = {
    outputDir: process.env.NODE_ENV === 'production' ? 'build.min/' : 'build/',
    filenameHashing: false,
    productionSourceMap: false,
    runtimeCompiler: true,
    
    // The source of CKEditor is encapsulated in ES6 modules. By default, the code
    // from the node_modules directory is not transpiled, so you must explicitly tell
    // the CLI tools to transpile JavaScript files in all ckeditor5-* modules.
    transpileDependencies: [
        /ckeditor5-[^/\\]+[/\\]src[/\\].+\.js$/,
    ],

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
        plugins: [
            // CKEditor needs its own plugin to be built using webpack.
            new CKEditorWebpackPlugin( {
                // See https://ckeditor.com/docs/ckeditor5/latest/features/ui-language.html
                language: 'en',
            } )
        ]
    },
    css: {
        loaderOptions: {
            // Various modules in the CKEditor source code import .css files.
            // These files must be transpiled using PostCSS in order to load properly.
            postcss: styles.getPostCssConfig( {
                themeImporter: {
                    themePath: require.resolve( '@ckeditor/ckeditor5-theme-lark' )
                },
                minify: true
            } )
        }
    },

    chainWebpack: config => {
        const svgRule = config.module.rule( 'svg' );
        svgRule.uses.clear();
        svgRule.use( 'raw-loader' ).loader( 'raw-loader' );
        
        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload");
        
        config.optimization.delete('splitChunks');
    }
};
