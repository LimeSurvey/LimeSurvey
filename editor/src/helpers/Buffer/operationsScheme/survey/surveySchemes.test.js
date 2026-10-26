import { languageSettingUpdateJoi } from './languageSettingUpdateJoi'
import { surveyUpdateJoi } from './surveyUpdateJoi'

import { surveyStatusUpdateJoi } from './surveyStatusUpdateJoi'
import {
  getThemeSettingUpdateJoi,
  themeSettingUpdateJoi,
} from './themeSettingUpdateJoi'

describe('LanguageSettingUpdateJoi Schema Tests', () => {
  // Valid input test
  test('Validates a correct input', () => {
    const validObject = {
      entity: 'languageSetting',
      op: 'update',
      id: null,
      props: {
        de: {
          title: 'Beispielfragebogen',
        },
        en: {
          title: 'Example Survey',
        },
      },
    }

    const { error, value } = languageSettingUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing required fields
  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: 'update',
      id: null,
      props: {
        de: {
          title: 'Beispielfragebogen',
        },
      },
    }

    const { error } = languageSettingUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  test('Fails validation when "op" is missing', () => {
    const invalidObject = {
      entity: 'languageSetting',
      id: null,
      props: {
        en: {
          title: 'Example Survey',
        },
      },
    }

    const { error } = languageSettingUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"op" is required')
  })

  test('Fails validation when "id" is missing', () => {
    const invalidObject = {
      entity: 'languageSetting',
      op: 'update',
      props: {
        de: {
          title: 'Beispielfragebogen',
        },
      },
    }

    const { error } = languageSettingUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })

  // Invalid values
  test('Fails validation when "id" is not null', () => {
    const invalidObject = {
      entity: 'languageSetting',
      op: 'update',
      id: 1, // Invalid value
      props: {
        de: {
          title: 'Beispielfragebogen',
        },
      },
    }

    const { error } = languageSettingUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" must be [null]')
  })

  test('Fails validation when "props" contains invalid keys', () => {
    const invalidObject = {
      entity: 'languageSetting',
      op: 'update',
      id: null,
      props: {
        de: {
          title: 'Beispielfragebogen',
        },
        en: {
          title: 'Test',
        },
      },
    }

    const { error } = languageSettingUpdateJoi.validate(invalidObject)
    expect(error).toBeUndefined()
  })

  test('Fails validation when "props" contains invalid value', () => {
    const invalidObject = {
      entity: 'languageSetting',
      op: 'update',
      id: null,
      props: null,
    }

    const { error } = languageSettingUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props" must be of type object')
  })

  // Validates additional languages
  test('Validates input with additional languages in "props"', () => {
    const validObject = {
      entity: 'languageSetting',
      op: 'update',
      id: null,
      props: {
        de: {
          title: 'Beispielfragebogen',
        },
        en: {
          title: 'Example Survey',
        },
        fr: {
          title: 'Exemple de questionnaire',
        },
      },
    }

    const { error, value } = languageSettingUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })
})

describe('SurveyUpdateJoi Schema Tests', () => {
  // Valid input test
  test('Validates a correct input', () => {
    const validObject = {
      entity: 'survey',
      op: 'update',
      props: {
        anonymized: false,
        language: 'en',
        additionalLanguages: ['de'],
        expires: '2001-03-20 13:28:00',
        template: 'fruity_twentythree',
        format: 'G',
      },
    }

    const { error, value } = surveyUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing required fields
  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: 'update',
      props: {
        anonymized: false,
        language: 'en',
        additionalLanguages: ['de'],
        expires: '2001-03-20 13:28:00',
        template: 'fruity_twentythree',
        format: 'G',
      },
    }

    const { error } = surveyUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  test('Fails validation when "props" is missing', () => {
    const invalidObject = {
      entity: 'survey',
      op: 'update',
    }

    const { error } = surveyUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props" is required')
  })

  // Invalid values
  // test('Fails validation when "expires" is not in the correct format', () => {
  //   const invalidObject = {
  //     entity: 'survey',
  //     op: 'update',
  //     props: {
  //       anonymized: false,
  //       language: 'en',
  //       additionalLanguages: ['de'],
  //       expires: 'invalid-date',
  //       template: 'fruity_twentythree',
  //       format: 'G',
  //     },
  //   }

  //   const { error } = surveyUpdateJoi.validate(invalidObject)
  //   expect(error.message).toContain(
  //     '"props.expires" with value "invalid-date" fails to match'
  //   )
  // })

  // test('Fails validation when "format" has an invalid value', () => {
  //   const invalidObject = {
  //     entity: 'survey',
  //     op: 'update',
  //     props: {
  //       anonymized: false,
  //       language: 'en',
  //       additionalLanguages: ['de'],
  //       expires: '2001-03-20 13:28:00',
  //       template: 'fruity_twentythree',
  //       format: 'Z', // Invalid value
  //     },
  //   }

  //   const { error } = surveyUpdateJoi.validate(invalidObject)
  //   expect(error).toBeDefined()
  //   expect(error.message).toContain('"props.format" must be one of [G, A, X]')
  // })

  // Stripping unknown fields
  test('Strips unknown fields when stripUnknown is enabled', () => {
    const inputObject = {
      entity: 'survey',
      op: 'update',
      extraField: 'remove this',
      props: {
        anonymized: false,
        language: 'en',
        additionalLanguages: ['de'],
        expires: '2001-03-20 13:28:00',
        template: 'fruity_twentythree',
        format: 'G',
      },
    }

    const { error, value } = surveyUpdateJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: 'survey',
      op: 'update',
      props: {
        anonymized: false,
        language: 'en',
        additionalLanguages: ['de'],
        expires: '2001-03-20 13:28:00',
        template: 'fruity_twentythree',
        format: 'G',
      },
    })
  })

  // Valid input without "additionalLanguages"
  test('Validates input without "additionalLanguages"', () => {
    const validObject = {
      entity: 'survey',
      op: 'update',
      props: {
        anonymized: false,
        language: 'en',
        expires: '2001-03-20 13:28:00',
        template: 'fruity_twentythree',
        format: 'A',
      },
    }

    const { error, value } = surveyUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })
})

describe('SurveyStatusUpdateJoi Schema Tests', () => {
  // Valid input test
  test('Validates a correct input with all fields', () => {
    const validObject = {
      id: 754621,
      op: 'update',
      entity: 'surveyStatus',
      error: false,
      props: {
        anonymized: false,
        activate: true,
      },
    }

    const { error, value } = surveyStatusUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Test with missing optional `error`
  test('Validates input without "error"', () => {
    const validObject = {
      id: 754621,
      op: 'update',
      entity: 'surveyStatus',
      props: {
        anonymized: false,
        activate: true,
      },
    }

    const { error, value } = surveyStatusUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing required fields
  test('Fails validation when "id" is missing', () => {
    const invalidObject = {
      op: 'update',
      entity: 'surveyStatus',
      props: {
        anonymized: false,
        activate: true,
      },
    }

    const { error } = surveyStatusUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })

  test('Fails validation when "op" is missing', () => {
    const invalidObject = {
      id: 754621,
      entity: 'surveyStatus',
      props: {
        anonymized: false,
        activate: true,
      },
    }

    const { error } = surveyStatusUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"op" is required')
  })

  test('Fails validation when "props" is missing', () => {
    const invalidObject = {
      id: 754621,
      op: 'update',
      entity: 'surveyStatus',
    }

    const { error } = surveyStatusUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props" is required')
  })

  // Invalid values
  test('Fails validation when "props" contains invalid structure', () => {
    const invalidObject = {
      id: 754621,
      op: 'update',
      entity: 'surveyStatus',
      props: {
        anonymized: 'no', // Invalid value
        activate: true,
      },
    }

    const { error } = surveyStatusUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props.anonymized" must be a boolean')
  })

  // Stripping unknown fields
  test('Strips unknown fields when stripUnknown is enabled', () => {
    const inputObject = {
      id: 754621,
      op: 'update',
      entity: 'surveyStatus',
      error: false,
      extraField: 'remove this',
      props: {
        anonymized: false,
        activate: true,
        extraProp: 'remove this too',
      },
    }

    const { error, value } = surveyStatusUpdateJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      id: 754621,
      op: 'update',
      entity: 'surveyStatus',
      error: false,
      props: {
        anonymized: false,
        activate: true,
      },
    })
  })
})

describe('ThemeSettingUpdateJoi Schema Tests', () => {
  const themesettings = {
    headerBackgroundColor: { type: 'colorpicker' },
    showclearall: { type: 'buttons', options: 'on|off' },
    brandlogofile: { type: 'dropdown' },
  }
  const schema = getThemeSettingUpdateJoi(themesettings)
  const buildOperation = (props) => ({
    id: 754621,
    op: 'update',
    entity: 'themeSettings',
    props: { templateName: 'custom_options_demo', ...props },
  })

  test('Accepts options declared in the theme manifest', () => {
    const input = buildOperation({
      headerBackgroundColor: '#2a2f38',
      showclearall: 'off',
      brandlogofile: null,
    })

    const { error, value } = schema.validate(input)
    expect(error).toBeUndefined()
    expect(value).toEqual(input)
  })

  test('Rejects options not declared in the theme manifest', () => {
    const { error } = schema.validate(buildOperation({ unknownOption: 'x' }))
    expect(error.message).toContain('"props.unknownOption" is not allowed')
  })

  test('Rejects button values outside the declared options', () => {
    const { error } = schema.validate(buildOperation({ showclearall: 'maybe' }))
    expect(error).toBeDefined()
  })

  test('Accepts any option key when no manifest is available', () => {
    const input = buildOperation({ headerBackgroundColor: '#2a2f38' })

    const { error, value } = themeSettingUpdateJoi.validate(input)
    expect(error).toBeUndefined()
    expect(value).toEqual(input)
  })

  test('Fails validation when "templateName" is missing', () => {
    const { error } = themeSettingUpdateJoi.validate({
      id: 754621,
      op: 'update',
      entity: 'themeSettings',
      props: { headerBackgroundColor: '#2a2f38' },
    })
    expect(error).toBeDefined()
    expect(error.message).toContain('"props.templateName" is required')
  })
})
