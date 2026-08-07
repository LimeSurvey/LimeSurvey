import React, { useMemo } from 'react'
import pluginManagerInstance from './PluginManager'

export const PluginSlot = ({ slotName, fallback = null, ...props }) => {
  const manager = useMemo(() => pluginManagerInstance, [])

  const PluginComponent = useMemo(() => manager.getPlugin(slotName), [slotName])

  if (!PluginComponent) {
    return fallback
  }

  return <PluginComponent {...props} />
}
