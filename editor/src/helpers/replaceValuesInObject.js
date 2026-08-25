import { cloneDeep } from 'lodash'

export const replaceValuesInObject = (_obj, oldValue, newValue) => {
  const obj = cloneDeep(_obj)
  const oldStr = String(oldValue)

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
    } else if (typeof obj[key] === 'string' && obj[key].includes(oldStr)) {
      // Handle composite strings (e.g. cfieldname "Qtemp__123456_SQ3")
      // where the temp ID is embedded within a larger string
      obj[key] = obj[key].replaceAll(oldStr, String(newValue))
    } else if (typeof obj[key] === 'object' && obj[key] !== null) {
      obj[key] = replaceValuesInObject(obj[key], oldValue, newValue) // Recursive call for nested objects
    }
  })

  return obj
}
