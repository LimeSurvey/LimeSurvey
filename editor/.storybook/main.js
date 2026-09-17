import path from 'path'
import { fileURLToPath } from 'url'
import { mergeRsbuildConfig, rspack } from '@rsbuild/core'

// Storybook 10 loads this file as native ESM, where __dirname does not exist.
const dirname = path.dirname(fileURLToPath(import.meta.url))

/** @type { import('storybook-react-rsbuild').StorybookConfig } */
const config = {
  stories: ['../src/**/*.mdx', '../src/**/*.stories.@(js|jsx|ts|tsx)'],

  addons: [
    '@storybook/addon-links',
    '@storybook/addon-coverage',
    '@chromatic-com/storybook',
  ],

  framework: {
    name: 'storybook-react-rsbuild',
  },

  docs: {},

  staticDirs: ['../public'],

  core: {
    disableTelemetry: true,
  },

  logLevel: 'error',

  typescript: {
    reactDocgen: 'react-docgen',
  },

  rsbuildFinal: (config) =>
    mergeRsbuildConfig(config, {
      tools: {
        rspack: {
          // jsconfig.json sets baseUrl:"src"; stories reach sbook/ and themes/
          // through it, so the builder needs it spelled out.
          resolve: {
            modules: ['node_modules', path.resolve(dirname, '../src')],
          },
          plugins: [
            new rspack.ProvidePlugin({
              t: [path.resolve(dirname, './mocks/i18n.js'), 't'],
              st: [path.resolve(dirname, './mocks/i18n.js'), 'st'],
            }),
          ],
        },
      },
      resolve: {
        alias: {
          pluginRegistry: path.resolve(dirname, '../src/plugins/pluginRegistry'),
        },
      },
    }),
}

export default config
