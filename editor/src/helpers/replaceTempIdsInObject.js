import { escapeRegExp } from 'lodash'

import { isTempId } from './isTempId'
import { NEW_OBJECT_ID_PREFIX } from './constants'

// The temp ID format, "temp__12", as a regex source; Conditions add a counter:
// "temp__12_1" (see buildConditionTempId). Defined here only.
export const TEMP_ID_PATTERN = `${escapeRegExp(NEW_OBJECT_ID_PREFIX)}[0-9]+`
const CONDITION_COUNTER_PATTERN = '_[0-9]+'

// A temp ID, as a whole: "temp__12", or the condition temp ID "temp__12_1".
export const TEMP_ID_TOKEN_REGEX = new RegExp(
  `${TEMP_ID_PATTERN}(?:${CONDITION_COUNTER_PATTERN})?`,
  'g'
)

// A condition temp ID, the whole string: "temp__12_1".
export const CONDITION_TEMP_ID_REGEX = new RegExp(
  `^${TEMP_ID_PATTERN}${CONDITION_COUNTER_PATTERN}$`
)

/**
 * Whether `id` is a condition temp ID ("temp__12_1"), not the temp ID of a
 * question, subquestion or group ("temp__12").
 * @param {*} id
 * @returns {boolean}
 */
export const isConditionTempId = (id) =>
  CONDITION_TEMP_ID_REGEX.test(String(id))

/**
 * Builds the temp ID of a new condition: The conditions saved together share
 * a seed and get a counter each.
 * @param {number} seed
 * @param {number} counter
 * @returns {string} e.g. "temp__12_1"
 */
export const buildConditionTempId = (seed, counter) =>
  `${NEW_OBJECT_ID_PREFIX}${seed}_${counter}`

/**
 * Replace the temp IDs of `idMap` in one string. A string that is a temp ID
 * becomes its real ID, temp IDs embedded in it (e.g. cfieldname
 * "Qtemp__12_S34") are replaced as a whole: "temp__12" is not replaced inside
 * "temp__123" or the condition temp ID "temp__12_1".
 * Returns `string` itself when there is nothing to replace.
 *
 * @param {string} string
 * @param {Map<string, number|string>} idMap temp ID => real ID
 * @returns {string|number}
 */
export const replaceTempIdsInString = (string, idMap) => {
  if (idMap.has(string)) return idMap.get(string)
  if (!string.includes(NEW_OBJECT_ID_PREFIX)) return string

  const replaced = string.replace(TEMP_ID_TOKEN_REGEX, (token) =>
    idMap.has(token) ? String(idMap.get(token)) : token
  )
  return replaced === string ? string : replaced
}

/**
 * Replace many temp IDs in one pass (instead of one deep copy per temp ID).
 *
 * Keys and values equal to a temp ID are replaced, as well as temp IDs
 * embedded in strings (see replaceTempIdsInString).
 * Doesn't mutate `value`: Only the objects and arrays containing a replaced
 * temp ID are copied, the others are kept as is (so unchanged parts keep their
 * identity, e.g. for memoized components). Returns `value` itself when there
 * is nothing to replace.
 *
 * @param {*} value
 * @param {Map<string, number|string>} idMap temp ID => real ID
 * @returns {*}
 */
export const replaceTempIdsInObject = (value, idMap) => {
  if (!idMap?.size) return value

  const walk = (current) => {
    if (typeof current === 'string') {
      return replaceTempIdsInString(current, idMap)
    }
    if (current === null || typeof current !== 'object') return current

    if (Array.isArray(current)) {
      let changed = false
      const result = current.map((item) => {
        const replaced = walk(item)
        if (replaced !== item) changed = true
        return replaced
      })
      return changed ? result : current
    }

    let changed = false
    const result = {}
    Object.keys(current).forEach((key) => {
      const newKey = idMap.has(key) ? String(idMap.get(key)) : key
      const replaced = walk(current[key])
      if (newKey !== key || replaced !== current[key]) changed = true
      result[newKey] = replaced
    })
    return changed ? result : current
  }

  return walk(value)
}

export const getTempIdMap = (mappings = []) =>
  new Map(
    (mappings || [])
      .filter(({ tempId }) => isTempId(tempId))
      .map(({ tempId, id }) => [String(tempId), id])
  )
