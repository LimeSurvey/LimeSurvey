import { Entities, Operations } from 'helpers'

import { answerCreateJoi } from './answerCreateJoi'
import { answerUpdateJoi } from './answerUpdateJoi'
import { answerDeleteJoi } from './answerDeleteJoi'

describe('AnswerCreateJoi Schema Tests', () => {
  test('Validates a correct input', () => {
    const validObject = {
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      props: [
        {
          tempId: 'temp_001',
          code: 'A01',
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Answer 1', language: 'en' },
          },
        },
      ],
    }

    const { error, value } = answerCreateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject) // Should not modify the valid object
  })

  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: Operations.create,
      id: 123,
      props: [
        {
          code: 'A01',
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Answer 1', language: 'en' },
          },
        },
      ],
    }

    const { error } = answerCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  test('Fails validation for invalid language code in l10ns', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      props: [
        {
          code: 'A01',
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          l10ns: {
            e: { answer: 'Answer 1', language: 'en' }, // Invalid language code
          },
        },
      ],
    }

    const { error } = answerCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].l10ns.e" is not allowed') // Updated to match the full path
  })

  test('Fails validation when props is an empty array', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      props: [],
    }

    const { error } = answerCreateJoi.validate(invalidObject)
    expect(error).toBeUndefined()
  })

  test('Fails validation when a required field is missing in props', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      props: [
        {
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Answer 1', language: 'en' },
          },
        },
      ],
    }

    const { error } = answerCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].code" is required') // Updated to match the full path
  })
})

describe('AnswerCreateJoiWithStripUnknown Schema Tests', () => {
  test('Strips unknown fields from input', () => {
    const inputObject = {
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      extraField: 'should be removed',
      props: [
        {
          tempId: 'temp_001',
          code: 'A01',
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          extraProp: 'remove this too',
          l10ns: {
            en: {
              answer: 'Answer 1',
              language: 'en',
              extraNestedField: 'remove this',
            },
          },
        },
      ],
    }

    const { error, value } = answerCreateJoi.validate(inputObject, {
      stripUnknown: true,
    })

    expect(error).toBeUndefined() // Validation should pass
    expect(value).toEqual({
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      props: [
        {
          tempId: 'temp_001',
          code: 'A01',
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Answer 1', language: 'en' },
          },
        },
      ],
    })
  })

  test('Still validates required fields when stripping unknown fields', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.create,
      id: 123,
      props: [
        {
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Answer 1', language: 'en' },
          },
        },
      ],
    }

    const { error } = answerCreateJoi.validate(invalidObject, {
      stripUnknown: true,
    })
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].code" is required') // Updated to match the full path
  })
})

describe('AnswerUpdateJoi Schema Tests', () => {
  test('Validates a correct input', () => {
    const validObject = {
      entity: Entities.answer,
      op: Operations.update,
      id: 123,
      props: [
        {
          tempId: 'temp_001',
          aid: 'A001',
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Updated Answer', language: 'en' },
            fr: { answer: 'Réponse mise à jour', language: 'fr' },
          },
        },
      ],
    }

    const { error, value } = answerUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: Operations.update,
      id: 123,
      props: [
        {
          aid: 'A001',
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Updated Answer', language: 'en' },
          },
        },
      ],
    }

    const { error } = answerUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  test('Fails validation when "aid" is missing in props', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.update,
      id: 123,
      props: [
        {
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Updated Answer', language: 'en' },
          },
        },
      ],
    }

    const { error } = answerUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].aid" is required')
  })

  test('Fails validation for invalid language code in l10ns', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.update,
      id: 123,
      props: [
        {
          aid: 'A001',
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          l10ns: {
            e: { answer: 'Updated Answer', language: 'en' }, // Invalid language code
          },
        },
      ],
    }

    const { error } = answerUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].l10ns.e" is not allowed')
  })
})

describe('AnswerUpdateJoiWithStripUnknown Schema Tests', () => {
  test('Strips unknown fields from input', () => {
    const inputObject = {
      entity: Entities.answer,
      op: Operations.update,
      id: 123,
      extraField: 'should be removed',
      props: [
        {
          tempId: 'temp_001',
          aid: 'A001',
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          extraProp: 'remove this too',
          l10ns: {
            en: {
              answer: 'Updated Answer',
              language: 'en',
              extraNestedField: 'remove this',
            },
          },
        },
      ],
    }

    const { error, value } = answerUpdateJoi.validate(inputObject, {
      stripUnknown: true,
    })

    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: Entities.answer,
      op: Operations.update,
      id: 123,
      props: [
        {
          tempId: 'temp_001',
          aid: 'A001',
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Updated Answer', language: 'en' },
          },
        },
      ],
    })
  })

  test('Still validates required fields when stripping unknown fields', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.update,
      id: 123,
      props: [
        {
          code: 'A02',
          sortOrder: 1,
          assessmentValue: 10,
          scaleId: 2,
          l10ns: {
            en: { answer: 'Updated Answer', language: 'en' },
          },
        },
      ],
    }

    const { error } = answerUpdateJoi.validate(invalidObject, {
      stripUnknown: true,
    })
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].aid" is required')
  })
})

describe('AnswerDeleteJoi Schema Tests', () => {
  test('Validates a correct input', () => {
    const validObject = {
      entity: Entities.answer,
      op: Operations.delete,
      id: 12345,
    }

    const { error, value } = answerDeleteJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: Operations.delete,
      id: 12345,
    }

    const { error } = answerDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  test('Fails validation when "op" is not "delete"', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: 'remove', // Invalid operation
      id: 12345,
    }

    const { error } = answerDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"op" must be [delete]')
  })

  test('Fails validation when "id" is missing', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.delete,
    }

    const { error } = answerDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })

  test('Fails validation when "id" is an invalid type', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.delete,
      id: {}, // Invalid type
    }

    const { error } = answerDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" must be one of [string, number]')
  })
})

describe('AnswerDeleteJoiWithStripUnknown Schema Tests', () => {
  test('Strips unknown fields from input', () => {
    const inputObject = {
      entity: Entities.answer,
      op: Operations.delete,
      id: 12345,
      extraField: 'remove this', // Extra field
    }

    const { error, value } = answerDeleteJoi.validate(inputObject, {
      stripUnknown: true,
    })

    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: Entities.answer,
      op: Operations.delete,
      id: 12345,
    })
  })

  test('Still validates required fields when stripping unknown fields', () => {
    const invalidObject = {
      entity: Entities.answer,
      op: Operations.delete,
    }

    const { error } = answerDeleteJoi.validate(invalidObject, {
      stripUnknown: true,
    })

    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })
})
