import { LANGUAGE_CODES } from 'helpers'

export const getAttributeValue = (attribute, language = 'en') => {
  if (typeof attribute !== 'object') {
    return attribute
  } else if (attribute[language] !== undefined) {
    return attribute[language]
  } else if (attribute[''] !== undefined) {
    return attribute['']
  } else if (attribute[LANGUAGE_CODES.EN] !== undefined) {
    return attribute[LANGUAGE_CODES.EN]
  } else if (attribute !== undefined) {
    return attribute
  } else if (typeof attribute === 'object') {
    return ''
  }

  return attribute
}
