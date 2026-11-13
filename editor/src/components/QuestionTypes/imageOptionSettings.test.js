import {
  IMAGE_OPTION_ALT_ATTRIBUTE,
  IMAGE_OPTION_SETTINGS_ATTRIBUTE,
  applyAttributeChanges,
  getImageOptionAltText,
  getImageOptionDeleteChanges,
  getImageOptionRenameChanges,
  getImageOptionResetChanges,
  getImageOptionSaveChanges,
  getImageOptionSettings,
} from './imageOptionSettings'

const settingsA1 = { width: 400, height: 300, zoom: 1 }
const settingsA2 = { width: 200, height: 200, zoom: 2 }

const attributes = {
  other: { '': 'N' },
  [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
    en: JSON.stringify({ A1: settingsA1, A2: settingsA2 }),
    de: JSON.stringify({ A1: settingsA1 }),
  },
  [IMAGE_OPTION_ALT_ATTRIBUTE]: {
    en: JSON.stringify({ A1: 'Lime', A2: 'Lemon' }),
    de: JSON.stringify({ A1: 'Limette' }),
  },
}

const parse = (changes, attributeName, language) =>
  JSON.parse(changes[attributeName][language])

describe('imageOptionSettings', () => {
  test('Reads settings and alt text by code and language', () => {
    expect(getImageOptionSettings(attributes, 'A1', 'en')).toEqual(settingsA1)
    expect(getImageOptionSettings(attributes, 'A2', 'en')).toEqual(settingsA2)
    expect(getImageOptionSettings(attributes, 'A2', 'de')).toBeNull()
    expect(getImageOptionSettings(attributes, 'A3', 'en')).toBeNull()
    expect(getImageOptionAltText(attributes, 'A1', 'de')).toBe('Limette')
    expect(getImageOptionAltText(attributes, 'A2', 'de')).toBe('')
    expect(getImageOptionAltText(attributes, 'A1', 'fr')).toBe('')
  })

  test('Reads nothing from missing or broken attributes', () => {
    expect(getImageOptionSettings({}, 'A1', 'en')).toBeNull()
    expect(getImageOptionSettings(undefined, 'A1', 'en')).toBeNull()
    expect(
      getImageOptionSettings(
        { [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: { en: '{broken' } },
        'A1',
        'en'
      )
    ).toBeNull()
    expect(
      getImageOptionAltText(
        { [IMAGE_OPTION_ALT_ATTRIBUTE]: { en: '' } },
        'A1',
        'en'
      )
    ).toBe('')
  })

  test('Saving stores the option and drops deleted options', () => {
    const newSettings = { width: 100, height: 50, zoom: 1.5 }
    const changes = getImageOptionSaveChanges(attributes, {
      code: 'A1',
      settings: newSettings,
      altText: 'Green lime',
      language: 'en',
      codes: ['A1', 'A3'],
    })

    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A1: newSettings,
    })
    // Other languages keep their settings, without deleted options.
    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'de')).toEqual({
      A1: settingsA1,
    })
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      A1: 'Green lime',
    })
    // Other languages keep their alt texts, without deleted options.
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'de')).toEqual({
      A1: 'Limette',
    })
  })

  test('Saving with "Apply to all" stores the settings of the other options too', () => {
    const changes = getImageOptionSaveChanges(attributes, {
      code: 'A1',
      settings: settingsA1,
      altText: 'Lime',
      language: 'en',
      codes: ['A1', 'A2', 'A3'],
      otherSettings: { A2: settingsA1, A3: settingsA1 },
    })

    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A1: settingsA1,
      A2: settingsA1,
      A3: settingsA1,
    })
    // The alt texts of the other options stay.
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      A1: 'Lime',
      A2: 'Lemon',
    })
  })

  test('Saving without alt text removes it, and works without saved values', () => {
    const changes = getImageOptionSaveChanges(
      {},
      {
        code: 'SQ001',
        settings: settingsA1,
        altText: '',
        language: 'en',
        codes: ['SQ001'],
      }
    )

    expect(changes[IMAGE_OPTION_SETTINGS_ATTRIBUTE]).toEqual({
      en: JSON.stringify({ SQ001: settingsA1 }),
    })
    expect(changes[IMAGE_OPTION_ALT_ATTRIBUTE]).toEqual({ en: '' })
  })

  test('Saving copies the settings to the languages with the same image, the alt text stays in its language', () => {
    const newSettings = { width: 100, height: 50, zoom: 1.5 }
    const changes = getImageOptionSaveChanges(attributes, {
      code: 'A2',
      settings: newSettings,
      altText: 'Green lemon',
      language: 'en',
      codes: ['A1', 'A2'],
      otherSettings: { A1: settingsA2 },
      sameImageLanguages: { A1: [], A2: ['de', 'fr'] },
    })

    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A1: settingsA2,
      A2: newSettings,
    })
    // A1 has another image in German: Its German settings stay.
    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'de')).toEqual({
      A1: settingsA1,
      A2: newSettings,
    })
    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'fr')).toEqual({
      A2: newSettings,
    })
    expect(Object.keys(changes[IMAGE_OPTION_ALT_ATTRIBUTE])).toEqual([
      'en',
      'de',
    ])
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'de')).toEqual({
      A1: 'Limette',
    })
  })

  test('Renaming a code moves its settings and alt texts in all languages', () => {
    const changes = getImageOptionRenameChanges(attributes, 'A1', 'B1')

    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      B1: settingsA1,
      A2: settingsA2,
    })
    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'de')).toEqual({
      B1: settingsA1,
    })
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      B1: 'Lime',
      A2: 'Lemon',
    })
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'de')).toEqual({
      B1: 'Limette',
    })
  })

  test('Renaming only changes the languages that have the code', () => {
    const changes = getImageOptionRenameChanges(attributes, 'A2', 'B2')

    expect(Object.keys(changes[IMAGE_OPTION_ALT_ATTRIBUTE])).toEqual(['en'])
    expect(getImageOptionRenameChanges(attributes, 'A9', 'B9')).toBeNull()
    expect(getImageOptionRenameChanges(attributes, 'A1', 'A1')).toBeNull()
    expect(getImageOptionRenameChanges({}, 'A1', 'B1')).toBeNull()
  })

  test('Renaming step by step through an empty code keeps the settings', () => {
    let current = attributes
    ;[
      ['A1', 'A'],
      ['A', ''],
      ['', 'X'],
    ].forEach(([oldCode, newCode]) => {
      current = applyAttributeChanges(
        current,
        getImageOptionRenameChanges(current, oldCode, newCode)
      )
    })

    expect(getImageOptionSettings(current, 'X', 'de')).toEqual(settingsA1)
    expect(getImageOptionAltText(current, 'X', 'de')).toBe('Limette')
  })

  test('Deleting an option removes its settings and alt texts', () => {
    const changes = getImageOptionDeleteChanges(attributes, 'A1')

    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A2: settingsA2,
    })
    expect(changes[IMAGE_OPTION_SETTINGS_ATTRIBUTE].de).toBe('')
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      A2: 'Lemon',
    })
    // The last option of a language: Stored as an empty value.
    expect(changes[IMAGE_OPTION_ALT_ATTRIBUTE].de).toBe('')
    expect(getImageOptionDeleteChanges(attributes, 'A9')).toBeNull()
  })

  test('A new image removes the settings and the alt text of its language only', () => {
    const changes = getImageOptionResetChanges(attributes, 'A1', 'de')

    expect(changes).toEqual({
      [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: { de: '' },
      [IMAGE_OPTION_ALT_ATTRIBUTE]: { de: '' },
    })
    expect(
      parse(
        getImageOptionResetChanges(attributes, 'A2', 'en'),
        IMAGE_OPTION_SETTINGS_ATTRIBUTE,
        'en'
      )
    ).toEqual({ A1: settingsA1 })
    // Nothing saved in that language: Nothing changes.
    expect(getImageOptionResetChanges(attributes, 'A2', 'de')).toBeNull()
    expect(getImageOptionResetChanges(attributes, 'A9', 'en')).toBeNull()
  })

  test('Applying changes keeps other attributes and languages', () => {
    const updated = applyAttributeChanges(attributes, {
      [IMAGE_OPTION_ALT_ATTRIBUTE]: { de: '' },
    })

    expect(updated.other).toEqual({ '': 'N' })
    expect(updated[IMAGE_OPTION_ALT_ATTRIBUTE]).toEqual({
      en: attributes[IMAGE_OPTION_ALT_ATTRIBUTE].en,
      de: '',
    })
    // The original attributes are not changed.
    expect(attributes[IMAGE_OPTION_ALT_ATTRIBUTE].de).not.toBe('')
  })
})
