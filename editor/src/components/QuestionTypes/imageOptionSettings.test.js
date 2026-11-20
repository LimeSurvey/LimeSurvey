import {
  IMAGE_OPTION_ALT_ATTRIBUTE,
  IMAGE_OPTION_DEFAULTS_ATTRIBUTE,
  IMAGE_OPTION_SETTINGS_ATTRIBUTE,
  applyAttributeChanges,
  getImageOptionAltText,
  getImageOptionDefaults,
  getImageOptionDefaultsChanges,
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

  test('Apply to all drops the old alt text of an option whose image changed', () => {
    // A2 got another image in the classic editor: Its settings and its alt
    // text "Lemon" still belong to lemon.png.
    const changes = getImageOptionSaveChanges(attributes, {
      code: 'A1',
      settings: settingsA1,
      altText: 'Lime',
      language: 'en',
      codes: ['A1', 'A2'],
      otherSettings: { A2: { width: 400, image: 'orange.png' } },
    })

    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      A1: 'Lime',
    })
    // An option that still shows its image keeps its alt text.
    const sameImage = getImageOptionSaveChanges(attributes, {
      code: 'A1',
      settings: settingsA1,
      altText: 'Lime',
      language: 'en',
      codes: ['A1', 'A2'],
      otherSettings: { A2: { width: 400, image: 'lemon.png' } },
    })
    // Its alt texts stay as they are: Nothing to send.
    expect(sameImage[IMAGE_OPTION_ALT_ATTRIBUTE]).toBeUndefined()
    // Languages without new settings for A2 keep their alt texts: Unchanged
    // languages are not sent.
    expect(changes[IMAGE_OPTION_ALT_ATTRIBUTE].de).toBeUndefined()
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
    // German has no deleted options: Its settings don't change and are not
    // sent.
    expect(changes[IMAGE_OPTION_SETTINGS_ATTRIBUTE].de).toBeUndefined()
    expect(parse(changes, IMAGE_OPTION_ALT_ATTRIBUTE, 'en')).toEqual({
      A1: 'Green lime',
    })
    expect(changes[IMAGE_OPTION_ALT_ATTRIBUTE].de).toBeUndefined()
  })

  test('Saving with "Apply to all" stores the settings of the other options too', () => {
    const changes = getImageOptionSaveChanges(attributes, {
      code: 'A1',
      settings: settingsA1,
      altText: 'Lime',
      language: 'en',
      codes: ['A1', 'A2', 'A3'],
      // Each option gets the settings with its own image file name.
      otherSettings: {
        A2: { ...settingsA1, image: 'lemon.png' },
        A3: { ...settingsA1, image: 'orange.png' },
      },
    })

    expect(parse(changes, IMAGE_OPTION_SETTINGS_ATTRIBUTE, 'en')).toEqual({
      A1: settingsA1,
      A2: { ...settingsA1, image: 'lemon.png' },
      A3: { ...settingsA1, image: 'orange.png' },
    })
    // The alt texts of the other options stay: The alt texts don't change.
    expect(changes[IMAGE_OPTION_ALT_ATTRIBUTE]).toBeUndefined()
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
    // No alt text before and after: Nothing to send.
    expect(changes[IMAGE_OPTION_ALT_ATTRIBUTE]).toBeUndefined()
  })

  test('Saving only sends the languages that change, and nothing when nothing changes', () => {
    const newSettings = { width: 100, height: 50, zoom: 1.5, image: 'lime.png' }
    const changes = getImageOptionSaveChanges(attributes, {
      code: 'A1',
      settings: newSettings,
      altText: 'Lime',
      language: 'de',
      codes: ['A1', 'A2'],
    })

    expect(changes).toEqual({
      [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
        de: JSON.stringify({ A1: newSettings }),
      },
      [IMAGE_OPTION_ALT_ATTRIBUTE]: { de: JSON.stringify({ A1: 'Lime' }) },
    })
    // The same settings and alt text again: Nothing to send.
    expect(
      getImageOptionSaveChanges(attributes, {
        code: 'A1',
        settings: settingsA1,
        altText: 'Lime',
        language: 'en',
        codes: ['A1', 'A2'],
      })
    ).toBeNull()
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
    // Only the English alt text changes.
    expect(Object.keys(changes[IMAGE_OPTION_ALT_ATTRIBUTE])).toEqual(['en'])
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

  test('The settings for new images keep frame, zoom and radius only, for all languages', () => {
    const changes = getImageOptionDefaultsChanges({
      ...settingsA1,
      keepRatio: true,
      radius: 8,
      positionX: 0.2,
      positionY: 0.8,
      crop: { x: 0, y: 0, width: 1, height: 1 },
    })

    expect(Object.keys(changes[IMAGE_OPTION_DEFAULTS_ATTRIBUTE])).toEqual([''])
    const defaults = {
      width: 400,
      height: 300,
      keepRatio: true,
      zoom: 1,
      radius: 8,
    }
    expect(parse(changes, IMAGE_OPTION_DEFAULTS_ATTRIBUTE, '')).toEqual(
      defaults
    )
    expect(
      getImageOptionDefaults(applyAttributeChanges(attributes, changes))
    ).toEqual(defaults)
  })

  test('Removed, missing or broken settings for new images read as none', () => {
    const removed = applyAttributeChanges(
      attributes,
      getImageOptionDefaultsChanges(null)
    )

    expect(removed[IMAGE_OPTION_DEFAULTS_ATTRIBUTE]).toEqual({ '': '' })
    expect(getImageOptionDefaults(removed)).toBeNull()
    expect(getImageOptionDefaults(attributes)).toBeNull()
    expect(getImageOptionDefaults(undefined)).toBeNull()
    expect(
      getImageOptionDefaults({
        [IMAGE_OPTION_DEFAULTS_ATTRIBUTE]: { '': '{"width":200' },
      })
    ).toBeNull()
    expect(
      getImageOptionDefaults({
        [IMAGE_OPTION_DEFAULTS_ATTRIBUTE]: {
          '': '{"width":"200","height":100,"zoom":1}',
        },
      })
    ).toBeNull()
  })
})
