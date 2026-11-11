import { isTempId } from './isTempId'
import { replaceTempIdsInObject } from './replaceTempIdsInObject'

// Temp IDs the server has already replaced with real IDs
// (tempId => { id, version }, the version being the one the mapping arrived
// with). Lets components holding local copies of entities (e.g. an open
// Condition Designer) tell an ID replacement apart from a switch to another
// entity.
// Temp IDs are random numbers, so a new entity can get a temp ID that was
// already resolved in this session. A mapping must therefore only be applied
// to data created before it arrived (see `sinceVersion` below): Data created
// later with the same temp ID is about another, still unsaved entity.
const resolvedTempIds = new Map()

// Incremented on every change of the map, so callers can skip resolving again
// when no new mapping arrived.
let version = 0

export const getResolvedTempIdsVersion = () => version

/**
 * Forget every mapping, e.g. when switching to another survey.
 */
export const clearResolvedTempIds = () => {
  if (!resolvedTempIds.size) return
  resolvedTempIds.clear()
  version++
}

/**
 * Remember the temp ID mappings returned by the survey PATCH response.
 * @param {Array<{tempId: string, id: number}>} mappings
 */
export const rememberResolvedTempIds = (mappings = []) => {
  const list = mappings || []
  list.forEach(({ tempId, id }) => {
    if (isTempId(tempId)) {
      resolvedTempIds.set(String(tempId), { id, version: version + 1 })
      version++
    }
  })
}

// The mappings that arrived after `sinceVersion` (tempId => id).
const getMappingsSince = (sinceVersion) => {
  const idMap = new Map()
  resolvedTempIds.forEach((mapping, tempId) => {
    if (mapping.version > sinceVersion) idMap.set(tempId, mapping.id)
  })
  return idMap
}

/**
 * Whether `id` is the real ID that replaced the temp ID `tempId`.
 * @param {number|string} tempId
 * @param {number|string} id
 * @param {number} [sinceVersion] the version (getResolvedTempIdsVersion) at
 *   which `tempId` was read: Older mappings are about another entity
 * @returns {boolean}
 */
export const isResolvedTempId = (tempId, id, sinceVersion = 0) => {
  const mapping = isTempId(tempId) && resolvedTempIds.get(String(tempId))

  return (
    Boolean(mapping) &&
    mapping.version > sinceVersion &&
    String(mapping.id) === String(id)
  )
}

/**
 * Replace the resolved temp IDs in `value` with their real IDs.
 * Returns `value` itself when there is nothing to replace.
 * @param {*} value
 * @param {number} [sinceVersion] the version (getResolvedTempIdsVersion) at
 *   which `value` was created or last resolved: Only the mappings that
 *   arrived since are applied
 * @returns {*}
 */
export const resolveTempIds = (value, sinceVersion = 0) =>
  replaceTempIdsInObject(value, getMappingsSince(sinceVersion))
