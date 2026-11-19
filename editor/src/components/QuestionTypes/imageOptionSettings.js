import { getQuestionTypeInfo } from './getQuestionTypeInfo'

// Per-option image settings of the image select themes, stored as question
// attributes of the parent question. Both hold a JSON object keyed by the option
// code (answer code or subquestion title), so that export, import and copy keep
// them without any ID mapping. Both are translated, one JSON object per
// language, because each language can have its own image. The settings also
// hold the file name of the image they were made for (image): When the option
// shows another image, e.g. replaced in the classic editor or a reused code,
// the settings and the alt text no longer apply. Only the file name counts,
// because import rewrites the folder and host of the image URL.
// Frame, position, zoom, radius, crop and image file name per option.
export const IMAGE_OPTION_SETTINGS_ATTRIBUTE = 'image_option_settings'
// Alt text per option.
export const IMAGE_OPTION_ALT_ATTRIBUTE = 'image_option_alt'

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
 * Returns the option maps of an attribute for all its languages. A value
 * without language (stored before the attribute was translated) is left out:
 * It is never read, and the API can't update it together with languages.
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
    Object.entries(values)
      .filter(([language]) => language !== '')
      .map(([language, json]) => [language, parseOptionMap(json)])
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
 * Returns the saved image settings and alt texts of all options in one
 * language. Only that language is parsed: Components that render all options
 * call this once (memoized) and look the options up by code.
 *
 * @param {Object} attributes the question attributes
 * @param {string} language the language code
 * @returns {{settings: Object, altTexts: Object}} the image settings and the
 *   alt texts, each keyed by option code
 */
export const getImageOptionLanguageMaps = (attributes, language) => ({
  settings: parseOptionMap(
    attributes?.[IMAGE_OPTION_SETTINGS_ATTRIBUTE]?.[language]
  ),
  altTexts: parseOptionMap(
    attributes?.[IMAGE_OPTION_ALT_ATTRIBUTE]?.[language]
  ),
})

/**
 * Returns the file name of an image path, without folder, query and fragment.
 * The survey themes compare it the same way, see rows/answer_image.twig.
 *
 * @param {string|undefined} imagePath the image path or URL of an option
 * @returns {string} the file name, or an empty string without a path
 */
export const getImageFileName = (imagePath) =>
  typeof imagePath === 'string'
    ? imagePath.split('?')[0].split('#')[0].split('/').pop().trim()
    : ''

/**
 * Adds the file name of the image to the settings of an option, see
 * getImageOptionForImage().
 *
 * @param {Object} settings the image settings of the option
 * @param {string} imagePath the image path the settings were made for
 * @returns {Object} the settings with the image file name
 */
export const withImageFileName = (settings, imagePath) => ({
  ...settings,
  image: getImageFileName(imagePath),
})

/**
 * Returns the image settings and the alt text of an option from the maps of
 * one language, see getImageOptionLanguageMaps(). Both are only returned when
 * they were saved for the image the option shows now.
 *
 * @param {{settings: Object, altTexts: Object}} maps the maps of the language
 * @param {string} code the option code
 * @param {string|undefined} imagePath the image path the option shows now
 * @returns {{settings: Object|null, altText: string}} the settings (see
 *   ImageEditor's onSave, without the alt text), or null when none apply, and
 *   the alt text, or an empty string
 */
export const getImageOptionForImage = (maps, code, imagePath) => {
  const settings = maps?.settings?.[code]
  const fileName = getImageFileName(imagePath)
  if (!fileName || !settings || settings.image !== fileName) {
    return { settings: null, altText: '' }
  }
  const altText = maps.altTexts?.[code]
  return {
    settings,
    altText: typeof altText === 'string' ? altText : '',
  }
}

/**
 * Returns the saved image settings of an option in a language, when they
 * were saved for the image the option shows now.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the option code
 * @param {string} language the language code
 * @param {string|undefined} imagePath the image path the option shows now
 * @returns {Object|null} the settings (see ImageEditor's onSave, without the
 *   alt text), or null when none apply
 */
export const getImageOptionSettings = (attributes, code, language, imagePath) =>
  getImageOptionForImage(
    getImageOptionLanguageMaps(attributes, language),
    code,
    imagePath
  ).settings

/**
 * Returns the saved alt text of an option in a language, when it was saved
 * for the image the option shows now.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the option code
 * @param {string} language the language code
 * @param {string|undefined} imagePath the image path the option shows now
 * @returns {string} the alt text, or an empty string
 */
export const getImageOptionAltText = (attributes, code, language, imagePath) =>
  getImageOptionForImage(
    getImageOptionLanguageMaps(attributes, language),
    code,
    imagePath
  ).altText

/**
 * Builds the attribute changes for saving the image settings and alt text of
 * one option, and optionally the settings of other options ("Apply to all").
 * The settings are saved for the language, and for the other languages that
 * show the same image for that option. Options that no longer exist are
 * removed on the way. An option whose stored settings belonged to another
 * image loses its alt text in that language too: It described the old image.
 *
 * @param {Object} attributes the question attributes
 * @param {Object} params
 * @param {string} params.code the option code
 * @param {Object} params.settings the image settings of the option, with the
 *   image file name (see withImageFileName())
 * @param {string} params.altText the alt text in the given language
 * @param {string} params.language the language the image was edited in
 * @param {string[]} params.codes the codes of all existing options
 * @param {Object} params.otherSettings image settings of other options keyed
 *   by code, each with its image file name, their alt texts stay as they are
 * @param {Object} params.sameImageLanguages keyed by option code: The other
 *   languages in which the option has the same image as in the language
 * @returns {Object} the changed values keyed by attribute and language
 */
export const getImageOptionSaveChanges = (
  attributes,
  {
    code,
    settings,
    altText,
    language,
    codes,
    otherSettings = {},
    sameImageLanguages = {},
  }
) => {
  const storedSettingsMaps = getOptionMaps(
    attributes,
    IMAGE_OPTION_SETTINGS_ATTRIBUTE
  )
  const settingsMaps = Object.fromEntries(
    Object.entries(storedSettingsMaps).map(([mapLanguage, map]) => [
      mapLanguage,
      pruneOptionMap(map, codes),
    ])
  )
  // Options per language whose settings now belong to another image.
  const reboundCodes = {}
  Object.entries({ ...otherSettings, [code]: settings }).forEach(
    ([optionCode, optionSettings]) => {
      const settingsLanguages = [
        language,
        ...(sameImageLanguages[optionCode] ?? []),
      ]
      settingsLanguages.forEach((settingsLanguage) => {
        const stored = storedSettingsMaps[settingsLanguage]?.[optionCode]
        if (!stored || stored.image !== optionSettings.image) {
          reboundCodes[settingsLanguage] = [
            ...(reboundCodes[settingsLanguage] ?? []),
            optionCode,
          ]
        }
        settingsMaps[settingsLanguage] = {
          ...(settingsMaps[settingsLanguage] ?? {}),
          [optionCode]: optionSettings,
        }
      })
    }
  )

  const altMaps = getOptionMaps(attributes, IMAGE_OPTION_ALT_ATTRIBUTE)
  const altChanges = {}
  new Set([...Object.keys(altMaps), language]).forEach((altLanguage) => {
    const altMap = pruneOptionMap(altMaps[altLanguage] ?? {}, codes)
    ;(reboundCodes[altLanguage] ?? []).forEach(
      (reboundCode) => delete altMap[reboundCode]
    )
    if (altLanguage === language) {
      if (altText) {
        altMap[code] = altText
      } else {
        delete altMap[code]
      }
    }
    altChanges[altLanguage] = stringifyOptionMap(altMap)
  })

  return {
    [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: Object.fromEntries(
      Object.entries(settingsMaps).map(([mapLanguage, map]) => [
        mapLanguage,
        stringifyOptionMap(map),
      ])
    ),
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
 * Builds the attribute changes for several renamed option codes at once, e.g.
 * when all codes are renumbered. All codes move in one step, so codes that
 * swap or shift (A1 → A2 while A2 → A3) don't overwrite each other.
 *
 * @param {Object} attributes the question attributes
 * @param {Object} codeMap the new code keyed by the old code
 * @returns {Object|null} the changed values keyed by attribute and language, or
 *   null when no option with saved settings was renamed
 */
export const getImageOptionRemapChanges = (attributes, codeMap) => {
  const renamed = Object.entries(codeMap ?? {}).filter(
    ([oldCode, newCode]) => oldCode !== newCode
  )
  if (!renamed.length) {
    return null
  }
  const remap = (map) => {
    const moved = renamed.filter(([oldCode]) =>
      Object.prototype.hasOwnProperty.call(map, oldCode)
    )
    if (!moved.length) {
      return null
    }
    const result = { ...map }
    moved.forEach(([oldCode]) => delete result[oldCode])
    moved.forEach(([oldCode, newCode]) => {
      result[newCode] = map[oldCode]
    })
    return result
  }
  return toChangeSet({
    [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: changeAllLanguages(
      attributes,
      IMAGE_OPTION_SETTINGS_ATTRIBUTE,
      remap
    ),
    [IMAGE_OPTION_ALT_ATTRIBUTE]: changeAllLanguages(
      attributes,
      IMAGE_OPTION_ALT_ATTRIBUTE,
      remap
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
 * settings and the alt text of the option are removed in the language of the
 * image, because they belong to the old image. The other languages keep
 * theirs.
 *
 * @param {Object} attributes the question attributes
 * @param {string} code the option code
 * @param {string} language the language whose image changed
 * @returns {Object|null} the changed values keyed by attribute and language, or
 *   null when the option has nothing saved in that language
 */
export const getImageOptionResetChanges = (attributes, code, language) => {
  const changes = getImageOptionDeleteChanges(attributes, code)
  if (!changes) {
    return null
  }
  Object.values(changes).forEach((languageChanges) =>
    Object.keys(languageChanges)
      .filter((changedLanguage) => changedLanguage !== language)
      .forEach((changedLanguage) => delete languageChanges[changedLanguage])
  )
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
