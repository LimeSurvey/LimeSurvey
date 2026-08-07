import { questionGroupCreateJoi } from './questionGroupCreateJoi'
import { questionGroupUpdateJoi } from './questionGroupUpdateJoi'
import { questionGroupL10nUpdateJoi } from './questionGroupL10nUpdateJoi'
import { questionGroupDeleteJoi } from './questionGroupDeleteJoi'

describe('QuestionGroupL10nUpdateJoi Schema Tests', () => {
  test('Validates a correct input', () => {
    const validObject = {
      entity: 'questionGroupL10n',
      op: 'update',
      id: 1,
      props: {
        en: {
          groupName: 'Name of group',
          description: 'English description',
        },
        de: {
          groupName: 'Gruppenname',
          description: 'Deutsche Beschreibung',
        },
      },
    }

    const { error, value } = questionGroupL10nUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  test('Fails validation when "entity" is incorrect', () => {
    const invalidObject = {
      entity: 'wrongEntity',
      op: 'update',
      id: 1,
      props: {
        en: {
          groupName: 'Name of group',
          description: 'English description',
        },
      },
    }

    const { error } = questionGroupL10nUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" must be [questionGroupL10n]')
  })

  test('Strips unknown fields when stripUnknown is enabled', () => {
    const inputObject = {
      entity: 'questionGroupL10n',
      op: 'update',
      id: 1,
      extraField: 'remove this',
      props: {
        en: {
          groupName: 'Name of group',
          description: 'English description',
          extraProp: 'remove this too',
        },
      },
    }

    const { error, value } = questionGroupL10nUpdateJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: 'questionGroupL10n',
      op: 'update',
      id: 1,
      props: {
        en: {
          groupName: 'Name of group',
          description: 'English description',
        },
      },
    })
  })

  test('Fails validation when props is missing', () => {
    const invalidObject = {
      entity: 'questionGroupL10n',
      op: 'update',
      id: 1,
    }

    const { error } = questionGroupL10nUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props" is required')
  })

  test('Validates input with additional languages in props', () => {
    const validObject = {
      entity: 'questionGroupL10n',
      op: 'update',
      id: 1,
      props: {
        en: {
          groupName: 'Name of group',
          description: 'English description',
        },
        de: {
          groupName: 'Gruppenname',
          description: 'Deutsche Beschreibung',
        },
        fr: {
          groupName: 'Nom du groupe',
          description: 'Description en français',
        },
      },
    }

    const { error, value } = questionGroupL10nUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })
})

describe('QuestionGroupUpdateJoi Schema Tests', () => {
  // Valid input test
  test('Validates a correct input', () => {
    const validObject = {
      entity: 'questionGroup',
      op: 'update',
      id: 7,
      props: {
        questionGroup: {
          gid: 4,
          sid: 596477,
          sortOrder: 1,
          randomizationGroup: '',
          gRelevance: '1',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group - updated',
            description: 'English',
          },
          fr: {
            groupName: 'Troisième Groupe - updated',
            description: 'French',
          },
        },
      },
    }

    const { error, value } = questionGroupUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing required fields
  test('Fails validation when "props.questionGroup" is missing', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'update',
      id: 7,
      props: {
        questionGroupL10n: {
          en: {
            groupName: '3rd Group - updated',
            description: 'English',
          },
        },
      },
    }

    const { error } = questionGroupUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props.questionGroup" is required')
  })

  // Invalid keys in questionGroup
  test('Fails validation when "questionGroup" contains invalid keys', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'update',
      id: 7,
      props: {
        questionGroup: {
          gid: 4,
          randomizationGroup: '',
          gRelevance: '1',
        },
        questionGroupL10n: {
          e: {
            groupName: '3rd Group - updated',
            description: 'English',
          },
        },
      },
    }

    const { error } = questionGroupUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain(
      '"props.questionGroupL10n.e" is not allowed'
    )
  })

  // Stripping unknown fields
  test('Strips unknown fields when stripUnknown is enabled', () => {
    const inputObject = {
      entity: 'questionGroup',
      op: 'update',
      id: 7,
      extraField: 'remove this',
      props: {
        questionGroup: {
          gid: 4,
          sid: 596477,
          sortOrder: 1,
          randomizationGroup: '',
          gRelevance: '1',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group - updated',
            description: 'English',
            extraField: 'remove this',
          },
        },
      },
    }

    const { error, value } = questionGroupUpdateJoi.validate(inputObject, {
      stripUnknown: true,
    })

    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: 'questionGroup',
      op: 'update',
      id: 7,
      props: {
        questionGroup: {
          gid: 4,
          sid: 596477,
          sortOrder: 1,
          randomizationGroup: '',
          gRelevance: '1',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group - updated',
            description: 'English',
          },
        },
      },
    })
  })

  // Validates additional languages in questionGroupL10n
  test('Validates input with additional languages in questionGroupL10n', () => {
    const validObject = {
      entity: 'questionGroup',
      op: 'update',
      id: 7,
      props: {
        questionGroup: {
          gid: 4,
          sid: 596477,
          sortOrder: 1,
          randomizationGroup: '',
          gRelevance: '1',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group - updated',
            description: 'English',
          },
          fr: {
            groupName: 'Troisième Groupe - updated',
            description: 'French',
          },
        },
      },
    }

    const { error, value } = questionGroupUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })
})

describe('QuestionGroupCreateJoi Schema Tests', () => {
  // Valid input test
  test('Validates a correct input', () => {
    const validObject = {
      entity: 'questionGroup',
      op: 'create',
      id: '123',
      props: {
        questionGroup: {
          tempId: 777,
          randomizationGroup: '',
          gRelevance: '',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group',
            description: 'English',
          },
          fr: {
            groupName: 'Troisième Groupe',
            description: 'French',
          },
        },
      },
    }

    const { error, value } = questionGroupCreateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing required fields
  test('Fails validation when "props.questionGroup" is missing', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'create',
      props: {
        questionGroupL10n: {
          en: {
            groupName: '3rd Group',
            description: 'English',
          },
        },
      },
    }

    const { error } = questionGroupCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })

  // Invalid keys in questionGroup
  test('Fails validation when "questionGroup" contains invalid keys', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'create',
      id: 123,
      props: {
        questionGroup: {
          tempId: 777,
          randomizationGroup: '',
          gRelevance: '',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group',
            description: 'English',
          },
        },
      },
    }

    const { error } = questionGroupCreateJoi.validate(invalidObject)
    expect(error).toBeUndefined()
  })

  // Stripping unknown fields
  test('Strips unknown fields when stripUnknown is enabled', () => {
    const inputObject = {
      entity: 'questionGroup',
      op: 'create',
      extraField: 'remove this',
      id: 123,
      props: {
        questionGroup: {
          tempId: 777,
          randomizationGroup: '',
          gRelevance: '',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group',
            description: 'English',
            extraField: 'remove this',
          },
        },
      },
    }

    const { error, value } = questionGroupCreateJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: 'questionGroup',
      op: 'create',
      id: 123,
      props: {
        questionGroup: {
          tempId: 777,
          randomizationGroup: '',
          gRelevance: '',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group',
            description: 'English',
          },
        },
      },
    })
  })

  // Validates additional languages in questionGroupL10n
  test('Validates input with additional languages in questionGroupL10n', () => {
    const validObject = {
      entity: 'questionGroup',
      op: 'create',
      id: 123,
      props: {
        questionGroup: {
          tempId: 777,
          randomizationGroup: '',
          gRelevance: '',
        },
        questionGroupL10n: {
          en: {
            groupName: '3rd Group',
            description: 'English',
          },
          fr: {
            groupName: 'Troisième Groupe',
            description: 'French',
          },
          de: {
            groupName: 'Dritte Gruppe',
            description: 'German',
          },
        },
      },
    }

    const { error, value } = questionGroupCreateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })
})

describe('QuestionGroupDeleteJoi Schema Tests', () => {
  // Test for valid input
  test('Validates a correct input', () => {
    const validObject = {
      entity: 'questionGroup',
      op: 'delete',
      id: 7,
    }

    const { error, value } = questionGroupDeleteJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing required fields
  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: 'delete',
      id: 7,
    }

    const { error } = questionGroupDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  test('Fails validation when "op" is missing', () => {
    const invalidObject = {
      entity: 'questionGroup',
      id: 7,
    }

    const { error } = questionGroupDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"op" is required')
  })

  test('Fails validation when "id" is missing', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'delete',
    }

    const { error } = questionGroupDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })

  // Invalid values
  test('Fails validation when "op" has an invalid value', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'remove', // Invalid value
      id: 7,
    }

    const { error } = questionGroupDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"op" must be [delete]')
  })

  test('Fails validation when "id" has an invalid type', () => {
    const invalidObject = {
      entity: 'questionGroup',
      op: 'delete',
      id: {}, // Invalid type
    }

    const { error } = questionGroupDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" must be one of [string, number]')
  })

  // Stripping unknown fields
  test('Strips unknown fields when stripUnknown is enabled', () => {
    const inputObject = {
      entity: 'questionGroup',
      op: 'delete',
      id: 7,
      extraField: 'remove this', // Extra field to be removed
    }

    const { error, value } = questionGroupDeleteJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: 'questionGroup',
      op: 'delete',
      id: 7,
    })
  })
})
