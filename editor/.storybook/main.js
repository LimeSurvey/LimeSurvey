import path from 'path'
import webpack from 'webpack'

/** @type { import('@storybook/react-webpack5').StorybookConfig } */
const config = {
  stories: ['../src/**/*.mdx', '../src/**/*.stories.@(js|jsx|ts|tsx)'],

  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@storybook/preset-create-react-app',
    '@storybook/addon-interactions',
    '@storybook/addon-jest',
    '@storybook/addon-coverage',
    '@chromatic-com/storybook',
  ],

  framework: {
    name: '@storybook/react-webpack5',
  },

  docs: {},

  staticDirs: ['../public'],

  core: {
    disableTelemetry: true,
  },

  logLevel: 'error',

  typescript: {
    reactDocgen: 'react-docgen-typescript',
  },

  webpackFinal: async (config) => {
    config.plugins.push(
      new webpack.ProvidePlugin({
        t: [path.resolve(__dirname, './mocks/i18n.js'), 't'],
        st: [path.resolve(__dirname, './mocks/i18n.js'), 'st'],
      })
    )
    config.resolve.alias = {
      ...(config.resolve.alias || {}),
      pluginRegistry: path.resolve(__dirname, '../src/plugins/pluginRegistry'),
    }
    return config
  },
}

export default config
