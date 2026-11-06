import { isTempId } from './isTempId'
import { replaceTempIdsInObject } from './replaceValuesInObject'
import { NEW_OBJECT_ID_PREFIX } from './constants'

// Temp IDs the server has already replaced with real IDs (tempId => id).
// Lets components holding local copies of entities (e.g. an open Condition
// Designer) tell an ID replacement apart from a switch to another entity.
const resolvedTempIds = new Map()

/**
 * Remember the temp ID mappings returned by the survey PATCH response.
 * @param {Array<{tempId: string, id: number}>} mappings
 */
export const rememberResolvedTempIds = (mappings = []) => {
  const list = mappings || []
  list.forEach(({ tempId, id }) => {
    if (isTempId(tempId)) {
      resolvedTempIds.set(String(tempId), id)
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
export const resolveTempIds = (value) => {
  if (!value || !resolvedTempIds.size) return value

  const serialized = JSON.stringify(value)
  if (!serialized?.includes(NEW_OBJECT_ID_PREFIX)) return value

  const resolved = replaceTempIdsInObject(value, resolvedTempIds)

  return JSON.stringify(resolved) === serialized ? value : resolved
}
