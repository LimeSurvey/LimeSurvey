import { defineConfig, loadEnv } from '@rsbuild/core'
import { pluginEslint } from '@rsbuild/plugin-eslint'
import { pluginReact } from '@rsbuild/plugin-react'
import { pluginSass } from '@rsbuild/plugin-sass'
import { pluginSvgr } from '@rsbuild/plugin-svgr'

const { publicVars, rawPublicVars } = loadEnv({
  prefixes: ['REACT_APP_', 'HTTPS', 'HOST', 'PORT', 'PUBLIC_URL'],
})

export default defineConfig({
  plugins: [
    pluginEslint({
      enable: process.env.NODE_ENV === 'development',
    }),
    pluginReact(),
    pluginSvgr({ mixedImport: true }),
    pluginSass({
      sassLoaderOptions: {
        sassOptions: {
          silenceDeprecations: ['import', 'global-builtin', 'color-functions'],
        },
      },
    }),
  ],
  html: {
    template: './public/index.html',
  },
  source: {
    // Compile all JS files and exclude core-js
    include: [{ not: [/[\\/]core-js[\\/]/] }],
    define: {
      ...publicVars,
      'process.env': JSON.stringify(rawPublicVars),
    },
  },
  resolve: {
    alias: {
      // Base directories for imports
      helpers: './src/helpers',
      components: './src/components',
      hooks: './src/hooks',
      services: './src/services',
      queryClient: './src/queryClient.js',
      assets: './src/assets',
      plugins: './src/plugins',
      shared: './src/shared',
      pluginRegistry: './src/plugins/pluginRegistry',
      appInstrumentation: './src/appInstrumentation',
    },
  },
  server: {
    host: '0.0.0.0',
    port: process.env.PORT || 5000,
    https: process.env.HTTPS === 'true',
  },
  output: {
    sourceMap: true,
    distPath: { root: 'build' },
    assets: {
      image: {
        limit: 10000,
      },
    },
    assetPrefix: process.env.PUBLIC_URL || '/',
    polyfill: 'usage',
  },
  tools: {
    rspack: {
      resolve: {
        preferRelative: true,
      },
    },
    sass: {
      sassOptions: {
        includePaths: ['node_modules'],
      },
    },
  },
  dev: {
    assetPrefix: '/editor/',
    client: {
      path: '/editor/rsbuild-hmr',
    },
    lazyCompilation: false,
  },
})
