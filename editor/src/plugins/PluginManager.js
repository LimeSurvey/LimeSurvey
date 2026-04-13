import { pluginRegistry } from 'pluginRegistry'

export class PluginManager {
  static instance = null

  constructor() {
    this.plugins = new Map()
    this.loadPlugins()
  }

  static getInstance() {
    if (!PluginManager.instance) {
      PluginManager.instance = new PluginManager()
    }
    return PluginManager.instance
  }

  loadPlugins() {
    Object.entries(pluginRegistry).forEach(([slot, plugin]) => {
      this.plugins.set(slot, plugin)
    })
  }

  getPlugin(slotName) {
    return this.plugins.get(slotName)
  }
}

const pluginManagerInstance = PluginManager.getInstance()
export default pluginManagerInstance
