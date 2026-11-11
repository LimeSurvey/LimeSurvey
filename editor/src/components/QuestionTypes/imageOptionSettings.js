import { getQuestionTypeInfo } from './getQuestionTypeInfo'

// Per-option image settings of the image select themes, stored as question
// attributes of the parent question. Both hold a JSON object keyed by the option
// code (answer code or subquestion title), so that export, import and copy keep
// them without any ID mapping.
// Not translated: Frame, position, zoom, radius and crop per option.
export const IMAGE_OPTION_SETTINGS_ATTRIBUTE = 'image_option_settings'
// Translated: Alt text per option, one JSON object per language.
export const IMAGE_OPTION_ALT_ATTRIBUTE = 'image_option_alt'

// Language key of attributes that are not translated.
const NO_LANGUAGE = ''

/**
 * Returns the themes that store per-option image settings.
 *
 * @returns {string[]} the theme names
 */
export const getImageSelectThemes = () => [
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
]

/**
 * Parses a stored option map. Invalid or empty values give an empty map.
 *
 * @param {string|undefined} json the stored attribute value
 * @returns {Object} the options keyed by code
 */
const parseOptionMap = (json) => {
  if (!json || typeof json !== 'string') {
    return {}
  }
  try {
    const map = JSON.parse(json)
    return map && typeof map === 'object' && !Array.isArray(map) ? map : {}
  } catch {
    return {}
  }
}

/**
 * Turns an option map into the stored value. An empty map is stored as an
 * empty string.
 *
 * @param {Object} map the options keyed by code
 * @returns {string} the attribute value
 */
const stringifyOptionMap = (map) =>
  Object.keys(map).length ? JSON.stringify(map) : ''

/**
 * Returns the option maps of an attribute for all its languages.
 *
 * @param {Object} attributes the question attributes
 * @param {string} attributeName name of the attribute
 * @returns {Object} the option maps keyed by language
 */
const getOptionMaps = (attributes, attributeName) => {
  const values = attributes?.[attributeName]
  if (!values || typeof values !== 'object') {
    return {}
  }
  return Object.fromEntries(
    Object.entries(values).map(([language, json]) => [
      language,
      parseOptionMap(json),
    ])
  )
}

/**
 * Applies a change to every language of an attribute and returns the changed
 * values only, ready for the attribute update.
 *
 * @param {Object} attributes the question attributes
 * @param {string} attributeName name of the attribute
 * @param {Function} change gets an option map and returns the changed map, or
 *   null when nothing changes
 * @returns {Object} the changed attribute values keyed by language
 */
const changeAllLanguages = (attributes, attributeName, change) => {
  const changes = {}
  Object.entries(getOptionMaps(attributes, attributeName)).forEach(
    ([language, map]) => {
      const changedMap = change(map)
      if (changedMap) {
        changes[language] = stringifyOptionMap(changedMap)
      }
    }
  )
  return changes
}

/**
 * Removes the options that are not in the list of codes.
 *
 * @param {Object} map the options keyed by code
 * @param {string[]|null} codes the codes of the existing options, or null to
 *   keep all
 * @returns {Object} the map without deleted options
 */
const pruneOptionMap = (map, codes) =>
  codes
    ? Object.fromEntries(
        Object.entries(map).filter(([code]) => codes.includes(code))
      )
    : map

/**
 * Drops attributes without changes from an attribute change set.
 *
 * @param {Object} changes the changed values keyed by attribute and language
 * @returns {Object|null} the change set, or null when nothing changed
 */
const toChangeSet = (changes) => {
  const changeSet = Object.fromEntries(
    Object.entries(changes).filter(
      ([, values]) => Object.keys(values).length > 0
    )
  )
  return Object.keys(changeSet).length ? changeSet : null
}

/**
 * Returns the saved image settings of an option.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the option code
 * @returns {Object|null} the settings (see ImageEditor's onSave, without the
 *   alt text), or null when none were saved
 */
export const getImageOptionSettings = (attributes, code) =>
  getOptionMaps(attributes, IMAGE_OPTION_SETTINGS_ATTRIBUTE)[NO_LANGUAGE]?.[
    code
  ] ?? null

/**
 * Returns the saved alt text of an option in a language.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the option code
 * @param {string} language the language code
 * @returns {string} the alt text, or an empty string
 */
export const getImageOptionAltText = (attributes, code, language) =>
  getOptionMaps(attributes, IMAGE_OPTION_ALT_ATTRIBUTE)[language]?.[code] ?? ''

/**
 * Builds the attribute changes for saving the image settings and alt text of
 * one option. Options that no longer exist are removed on the way.
 *
 * @param {Object} attributes the question attributes
 * @param {Object} params
 * @param {string} params.code the option code
 * @param {Object} params.settings the image settings of the option
 * @param {string} params.altText the alt text in the given language
 * @param {string} params.language the language of the alt text
 * @param {string[]} params.codes the codes of all existing options
 * @returns {Object} the changed values keyed by attribute and language
 */
export const getImageOptionSaveChanges = (
  attributes,
  { code, settings, altText, language, codes }
) => {
  const settingsMap = pruneOptionMap(
    getOptionMaps(attributes, IMAGE_OPTION_SETTINGS_ATTRIBUTE)[NO_LANGUAGE] ??
      {},
    codes
  )
  settingsMap[code] = settings

  const altMaps = getOptionMaps(attributes, IMAGE_OPTION_ALT_ATTRIBUTE)
  const altChanges = changeAllLanguages(
    attributes,
    IMAGE_OPTION_ALT_ATTRIBUTE,
    (map) => pruneOptionMap(map, codes)
  )
  const altMap = pruneOptionMap(altMaps[language] ?? {}, codes)
  if (altText) {
    altMap[code] = altText
  } else {
    delete altMap[code]
  }
  altChanges[language] = stringifyOptionMap(altMap)

  return {
    [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
      [NO_LANGUAGE]: stringifyOptionMap(settingsMap),
    },
    [IMAGE_OPTION_ALT_ATTRIBUTE]: altChanges,
  }
}

/**
 * Builds the attribute changes for a renamed option code: Its settings and alt
 * texts move to the new code.
 *
 * @param {Object} attributes the question attributes
 * @param {string} oldCode the code before the change
 * @param {string} newCode the new code
 * @returns {Object|null} the changed values keyed by attribute and language, or
 *   null when the option has no saved settings
 */
export const getImageOptionRenameChanges = (attributes, oldCode, newCode) => {
  if (oldCode === newCode) {
    return null
  }
  const rename = (map) => {
    if (!Object.prototype.hasOwnProperty.call(map, oldCode)) {
      return null
    }
    const { [oldCode]: option, ...rest } = map
    return { ...rest, [newCode]: option }
  }
  return toChangeSet({
    [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: changeAllLanguages(
      attributes,
      IMAGE_OPTION_SETTINGS_ATTRIBUTE,
      rename
    ),
    [IMAGE_OPTION_ALT_ATTRIBUTE]: changeAllLanguages(
      attributes,
      IMAGE_OPTION_ALT_ATTRIBUTE,
      rename
    ),
  })
}

/**
 * Builds the attribute changes for a deleted option: Its settings and alt
 * texts are removed.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the code of the deleted option
 * @returns {Object|null} the changed values keyed by attribute and language, or
 *   null when the option has no saved settings
 */
export const getImageOptionDeleteChanges = (attributes, code) => {
  const remove = (map) => {
    if (!Object.prototype.hasOwnProperty.call(map, code)) {
      return null
    }
    // eslint-disable-next-line no-unused-vars
    const { [code]: removed, ...rest } = map
    return rest
  }
  return toChangeSet({
    [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: changeAllLanguages(
      attributes,
      IMAGE_OPTION_SETTINGS_ATTRIBUTE,
      remove
    ),
    [IMAGE_OPTION_ALT_ATTRIBUTE]: changeAllLanguages(
      attributes,
      IMAGE_OPTION_ALT_ATTRIBUTE,
      remove
    ),
  })
}

/**
 * Builds the attribute changes for a replaced or removed image: The image
 * settings of the option are removed, and so is its alt text in the language
 * of the image, because it describes the old image.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the option code
 * @param {string} language the language whose image changed
 * @returns {Object|null} the changed values keyed by attribute and language, or
 *   null when the option has nothing saved
 */
export const getImageOptionResetChanges = (attributes, code, language) => {
  const changes = getImageOptionDeleteChanges(attributes, code)
  if (!changes) {
    return null
  }
  const altChanges = changes[IMAGE_OPTION_ALT_ATTRIBUTE]
  if (altChanges) {
    Object.keys(altChanges)
      .filter((altLanguage) => altLanguage !== language)
      .forEach((altLanguage) => delete altChanges[altLanguage])
  }
  return toChangeSet(changes)
}

/**
 * Applies attribute changes to the question attributes, keeping the languages
 * that did not change.
 *
 * @param {Object} attributes the question attributes
 * @param {Object} changes the changed values keyed by attribute and language
 * @returns {Object} the updated attributes
 */
export const applyAttributeChanges = (attributes, changes) => {
  const updated = { ...attributes }
  Object.entries(changes).forEach(([attributeName, values]) => {
    updated[attributeName] = {
      ...(attributes?.[attributeName] ?? {}),
      ...values,
    }
  })
  return updated
}
