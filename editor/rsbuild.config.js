import { defineConfig, loadEnv } from '@rsbuild/core'
import { pluginEslint } from '@rsbuild/plugin-eslint'
import { pluginReact } from '@rsbuild/plugin-react'
import { pluginSass } from '@rsbuild/plugin-sass'
import { pluginSvgr } from '@rsbuild/plugin-svgr'

const { publicVars, rawPublicVars } = loadEnv({
  prefixes: ['REACT_APP_', 'HTTPS', 'HOST', 'PORT', 'PUBLIC_URL'],
})

// Warn when running a non-production build.
if (
  process.env.NODE_ENV !== 'production' ||
  process.env.REACT_APP_DEV_MODE === 'true' ||
  process.env.REACT_APP_DEMO_MODE === 'true'
) {
  // eslint-disable-next-line no-console
  console.warn(
    '\x1b[1m\x1b[33m\n' +
      '================================================================================\n' +
      ` WARNING: This is a non-production build (NODE_ENV="${process.env.NODE_ENV}").\n` +
      ' Do not deploy this build to production.\n' +
      '================================================================================\n' +
      '\x1b[0m'
  )
}

// Ensure REACT_APP_DEV_MODE and REACT_APP_DEMO_MODE is never true in production builds,
// regardless of what was set in the environment or .env files.
if (process.env.NODE_ENV !== 'development') {
  if (process.env.REACT_APP_DEV_MODE === 'true') {
    // eslint-disable-next-line no-console
    console.error(
      '\x1b[1m\x1b[31m\n' +
        '================================================================================\n' +
        ' WARNING: REACT_APP_DEV_MODE is set to true in a production build!\n' +
        ' This will expose dev code that is not ready for production.\n' +
        ' It will be ignored and deleted.\n' +
        '================================================================================\n' +
        '\x1b[0m'
    )
  }

  delete process.env.REACT_APP_DEV_MODE

  for (const key of Object.keys(rawPublicVars)) {
    if (key.includes('REACT_APP_DEV_MODE')) delete rawPublicVars[key]
  }

  for (const key of Object.keys(publicVars)) {
    if (key.includes('REACT_APP_DEV_MODE')) delete publicVars[key]
  }

  if (process.env.REACT_APP_DEMO_MODE === 'true') {
    // eslint-disable-next-line no-console
    console.error(
      '\x1b[1m\x1b[31m\n' +
        '================================================================================\n' +
        ' WARNING: REACT_APP_DEMO_MODE is set to true in a production build!\n' +
        ' This will expose demo code that should not be in production.\n' +
        ' It will be ignored and deleted.\n' +
        '================================================================================\n' +
        '\x1b[0m'
    )
  }

  delete process.env.REACT_APP_DEMO_MODE

  for (const key of Object.keys(rawPublicVars)) {
    if (key.includes('REACT_APP_DEMO_MODE')) delete rawPublicVars[key]
  }

  for (const key of Object.keys(publicVars)) {
    if (key.includes('REACT_APP_DEMO_MODE')) delete publicVars[key]
  }
}

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
