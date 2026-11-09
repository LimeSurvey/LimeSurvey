import { isTempId } from './isTempId'
import { replaceTempIdsInObject } from './replaceTempIdsInObject'

// Temp IDs the server has already replaced with real IDs (tempId => id).
// Lets components holding local copies of entities (e.g. an open Condition
// Designer) tell an ID replacement apart from a switch to another entity.
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
      resolvedTempIds.set(String(tempId), id)
      version++
    }
  })
}

/**
 * Whether `id` is the real ID that replaced the temp ID `tempId`.
 */
export const isResolvedTempId = (tempId, id) =>
  isTempId(tempId) &&
  resolvedTempIds.has(String(tempId)) &&
  String(resolvedTempIds.get(String(tempId))) === String(id)

/**
 * Replace every already resolved temp ID in `value` with its real ID.
 * Returns `value` itself when there is nothing to replace.
 */
export const resolveTempIds = (value) =>
  replaceTempIdsInObject(value, resolvedTempIds)
