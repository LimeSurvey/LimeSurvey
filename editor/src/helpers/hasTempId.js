import { NEW_OBJECT_ID_PREFIX } from './constants'

const containsTempId = (value) =>
  typeof value === 'string' && value.includes(NEW_OBJECT_ID_PREFIX)

export const hasTempId = (data, key) => {
  if (typeof data === 'string') {
    return containsTempId(data)
  }

  // eslint-disable-next-line no-console
  if (typeof data === 'object' && data !== null) {
    for (const k in data) {
      if (k === key && containsTempId(data[k])) {
        return true
      }

      if (typeof data[k] === 'object' && hasTempId(data[k], key)) {
        return true
      }
    }
  }

  return false
}
