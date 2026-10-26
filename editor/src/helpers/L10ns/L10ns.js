import isString from 'lodash/isString'

export const L10ns = ({ prop, language, l10ns }) => {
  if (
    l10ns?.[language]?.hasOwnProperty(prop) &&
    isString(l10ns[language][prop])
  ) {
    return l10ns[language][prop]
  }

  return ''
}
