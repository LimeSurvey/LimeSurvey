import { Entities, Operations } from 'helpers'

import { subquestionCreateJoi } from './subquestionCreateJoi'
import { subquestionUpdateJoi } from './subquestionUpdateJoi'
import { subquestionDeleteJoi } from './subquestionDeleteJoi'

describe('SubquestionCreateJoi Schema Tests', () => {
  // Valid input test
  test('Validates a correct input with tempId', () => {
    const validObject = {
      id: 4684,
      op: Operations.create,
      entity: 'subquestion',
      props: [
        {
          tempId: 'temp__001',
          sid: 476182,
          qid: 'temp__360280',
          gid: 528,
          type: 'T',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'longfreetext',
          parentQid: 4684,
          title: 'SQ740842',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          moduleName: null,
          sameDefault: null,
          relevance: '1', // Required field added
          sameScript: null,
          l10ns: {
            ar: {
              question: '',
              language: 'ar',
            },
          },
          attributes: [],
          answers: [],
          subquestions: [],
        },
      ],
    }

    const { error, value } = subquestionCreateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Missing "tempId"
  test('Fails validation when "tempId" is missing', () => {
    const invalidObject = {
      id: 4684,
      op: Operations.create,
      entity: 'subquestion',
      props: [
        {
          sid: 476182,
          qid: 'temp__360280',
          gid: 528,
          type: 'T',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'longfreetext',
          parentQid: 4684,
          title: 'SQ740842',
          relevance: '1', // Required field added
          l10ns: {
            ar: {
              question: '',
              language: 'ar',
            },
          },
          other: false,
          mandatory: false,
          encrypted: false,
          attributes: [],
          answers: [],
          subquestions: [],
        },
      ],
    }

    const { error } = subquestionCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].tempId" is required')
  })

  // Invalid "l10ns" language code
  test('Fails validation for invalid language code in l10ns', () => {
    const invalidObject = {
      id: 4684,
      op: Operations.create,
      entity: 'subquestion',
      props: [
        {
          tempId: 'temp__001',
          sid: 476182,
          qid: 'temp__360280',
          gid: 528,
          type: 'T',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'longfreetext',
          parentQid: 4684,
          title: 'SQ740842',
          relevance: '1', // Required field added
          l10ns: {
            a: {
              question: '',
              language: 'ar',
            },
          },
          other: false,
          mandatory: false,
          encrypted: false,
          attributes: [],
          answers: [],
          subquestions: [],
        },
      ],
    }

    const { error } = subquestionCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].l10ns.a" is not allowed')
  })

  // Unknown fields without stripping
  test('Fails validation when unknown fields are present', () => {
    const invalidObject = {
      id: 4684,
      op: Operations.create,
      entity: 'subquestion',
      extraField: 'remove this', // Unknown field
      props: [
        {
          tempId: 'temp__001',
          sid: 476182,
          qid: 'temp__360280',
          gid: 528,
          type: 'T',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'longfreetext',
          parentQid: 4684,
          title: 'SQ740842',
          relevance: '1', // Required field added
          l10ns: {
            ar: {
              question: '',
              language: 'ar',
            },
          },
          other: false,
          mandatory: false,
          encrypted: false,
          attributes: [],
          answers: [],
          subquestions: [],
          extraProp: 'remove this too', // Unknown field
        },
      ],
    }

    const { error } = subquestionCreateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].extraProp" is not allowed')
  })

  // Unknown fields with stripping
  test('Strips unknown fields from input', () => {
    const inputObject = {
      id: 4684,
      op: Operations.create,
      entity: 'subquestion',
      extraField: 'remove this', // Unknown field
      props: [
        {
          tempId: 'temp__001',
          sid: 476182,
          qid: 'temp__360280',
          gid: 528,
          type: 'T',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'longfreetext',
          parentQid: 4684,
          title: 'SQ740842',
          relevance: '1', // Required field added
          l10ns: {
            ar: {
              question: '',
              language: 'ar',
              extraNestedField: 'remove this', // Unknown nested field
            },
          },
          other: false,
          mandatory: false,
          encrypted: false,
          attributes: [],
          answers: [],
          subquestions: [],
          extraProp: 'remove this too',
        },
      ],
    }

    const { error, value } = subquestionCreateJoi.validate(inputObject, {
      stripUnknown: true,
    })

    expect(error).toBeUndefined()
    expect(value).toEqual({
      id: 4684,
      op: Operations.create,
      entity: 'subquestion',
      props: [
        {
          tempId: 'temp__001',
          sid: 476182,
          qid: 'temp__360280',
          gid: 528,
          type: 'T',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'longfreetext',
          parentQid: 4684,
          title: 'SQ740842',
          relevance: '1',
          l10ns: {
            ar: {
              question: '',
              language: 'ar',
            },
          },
          other: false,
          mandatory: false,
          encrypted: false,
          attributes: [],
          answers: [],
          subquestions: [],
        },
      ],
    })
  })
})

describe('SubquestionUpdateJoi Schema Tests', () => {
  test('Validates a correct input with optional tempId', () => {
    const validObject = {
      id: 4684,
      op: Operations.update,
      entity: 'subquestion',
      props: [
        {
          qid: 4690,
          tempId: 'temp__001', // Optional tempId
          parentQid: 4684,
          sid: 476182,
          type: 'T',
          title: 'SQ395',
          preg: null,
          other: false,
          mandatory: null,
          encrypted: false,
          sortOrder: 0,
          scaleId: 0,
          sameDefault: false,
          questionThemeName: null,
          moduleName: null,
          gid: 528,
          relevance: '1',
          sameScript: false,
          l10ns: {
            ar: {
              id: 8288,
              qid: 4690,
              question: '1',
              help: '',
              script: null,
              language: 'ar',
            },
          },
          attributes: [],
          answers: [],
        },
      ],
    }

    const { error, value } = subquestionUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  test('Validates a correct input without tempId', () => {
    const validObject = {
      id: 4684,
      op: Operations.update,
      entity: 'subquestion',
      props: [
        {
          qid: 4690,
          parentQid: 4684,
          sid: 476182,
          type: 'T',
          title: 'SQ395',
          preg: null,
          other: false,
          mandatory: null,
          encrypted: false,
          sortOrder: 0,
          scaleId: 0,
          sameDefault: false,
          questionThemeName: null,
          moduleName: null,
          gid: 528,
          relevance: '1',
          sameScript: false,
          l10ns: {
            ar: {
              id: 8288,
              qid: 4690,
              question: '1',
              help: '',
              script: null,
              language: 'ar',
            },
          },
          attributes: [],
          answers: [],
        },
      ],
    }

    const { error, value } = subquestionUpdateJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  test('Fails validation when "qid" is missing in props', () => {
    const invalidObject = {
      id: 4684,
      op: Operations.update,
      entity: 'subquestion',
      props: [
        {
          parentQid: 4684,
          type: 'T',
          title: 'SQ395',
          relevance: '1',
          l10ns: {
            ar: {
              question: '1',
              language: 'ar',
            },
          },
          attributes: [],
          answers: [],
        },
      ],
    }

    const { error } = subquestionUpdateJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"props[0].qid" is required')
  })

  test('Strips unknown fields from input', () => {
    const inputObject = {
      id: 4684,
      op: Operations.update,
      entity: 'subquestion',
      extraField: 'remove this',
      props: [
        {
          qid: 4690,
          gid: 3,
          type: 'T',
          title: 'SQ395',
          relevance: '1',
          scaleId: 0,
          sortOrder: 1,
          l10ns: {
            ar: {
              question: '1',
              language: 'ar',
            },
          },
          attributes: [],
          answers: [],
          extraProp: 'remove this too',
        },
      ],
    }

    const { error, value } = subquestionUpdateJoi.validate(inputObject, {
      stripUnknown: true,
    })

    expect(error).toBeUndefined()
    expect(value).toEqual({
      id: 4684,
      op: Operations.update,
      entity: 'subquestion',
      props: [
        {
          qid: 4690,
          type: 'T',
          title: 'SQ395',
          relevance: '1',
          gid: 3,
          scaleId: 0,
          sortOrder: 1,
          l10ns: {
            ar: {
              question: '1',
              language: 'ar',
            },
          },
          attributes: [],
          answers: [],
        },
      ],
    })
  })
})

describe('SubquestionDeleteJoi Schema Tests', () => {
  // Test for valid input
  test('Validates a correct input', () => {
    const validObject = {
      entity: Entities.subquestion,
      op: Operations.delete,
      id: 12345,
    }

    const { error, value } = subquestionDeleteJoi.validate(validObject)
    expect(error).toBeUndefined()
    expect(value).toEqual(validObject)
  })

  // Test for missing "entity"
  test('Fails validation when "entity" is missing', () => {
    const invalidObject = {
      op: Operations.delete,
      id: 12345,
    }

    const { error } = subquestionDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"entity" is required')
  })

  // Test for invalid "op"
  test('Fails validation when "op" is not "delete"', () => {
    const invalidObject = {
      entity: Entities.subquestion,
      op: 'remove', // Invalid operation
      id: 12345,
    }

    const { error } = subquestionDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"op" must be [delete]')
  })

  // Test for missing "id"
  test('Fails validation when "id" is missing', () => {
    const invalidObject = {
      entity: Entities.subquestion,
      op: Operations.delete,
    }

    const { error } = subquestionDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })

  // Test for invalid "id" type
  test('Fails validation when "id" is an invalid type', () => {
    const invalidObject = {
      entity: Entities.subquestion,
      op: Operations.delete,
      id: {}, // Invalid type
    }

    const { error } = subquestionDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" must be one of [string, number]')
  })

  // Test with unknown fields without stripping
  test('Fails validation when unknown fields are present', () => {
    const invalidObject = {
      entity: Entities.subquestion,
      op: Operations.delete,
      id: 12345,
      extraField: 'unexpected', // Unknown field
    }

    const { error } = subquestionDeleteJoi.validate(invalidObject)
    expect(error).toBeDefined()
    expect(error.message).toContain('"extraField" is not allowed')
  })

  // Test with unknown fields and stripping enabled
  test('Strips unknown fields from input', () => {
    const inputObject = {
      entity: Entities.subquestion,
      op: Operations.delete,
      id: 12345,
      extraField: 'unexpected', // Unknown field
    }

    const { error, value } = subquestionDeleteJoi.validate(inputObject, {
      stripUnknown: true,
    })
    expect(error).toBeUndefined()
    expect(value).toEqual({
      entity: Entities.subquestion,
      op: Operations.delete,
      id: 12345,
    })
  })

  // Test for required fields with stripping enabled
  test('Still enforces required fields when stripping unknown fields', () => {
    const invalidObject = {
      entity: Entities.subquestion,
      op: Operations.delete,
    }

    const { error } = subquestionDeleteJoi.validate(invalidObject, {
      stripUnknown: true,
    })
    expect(error).toBeDefined()
    expect(error.message).toContain('"id" is required')
  })
})
