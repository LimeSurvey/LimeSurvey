import { cloneDeep, escapeRegExp } from 'lodash'

import { isTempId } from './isTempId'

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
