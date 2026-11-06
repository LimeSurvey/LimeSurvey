import { cloneDeep, escapeRegExp } from 'lodash'

import { isTempId } from './isTempId'
import { NEW_OBJECT_ID_PREFIX } from './constants'

export const replaceValuesInObject = (_obj, oldValue, newValue) => {
  const obj = cloneDeep(_obj)
  // Temp IDs embedded in composite strings (e.g. cfieldname "Qtemp__12_S34").
  // The lookahead avoids replacing "temp__12" inside "temp__123" or the
  // condition temp ID "temp__12_1".
  const embeddedTempIdRegex = isTempId(oldValue)
    ? new RegExp(`${escapeRegExp(String(oldValue))}(?!_?[0-9])`, 'g')
    : null

  // replacing the old key with the new key
  Object.keys(obj).forEach((key) => {
    if (key === oldValue) {
      obj[newValue] = obj[key]
      delete obj[key]
    }

    if (typeof obj[newValue] === 'object' && obj[newValue] !== null) {
      obj[newValue] = replaceValuesInObject(obj[newValue], oldValue, newValue) // Recursive call for nested objects
    }
  })

  // replacing the old value with the new value
  Object.keys(obj).forEach((key) => {
    if (obj[key] === oldValue) {
      obj[key] = newValue
    } else if (embeddedTempIdRegex && typeof obj[key] === 'string') {
      obj[key] = obj[key].replace(embeddedTempIdRegex, String(newValue))
    } else if (typeof obj[key] === 'object' && obj[key] !== null) {
      obj[key] = replaceValuesInObject(obj[key], oldValue, newValue) // Recursive call for nested objects
    }
  })

  return obj
}

const TEMP_ID_TOKEN_REGEX = new RegExp(
  `${escapeRegExp(NEW_OBJECT_ID_PREFIX)}[0-9]+(?:_[0-9]+)?`,
  'g'
)

/**
 * Replace many temp IDs in one pass (instead of one deep copy per temp ID).
 *
 * Keys and values equal to a temp ID are replaced, as well as temp IDs
 * embedded in strings (e.g. cfieldname "Qtemp__12_S34"). A temp ID is only
 * replaced as a whole: "temp__12" is not replaced inside "temp__123" or the
 * condition temp ID "temp__12_1".
 *
 * @param {*} value
 * @param {Map<string, number|string>} idMap temp ID => real ID
 * @returns {*} a copy of `value` with the temp IDs replaced
 */
export const replaceTempIdsInObject = (value, idMap) => {
  if (!idMap?.size) return value

  const replaceString = (string) => {
    if (idMap.has(string)) return idMap.get(string)
    if (!string.includes(NEW_OBJECT_ID_PREFIX)) return string

    return string.replace(TEMP_ID_TOKEN_REGEX, (token) =>
      idMap.has(token) ? String(idMap.get(token)) : token
    )
  }

  const walk = (current) => {
    if (typeof current === 'string') return replaceString(current)
    if (Array.isArray(current)) return current.map(walk)
    if (current === null || typeof current !== 'object') return current

    return Object.keys(current).reduce((result, key) => {
      const newKey = idMap.has(key) ? String(idMap.get(key)) : key
      result[newKey] = walk(current[key])
      return result
    }, {})
  }

  return walk(value)
}

/**
 * Build a temp ID => real ID map from PATCH response mappings.
 * @param {Array<{tempId: string, id: number}>} mappings
 */
export const getTempIdMap = (mappings = []) =>
  new Map(
    (mappings || [])
      .filter(({ tempId }) => isTempId(tempId))
      .map(({ tempId, id }) => [String(tempId), id])
  )
