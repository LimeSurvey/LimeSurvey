const CKEditorWebpackPlugin = require( '@ckeditor/ckeditor5-dev-webpack-plugin' );
const { styles } = require( '@ckeditor/ckeditor5-dev-utils' );

const appName = 'panelintegration';
const entryPoint = ['./src/'+appName+'main.js', './scss/'+appName+'main.scss'];

module.exports = {
    outputDir: process.env.NODE_ENV === 'production' ? 'build.min/' : 'build/',
    filenameHashing: false,
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
        devtool: process.env.NODE_ENV === 'production' ? 'cheap-module-source-map' : 'source-map',
        externals: {
            LS: 'LS',
            jquery: 'jQuery',
            pjax: 'Pjax',
        },
        plugins: [
            // CKEditor needs its own plugin to be built using webpack.
            new CKEditorWebpackPlugin( {
                // See https://ckeditor.com/docs/ckeditor5/latest/features/ui-language.html
                language: 'en',
                additionalLanguages: 'all',
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
        // Vue CLI would normally use its own loader to load .svg files. The icons used by
        // CKEditor should be loaded using raw-loader instead.

        // Get the default rule for *.svg files.
        const svgRule = config.module.rule( 'svg' );

        // Then you can either:
        //
        // * clear all loaders for existing 'svg' rule:
        //
        svgRule.uses.clear();
        //
        // * or exclude ckeditor directory from node_modules:
        // svgRule.exclude.add( __dirname + '/node_modules/@ckeditor' );
        // svgRule.exclude.add( __dirname + '/src/lib/CkLsEditor/plugin/assets' );

        // Add an entry for *.svg files belonging to CKEditor. You can either:
        //
        // * modify the existing 'svg' rule:
        //
        svgRule.use( 'raw-loader' ).loader( 'raw-loader' );
        //
        // * or add a new one:
        // config.module
        //     .rule( 'cke-svg' )
        //     .test( /ckeditor5-[^/\\]+[/\\]theme[/\\]icons[/\\][^/\\]+\.svg$/ )
        //     .use( 'raw-loader' )
        //     .loader( 'raw-loader' );
        
        config.plugins
            .delete("html")
            .delete("prefetch")
            .delete("preload");
        
        config.optimization.delete('splitChunks');
    }
};