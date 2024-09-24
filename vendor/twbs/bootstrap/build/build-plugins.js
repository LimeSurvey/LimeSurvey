#!/usr/bin/env node

/*!
 * Script to build our plugins to use them separately.
 * Copyright 2020-2021 The Bootstrap Authors
 * Copyright 2020-2021 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 */

'use strict'

const path = require('path')
const rollup = require('rollup')
const { babel } = require('@rollup/plugin-babel')
const banner = require('./banner.js')

const rootPath = path.resolve(__dirname, '../js/dist/')
const plugins = [
  babel({
    // Only transpile our source code
    exclude: 'node_modules/**',
    // Include the helpers in each file, at most one copy of each
    babelHelpers: 'bundled'
  })
]
const bsPlugins = {
  Data: path.resolve(__dirname, '../js/src/dom/data.js'),
  EventHandler: path.resolve(__dirname, '../js/src/dom/event-handler.js'),
  Manipulator: path.resolve(__dirname, '../js/src/dom/manipulator.js'),
  SelectorEngine: path.resolve(__dirname, '../js/src/dom/selector-engine.js'),
  Alert: path.resolve(__dirname, '../js/src/alert.js'),
  Base: path.resolve(__dirname, '../js/src/base-component.js'),
  Button: path.resolve(__dirname, '../js/src/button.js'),
  Carousel: path.resolve(__dirname, '../js/src/carousel.js'),
  Collapse: path.resolve(__dirname, '../js/src/collapse.js'),
  Dropdown: path.resolve(__dirname, '../js/src/dropdown.js'),
  Modal: path.resolve(__dirname, '../js/src/modal.js'),
  Offcanvas: path.resolve(__dirname, '../js/src/offcanvas.js'),
  Popover: path.resolve(__dirname, '../js/src/popover.js'),
  ScrollSpy: path.resolve(__dirname, '../js/src/scrollspy.js'),
  Tab: path.resolve(__dirname, '../js/src/tab.js'),
  Toast: path.resolve(__dirname, '../js/src/toast.js'),
  Tooltip: path.resolve(__dirname, '../js/src/tooltip.js')
}

const defaultPluginConfig = {
  external: [
    bsPlugins.Data,
    bsPlugins.Base,
    bsPlugins.EventHandler,
    bsPlugins.SelectorEngine
  ],
  globals: {
    [bsPlugins.Data]: 'Data',
    [bsPlugins.Base]: 'Base',
    [bsPlugins.EventHandler]: 'EventHandler',
    [bsPlugins.SelectorEngine]: 'SelectorEngine'
  }
}

const getConfigByPluginKey = pluginKey => {
  switch (pluginKey) {
    case 'Alert':
    case 'Offcanvas':
    case 'Tab':
      return defaultPluginConfig

    case 'Base':
    case 'Button':
    case 'Carousel':
    case 'Collapse':
    case 'Modal':
    case 'ScrollSpy': {
      const config = Object.assign(defaultPluginConfig)
      config.external.push(bsPlugins.Manipulator)
      config.globals[bsPlugins.Manipulator] = 'Manipulator'
      return config
    }

    case 'Dropdown':
    case 'Tooltip': {
      const config = Object.assign(defaultPluginConfig)
      config.external.push(bsPlugins.Manipulator, '@popperjs/core')
      config.globals[bsPlugins.Manipulator] = 'Manipulator'
      config.globals['@popperjs/core'] = 'Popper'
      return config
    }

    case 'Popover':
      return {
        external: [
          bsPlugins.Data,
          bsPlugins.SelectorEngine,
          bsPlugins.Tooltip
        ],
        globals: {
          [bsPlugins.Data]: 'Data',
          [bsPlugins.SelectorEngine]: 'SelectorEngine',
          [bsPlugins.Tooltip]: 'Tooltip'
        }
      }

    case 'Toast':
      return {
        external: [
          bsPlugins.Data,
          bsPlugins.Base,
          bsPlugins.EventHandler,
          bsPlugins.Manipulator
        ],
        globals: {
          [bsPlugins.Data]: 'Data',
          [bsPlugins.Base]: 'Base',
          [bsPlugins.EventHandler]: 'EventHandler',
          [bsPlugins.Manipulator]: 'Manipulator'
        }
      }

    default:
      return {
        external: []
      }
  }
}

const utilObjects = new Set([
  'Util',
  'Sanitizer',
  'Backdrop'
])

const domObjects = new Set([
  'Data',
  'EventHandler',
  'Manipulator',
  'SelectorEngine'
])

const build = async plugin => {
  console.log(`Building ${plugin} plugin...`)

  const { external, globals } = getConfigByPluginKey(plugin)
  const pluginFilename = path.basename(bsPlugins[plugin])
  let pluginPath = rootPath

  if (utilObjects.has(plugin)) {
    pluginPath = `${rootPath}/util/`
  }

  if (domObjects.has(plugin)) {
    pluginPath = `${rootPath}/dom/`
  }

  const bundle = await rollup.rollup({
    input: bsPlugins[plugin],
    plugins,
    external
  })

  await bundle.write({
    banner: banner(pluginFilename),
    format: 'umd',
    name: plugin,
    sourcemap: true,
    globals,
    generatedCode: 'es2015',
    file: path.resolve(__dirname, `${pluginPath}/${pluginFilename}`)
  })

  console.log(`Building ${plugin} plugin... Done!`)
}

const main = async () => {
  try {
    await Promise.all(Object.keys(bsPlugins).map(plugin => build(plugin)))
  } catch (error) {
    console.error(error)

    process.exit(1)
  }
}

main()
