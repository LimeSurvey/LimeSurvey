import { NEW_OBJECT_ID_PREFIX } from './constants'

export const isTempId = (id) => {
  return String(id).indexOf(NEW_OBJECT_ID_PREFIX) === 0
}
