import {
  questionCreateJoi,
  questionAttributeUpdateJoi,
  questionDeleteJoi,
  questionL10nUpdateJoi,
  questionUpdateJoi,
} from './'

describe('QuestionCreateJoi Schema Tests', () => {
  test('Validates a correct input with all fields', () => {
    const validObject = {
      entity: 'question',
      op: 'create',
      id: '0',
      props: {
        question: {
          qid: '0',
          title: 'G01Q06',
          type: '1',
          questionThemeName: 'arrays/dualscale',
          gid: '1',
          mandatory: false,
          relevance: '1',
          encrypted: false,
          saveAsDefault: false,
          tempId: 'XXX321',
          sortOrder: '1',
        },
        questionL10n: {
          en: {
            question: 'Array Question',
            help: 'Help text',
          },
          de: {
            question: 'Array ger',
            help: 'help ger',
          },
        },
        attributes: {
          dualscale_headerA: {
            en: 'A',
            de: 'A ger',
          },
          public_statistics: {
            '': '1',
          },
        },
        answers: [
          {
            code: 'AO01',
            sortOrder: 0,
            assessmentValue: 0,
            scaleId: 0,
            tempId: '111',
            l10ns: {
              en: {
                answer: 'answer1',
                language: 'en',
              },
              de: {
                answer: 'antwort1',
                language: 'de',
              },
            },
          },
        ],
        subquestions: [
          {
            title: 'SQ001',
            sortOrder: 0,
            relevance: '1',
            tempId: '113',
            qid: '0',
            gid: '1',
            type: '1',
            scaleId: '0',
            questionThemeName: 'arrays/dualscale',
            parentQid: '0',
            mandatory: true,
            l10ns: {
              en: {
                question: 'sub1',
                language: 'en',
              },
              de: {
                question: 'subger1',
                language: 'de',
              },
            },
          },
        ],
      },
    }

    const { error, value } = questionCreateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Test for missing required fields
  test('Fails validation when "question" is missing in props', () => {
    const invalidObject = {
      entity: 'question',
      op: 'create',
      id: '0',
      props: {
        questionL10n: {
          en: {
            question: 'Array Question',
            help: 'Help text',
          },
        },
      },
    }

    const { error } = questionCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props.question" is required')
  })

  test('Fails validation when "questionL10n" is missing in props', () => {
    const invalidObject = {
      entity: 'question',
      op: 'create',
      id: '0',
      props: {
        question: {
          qid: '0',
          title: 'G01Q06',
          type: '1',
          questionThemeName: 'arrays/dualscale',
          gid: '1',
          mandatory: false,
          relevance: '1',
          encrypted: false,
          saveAsDefault: false,
          tempId: 'XXX321',
          sortOrder: 1,
        },
      },
    }

    const { error } = questionCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props.questionL10n" is required')
  })

  // Test for optional fields
  test('Validates input without "answers" and "subquestions"', () => {
    const validObject = {
      entity: 'question',
      op: 'create',
      id: '0',
      props: {
        question: {
          qid: '0',
          title: 'G01Q06',
          type: '1',
          questionThemeName: 'arrays/dualscale',
          gid: '1',
          mandatory: false,
          relevance: '1',
          encrypted: false,
          saveAsDefault: false,
          tempId: 'XXX321',
          sortOrder: 1,
        },
        questionL10n: {
          en: {
            question: 'Array Question',
            help: 'Help text',
          },
        },
        attributes: {
          dualscale_headerA: {
            en: 'A',
            de: 'A ger',
          },
        },
      },
    }

    const { error, value } = questionCreateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Test for stripping unknown fields
  test('Strips unknown fields from input', () => {
    const inputObject = {
      entity: 'question',
      op: 'create',
      id: '0',
      extraField: 'remove this',
      props: {
        question: {
          qid: '0',
          title: 'G01Q06',
          type: '1',
          questionThemeName: 'arrays/dualscale',
          gid: '1',
          mandatory: false,
          relevance: '1',
          encrypted: false,
          saveAsDefault: false,
          tempId: 'XXX321',
          sortOrder: 1,
        },
        questionL10n: {
          en: {
            question: 'Array Question',
            help: 'Help text',
          },
        },
        attributes: {
          dualscale_headerA: {
            en: 'A',
          },
        },
        extraProps: 'remove this too',
      },
    }

    const { error, value } = questionCreateJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: 'question',
      op: 'create',
      id: '0',
      props: {
        question: {
          qid: '0',
          title: 'G01Q06',
          type: '1',
          questionThemeName: 'arrays/dualscale',
          gid: '1',
          mandatory: false,
          relevance: '1',
          encrypted: false,
          saveAsDefault: false,
          tempId: 'XXX321',
          sortOrder: 1,
        },
        questionL10n: {
          en: {
            question: 'Array Question',
            help: 'Help text',
          },
        },
        attributes: {
          dualscale_headerA: {
            en: 'A',
          },
        },
      },
    })
  })
})

describe('Joi Schema Validation Tests', () => {
  // QuestionCreateJoi Tests
  describe('QuestionCreateJoi Schema Tests', () => {
    test('Validates a correct input with all fields', () => {
      const validObject = {
        entity: 'question',
        op: 'create',
        id: '0',
        props: {
          question: {
            qid: '0',
            title: 'Sample Question',
            type: 'text',
            questionThemeName: 'simple',
            gid: '123',
            mandatory: false,
            relevance: '1',
            encrypted: false,
            saveAsDefault: false,
            sortOrder: 1,
            tempId: 'temp123',
          },
          questionL10n: {
            en: { question: 'Question in English', help: '' },
          },
        },
      }

      const { error, value } = questionCreateJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Strips unknown fields from input when stripUnknown is enabled', () => {
      const inputObject = {
        entity: 'question',
        op: 'create',
        id: '0',
        extraField: 'remove this',
        props: {
          question: {
            qid: '0',
            title: 'Sample Question',
            type: 'text',
            gid: '123',
            mandatory: false,
            relevance: '1',
            encrypted: false,
            saveAsDefault: false,
            sortOrder: 1,
            tempId: 'temp123',
          },
          questionL10n: {
            en: { question: 'Question in English', help: '' },
          },
          extraProps: 'remove this too',
        },
      }

      const { error, value } = questionCreateJoi.validate(inputObject, {
        stripUnknown: true,
      })
      expect(error).toBeUndefined()
      expect(value).toEqual({
        entity: 'question',
        op: 'create',
        id: '0',
        props: {
          question: {
            qid: '0',
            title: 'Sample Question',
            type: 'text',
            gid: '123',
            mandatory: false,
            relevance: '1',
            encrypted: false,
            saveAsDefault: false,
            sortOrder: 1,
            tempId: 'temp123',
          },
          questionL10n: {
            en: { question: 'Question in English', help: '' },
          },
        },
      })
    })
  })

  // QuestionUpdateJoi Tests
  describe('QuestionUpdateJoi Schema Tests', () => {
    test('Validates a correct input', () => {
      const validObject = {
        entity: 'question',
        op: 'update',
        id: 1,
        props: {
          title: 'Updated Title',
          mandatory: true,
        },
      }

      const { error, value } = questionUpdateJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Strips unknown fields from input when stripUnknown is enabled', () => {
      const inputObject = {
        entity: 'question',
        op: 'update',
        id: 1,
        props: {
          title: 'Updated Title',
          mandatory: true,
        },
        extraField: 'remove this too',
      }

      const { value } = questionUpdateJoi.validate(inputObject, {
        stripUnknown: true,
      })
      expect(value).toEqual({
        entity: 'question',
        op: 'update',
        id: 1,
        props: {
          title: 'Updated Title',
          mandatory: true,
        },
      })
    })
  })

  // QuestionL10nUpdateJoi Tests
  describe('QuestionL10nUpdateJoi Schema Tests', () => {
    test('Validates a correct input', () => {
      const validObject = {
        entity: 'questionL10n',
        op: 'update',
        id: 12345,
        props: {
          en: { question: 'Updated Question', help: '' },
        },
      }

      const { error, value } = questionL10nUpdateJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Strips unknown fields from input when stripUnknown is enabled', () => {
      const inputObject = {
        entity: 'questionL10n',
        op: 'update',
        id: 12345,
        props: {
          en: { question: 'Updated Question', help: '', extra: 'remove this' },
        },
      }

      const { error, value } = questionL10nUpdateJoi.validate(inputObject, {
        stripUnknown: true,
      })
      expect(error).toBeUndefined()
      expect(value).toEqual({
        entity: 'questionL10n',
        op: 'update',
        id: 12345,
        props: {
          en: { question: 'Updated Question', help: '' },
        },
      })
    })
  })

  // QuestionDeleteJoi Tests
  describe('QuestionDeleteJoi Schema Tests', () => {
    test('Validates a correct input', () => {
      const validObject = {
        entity: 'question',
        op: 'delete',
        id: 1,
      }

      const { error, value } = questionDeleteJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Strips unknown fields from input when stripUnknown is enabled', () => {
      const inputObject = {
        entity: 'question',
        op: 'delete',
        id: 1,
        extraField: 'remove this',
      }

      const { error, value } = questionDeleteJoi.validate(inputObject, {
        stripUnknown: true,
      })
      expect(error).toBeUndefined()
      expect(value).toEqual({
        entity: 'question',
        op: 'delete',
        id: 1,
      })
    })
  })

  // QuestionAttributeUpdateJoi Tests
  describe('QuestionAttributeUpdateJoi Schema Tests', () => {
    test('Validates a correct input', () => {
      const validObject = {
        entity: 'questionAttribute',
        op: 'update',
        id: 809,
        props: {
          dualscale_headerA: { en: 'Header A', de: 'Header A DE' },
        },
      }

      const { error, value } = questionAttributeUpdateJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Strips unknown fields from input when stripUnknown is enabled', () => {
      const inputObject = {
        entity: 'questionAttribute',
        op: 'update',
        id: 809,
        props: {
          dualscale_headerA: { en: 'Header A' },
        },
        extraField: 'remove this too',
      }

      const { error, value } = questionAttributeUpdateJoi.validate(
        inputObject,
        { stripUnknown: true }
      )

      expect(error).toBeUndefined()
      expect(value).toEqual({
        entity: 'questionAttribute',
        op: 'update',
        id: 809,
        props: {
          dualscale_headerA: { en: 'Header A' },
        },
      })
    })
  })
})
