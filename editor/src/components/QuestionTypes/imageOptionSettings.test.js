import {
  IMAGE_OPTION_ALT_ATTRIBUTE,
  IMAGE_OPTION_SETTINGS_ATTRIBUTE,
  applyAttributeChanges,
  getImageOptionAltText,
  getImageFileName,
  getImageOptionDeleteChanges,
  getImageOptionForImage,
  getImageOptionLanguageMaps,
  getImageOptionRemapChanges,
  getImageOptionRenameChanges,
  getImageOptionResetChanges,
  getImageOptionSaveChanges,
  getImageOptionSettings,
  withImageFileName,
} from './imageOptionSettings'

const imageA1 = 'https://example.org/upload/surveys/1/images/lime.png'
const imageA2 = '/upload/surveys/1/images/lemon.png'
const settingsA1 = { width: 400, height: 300, zoom: 1, image: 'lime.png' }
const settingsA2 = { width: 200, height: 200, zoom: 2, image: 'lemon.png' }

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
    expect(getImageOptionSettings(attributes, 'A1', 'en', imageA1)).toEqual(
      settingsA1
    )
    expect(getImageOptionSettings(attributes, 'A2', 'en', imageA2)).toEqual(
      settingsA2
    )
    expect(getImageOptionSettings(attributes, 'A2', 'de', imageA2)).toBeNull()
    expect(getImageOptionSettings(attributes, 'A3', 'en', imageA1)).toBeNull()
    expect(getImageOptionAltText(attributes, 'A1', 'de', imageA1)).toBe(
      'Limette'
    )
    expect(getImageOptionAltText(attributes, 'A2', 'de', imageA2)).toBe('')
    expect(getImageOptionAltText(attributes, 'A1', 'fr', imageA1)).toBe('')
  })

  test('Settings and alt text saved for another image are ignored', () => {
    const maps = getImageOptionLanguageMaps(attributes, 'en')

    // Another file, e.g. replaced in the classic editor.
    expect(
      getImageOptionForImage(maps, 'A1', '/upload/surveys/1/images/lemon.png')
    ).toEqual({ settings: null, altText: '' })
    // No image.
    expect(getImageOptionForImage(maps, 'A1', '')).toEqual({
      settings: null,
      altText: '',
    })
    // Settings saved without the file name of their image.
    expect(
      getImageOptionForImage(
        { settings: { A1: { width: 400 } }, altTexts: { A1: 'Lime' } },
        'A1',
        imageA1
      )
    ).toEqual({ settings: null, altText: '' })
    // The same file in another folder or on another host, as after import.
    expect(
      getImageOptionForImage(
        maps,
        'A1',
        'https://other.example.com/upload/surveys/2/images/lime.png?v=2'
      )
    ).toEqual({ settings: settingsA1, altText: 'Lime' })
  })

  test('The image file name leaves out folder, query and fragment', () => {
    expect(getImageFileName(imageA1)).toBe('lime.png')
    expect(getImageFileName('/a/b/lime.png?x=1/2#top')).toBe('lime.png')
    expect(getImageFileName('lime.png')).toBe('lime.png')
    expect(getImageFileName('')).toBe('')
    expect(getImageFileName(undefined)).toBe('')
    expect(withImageFileName({ width: 1 }, imageA2)).toEqual({
      width: 1,
      image: 'lemon.png',
    })
  })

  test('Reads nothing from missing or broken attributes', () => {
    expect(getImageOptionSettings({}, 'A1', 'en', imageA1)).toBeNull()
    expect(getImageOptionSettings(undefined, 'A1', 'en', imageA1)).toBeNull()
    expect(
      getImageOptionSettings(
        { [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: { en: '{broken' } },
        'A1',
        'en',
        imageA1
      )
    ).toBeNull()
    expect(
      getImageOptionAltText(
        { [IMAGE_OPTION_ALT_ATTRIBUTE]: { en: '' } },
        'A1',
        'en',
        imageA1
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

    expect(getImageOptionSettings(current, 'X', 'de', imageA1)).toEqual(
      settingsA1
    )
    expect(getImageOptionAltText(current, 'X', 'de', imageA1)).toBe('Limette')
  })

  test('A value without language is never sent back', () => {
    // Stored before the settings were translated.
    const withOldValue = {
      ...attributes,
      [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
        '': JSON.stringify({ A1: { width: 1 } }),
        ...attributes[IMAGE_OPTION_SETTINGS_ATTRIBUTE],
      },
    }
    const saveChanges = getImageOptionSaveChanges(withOldValue, {
      code: 'A1',
      settings: settingsA1,
      altText: 'Lime',
      language: 'en',
      codes: ['A2'],
    })

    expect(
      Object.keys(saveChanges[IMAGE_OPTION_SETTINGS_ATTRIBUTE]).sort()
    ).toEqual(['de', 'en'])
    expect(
      Object.keys(
        getImageOptionRenameChanges(withOldValue, 'A1', 'B1')[
          IMAGE_OPTION_SETTINGS_ATTRIBUTE
        ]
      ).sort()
    ).toEqual(['de', 'en'])
    expect(
      Object.keys(
        getImageOptionDeleteChanges(withOldValue, 'A1')[
          IMAGE_OPTION_SETTINGS_ATTRIBUTE
        ]
      ).sort()
    ).toEqual(['de', 'en'])
  })

  test('Renaming all codes at once moves swapped and shifted codes correctly', () => {
    // A1 and A2 swap.
    const swapped = getImageOptionRemapChanges(attributes, {
      A1: 'A2',
      A2: 'A1',
    })
    expect(parse(swapped, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A2: settingsA1,
      A1: settingsA2,
    })
    expect(parse(swapped, IMAGE_OPTION_ALT_ATTRIBUTE, 'de')).toEqual({
      A2: 'Limette',
    })

    // A1 becomes A2 while A2 becomes A3: Nothing is lost on the way.
    const shifted = getImageOptionRemapChanges(attributes, {
      A1: 'A2',
      A2: 'A3',
    })
    expect(parse(shifted, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A2: settingsA1,
      A3: settingsA2,
    })
    expect(parse(shifted, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      A2: 'Lime',
      A3: 'Lemon',
    })
  })

  test('Renaming codes without saved settings changes nothing', () => {
    expect(getImageOptionRemapChanges(attributes, { A9: '9' })).toBeNull()
    expect(getImageOptionRemapChanges(attributes, { A1: 'A1' })).toBeNull()
    expect(getImageOptionRemapChanges(attributes, {})).toBeNull()
    expect(getImageOptionRemapChanges({}, { A1: '1' })).toBeNull()
    // Only the languages with a renamed option change.
    expect(
      Object.keys(
        getImageOptionRemapChanges(attributes, { A2: '2' })[
          IMAGE_OPTION_SETTINGS_ATTRIBUTE
        ]
      )
    ).toEqual(['en'])
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
