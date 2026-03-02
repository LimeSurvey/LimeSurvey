import { questionConditionCreateJoi } from './questionConditionCreateJoi'
import { questionConditionUpdateJoi } from './questionConditionUpdateJoi'
import { questionConditionDeleteJoi } from './questionConditionDeleteJoi'

// Shared test data and utilities
const BASE_CONDITION = {
  qid: 177,
  cqid: 456,
  cfieldname: '766191X1X456',
  cquestions: '766191X1X456',
  method: '==',
  value: 'test',
  scenario: 1,
  editSourceTab: '#SRCPREVQUEST',
}

const BASE_SCENARIO = {
  scid: 1,
  conditions: [BASE_CONDITION],
}

const BASE_PROPS = {
  qid: 177,
  scenarios: [BASE_SCENARIO],
}

const BASE_VALID_OBJECT = {
  entity: 'questionCondition',
  op: 'create',
  id: 177,
  props: BASE_PROPS,
  qid: 177,
}

const CONDITION_TYPES = [
  {
    name: 'Answer condition',
    editTargetTab: '#CANSWERSTAB',
    requiredFields: { canswers: ['answer1'] },
    optionalFields: {
      ConditionConst: undefined,
      prevQuestionSGQA: undefined,
      ConditionRegexp: undefined,
      tokenAttr: undefined,
    },
  },
  {
    name: 'Constant condition',
    editTargetTab: '#CONST',
    requiredFields: { ConditionConst: '123' },
    optionalFields: {
      canswers: undefined,
      prevQuestionSGQA: undefined,
      ConditionRegexp: undefined,
      tokenAttr: undefined,
    },
  },
  {
    name: 'Question condition',
    editTargetTab: '#PREVQUESTIONS',
    requiredFields: { prevQuestionSGQA: '@198895X2X3@' },
    optionalFields: {
      canswers: undefined,
      ConditionConst: undefined,
      ConditionRegexp: undefined,
      tokenAttr: undefined,
    },
  },
  {
    name: 'RegExp condition',
    editTargetTab: '#REGEXP',
    requiredFields: { ConditionRegexp: '/^temp__\\d+_\\d+$/' },
    optionalFields: {
      canswers: undefined,
      ConditionConst: undefined,
      prevQuestionSGQA: undefined,
      tokenAttr: undefined,
    },
  },
  {
    name: 'Token condition',
    editTargetTab: '#TOKENATTRS',
    requiredFields: {
      tokenAttr: '{TOKEN:LASTNAME}',
      prevQuestionSGQA: '766191X1X456',
    },
    optionalFields: {
      canswers: undefined,
      ConditionConst: undefined,
      ConditionRegexp: undefined,
    },
  },
]

describe('QuestionCondition Schemas', () => {
  describe('Create Schema', () => {
    const createBaseCondition = {
      ...BASE_CONDITION,
      action: 'insertCondition',
      editTargetTab: '#CANSWERSTAB',
      tempId: 'temp__124281_1',
      cid: 'temp__124281_1',
      tempcids: ['temp__124281_1'],
      canswers: ['test'],
    }

    const createBaseValidObject = {
      ...BASE_VALID_OBJECT,
      props: {
        ...BASE_PROPS,
        scenarios: [
          {
            ...BASE_SCENARIO,
            conditions: [createBaseCondition],
          },
        ],
      },
    }

    describe('Condition Type Validation', () => {
      CONDITION_TYPES.forEach(
        ({ name, editTargetTab, requiredFields, optionalFields }) => {
          test(`Validates ${name} correctly`, () => {
            const validObject = {
              ...createBaseValidObject,
              props: {
                ...createBaseValidObject.props,
                scenarios: [
                  {
                    ...createBaseValidObject.props.scenarios[0],
                    conditions: [
                      {
                        ...createBaseCondition,
                        editTargetTab,
                        ...requiredFields,
                        ...optionalFields,
                      },
                    ],
                  },
                ],
              },
            }

            const { error, value } =
              questionConditionCreateJoi.validate(validObject)
            expect(error).toBeUndefined()
            expect(value).toEqual(validObject)
          })
        }
      )
    })

    describe('Scenario Validation', () => {
      test('Validates multiple conditions in one scenario', () => {
        const validObject = {
          ...createBaseValidObject,
          props: {
            ...createBaseValidObject.props,
            scenarios: [
              {
                scid: 1,
                conditions: [
                  {
                    ...createBaseCondition,
                    editTargetTab: '#CANSWERSTAB',
                    canswers: ['answer1'],
                  },
                  {
                    ...createBaseCondition,
                    cid: 'temp__124282_1',
                    tempId: 'temp__124282_1',
                    tempcids: ['temp__124282_1'],
                    editTargetTab: '#CONST',
                    ConditionConst: '123',
                    canswers: undefined,
                  },
                ],
              },
            ],
          },
        }

        const { error, value } =
          questionConditionCreateJoi.validate(validObject)
        expect(error).toBeUndefined()
        expect(value).toEqual(validObject)
      })

      test('Validates multiple scenarios', () => {
        const validObject = {
          ...createBaseValidObject,
          props: {
            ...createBaseValidObject.props,
            scenarios: [
              {
                scid: 1,
                conditions: [
                  {
                    ...createBaseCondition,
                    editTargetTab: '#CANSWERSTAB',
                    canswers: ['answer1'],
                  },
                ],
              },
              {
                scid: 2,
                conditions: [
                  {
                    ...createBaseCondition,
                    cid: 'temp__124282_1',
                    tempId: 'temp__124282_1',
                    tempcids: ['temp__124282_1'],
                    scenario: 2,
                    editTargetTab: '#CONST',
                    ConditionConst: '123',
                    canswers: undefined,
                  },
                ],
              },
            ],
          },
        }

        const { error, value } =
          questionConditionCreateJoi.validate(validObject)
        expect(error).toBeUndefined()
        expect(value).toEqual(validObject)
      })
    })

    describe('Error Cases', () => {
      test('Fails when tempId format is invalid', () => {
        const invalidObject = {
          ...createBaseValidObject,
          props: {
            ...createBaseValidObject.props,
            scenarios: [
              {
                scid: 1,
                conditions: [
                  {
                    ...createBaseCondition,
                    tempId: 'invalid_temp_id',
                  },
                ],
              },
            ],
          },
        }

        const { error } = questionConditionCreateJoi.validate(invalidObject)
        expect(error).toBeDefined()
        expect(error.message).toContain('fails to match the required pattern')
        expect(error.message).toContain('tempId')
      })

      test('Fails when method is invalid', () => {
        const invalidObject = {
          ...createBaseValidObject,
          props: {
            ...createBaseValidObject.props,
            scenarios: [
              {
                scid: 1,
                conditions: [
                  {
                    ...createBaseCondition,
                    method: 'invalid',
                  },
                ],
              },
            ],
          },
        }

        const { error } = questionConditionCreateJoi.validate(invalidObject)
        expect(error).toBeDefined()
        expect(error.message).toContain(
          'must be one of [<, >, <=, >=, ==, !=, RX]'
        )
      })
    })
  })

  describe('Update Schema', () => {
    const updateBaseCondition = {
      ...BASE_CONDITION,
      cid: 456,
      action: 'updateCondition',
      editTargetTab: '#CANSWERSTAB',
      canswers: ['test'],
    }

    const updateBaseValidObject = {
      ...BASE_VALID_OBJECT,
      op: 'update',
      id: 123,
      props: {
        ...BASE_PROPS,
        qid: 123,
        scenarios: [
          {
            ...BASE_SCENARIO,
            conditions: [updateBaseCondition],
          },
        ],
      },
    }

    test('Validates update with numeric cid', () => {
      const validObject = {
        ...updateBaseValidObject,
        props: {
          ...updateBaseValidObject.props,
          scenarios: [
            {
              scid: 1,
              conditions: [
                {
                  ...updateBaseCondition,
                  cid: 789,
                },
              ],
            },
          ],
        },
      }

      const { error, value } = questionConditionUpdateJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Validates create with string cid', () => {
      const validObject = {
        ...updateBaseValidObject,
        props: {
          ...updateBaseValidObject.props,
          scenarios: [
            {
              scid: 1,
              conditions: [
                {
                  ...updateBaseCondition,
                  cid: 'temp__124283_1',
                },
              ],
            },
          ],
        },
      }

      const { error, value } = questionConditionUpdateJoi.validate(validObject)
      expect(error).toBeUndefined()
      expect(value).toEqual(validObject)
    })

    test('Allows mixing insert and update actions', () => {
      const validObject = {
        ...updateBaseValidObject,
        props: {
          ...updateBaseValidObject.props,
          scenarios: [
            {
              scid: 1,
              conditions: [
                { ...updateBaseCondition, action: 'updateCondition' },
                {
                  ...updateBaseCondition,
                  cid: 'temp__124281_1',
                  tempId: 'temp__124281_1',
                  tempcids: ['temp__124281_1'],
                  action: 'insertCondition',
                },
              ],
            },
          ],
        },
      }

      const { error } = questionConditionUpdateJoi.validate(validObject)
      expect(error).toBeUndefined()
    })

    test('Fails with empty canswers array', () => {
      const invalidObject = {
        ...updateBaseValidObject,
        props: {
          ...updateBaseValidObject.props,
          scenarios: [
            {
              scid: 1,
              conditions: [
                {
                  ...updateBaseCondition,
                  canswers: [],
                },
              ],
            },
          ],
        },
      }

      const { error } = questionConditionUpdateJoi.validate(invalidObject)
      expect(error).toBeDefined()
    })
  })
})

describe('questionConditionUpdateJoi - conditionScript validation', () => {
  const validData = {
    id: 123,
    op: 'update',
    entity: 'questionCondition',
    qid: 456,
    props: {
      qid: 456,
      action: 'conditionScript',
      script: 'return answer1 == 5;',
    },
  }

  it('should validate correct conditionScript input', () => {
    const { error, value } = questionConditionUpdateJoi.validate(validData)
    expect(error).toBeUndefined()
    expect(value).toMatchObject(validData)
  })

  it('should fail if action is not conditionScript', () => {
    const invalidData = {
      ...validData,
      props: {
        ...validData.props,
        action: 'insertCondition',
      },
    }

    const { error } = questionConditionUpdateJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if script is missing', () => {
    const invalidData = {
      ...validData,
      props: {
        qid: 456,
        action: 'conditionScript',
        // script missing
      },
    }

    const { error } = questionConditionUpdateJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if qid is not a number', () => {
    const invalidData = {
      ...validData,
      props: {
        qid: 'abc',
        action: 'conditionScript',
        script: 'return answer1 == 5;',
      },
    }

    const { error } = questionConditionUpdateJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if extra unknown props are added', () => {
    const invalidData = {
      ...validData,
      props: {
        ...validData.props,
        unknownKey: 'should fail',
      },
    }

    const { error } = questionConditionUpdateJoi.validate(invalidData, {
      allowUnknown: false,
    })
    expect(error).toBeDefined()
  })
})

describe('questionConditionDeleteJoi Schema Validation', () => {
  it('should validate valid data correctly', () => {
    const validData = {
      id: '123',
      op: 'delete',
      entity: 'questionCondition',
      qid: 1,
      props: {
        qid: 2,
        scenarios: [
          {
            scid: 1,
            action: 'deleteScenario',
          },
          {
            scid: 2,
            conditions: [
              {
                cid: 3,
                action: 'deleteCondition',
              },
            ],
          },
        ],
      },
    }

    const { error } = questionConditionDeleteJoi.validate(validData)
    expect(error).toBeUndefined()
  })

  it('should fail when required fields are missing', () => {
    const invalidData = {
      id: '123',
      op: 'delete',
      // Missing entity
      qid: 1,
      props: {
        qid: 2,
        scenarios: [
          {
            scid: 1,
            action: 'deleteScenario',
          },
        ],
      },
    }

    const { error } = questionConditionDeleteJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if "scenarios" contains invalid data', () => {
    const invalidData = {
      id: '123',
      op: 'delete',
      entity: 'questionCondition',
      qid: 1,
      props: {
        qid: 2,
        scenarios: [
          {
            // Invalid scenario without "action" key
            scid: 1,
          },
        ],
      },
    }

    const { error } = questionConditionDeleteJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if "conditions" inside "scenarios" is not an array', () => {
    const invalidData = {
      id: '123',
      op: 'delete',
      entity: 'questionCondition',
      qid: 1,
      props: {
        qid: 2,
        scenarios: [
          {
            scid: 2,
            conditions: 'invalidConditions', // conditions should be an array
          },
        ],
      },
    }

    const { error } = questionConditionDeleteJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if "action" in scenario is not valid', () => {
    const invalidData = {
      id: '123',
      op: 'delete',
      entity: 'questionCondition',
      qid: 1,
      props: {
        qid: 2,
        scenarios: [
          {
            scid: 1,
            action: 'invalidAction', // Invalid action
          },
        ],
      },
    }

    const { error } = questionConditionDeleteJoi.validate(invalidData)
    expect(error).toBeDefined()
  })

  it('should fail if a condition has an invalid action', () => {
    const invalidData = {
      id: '123',
      op: 'delete',
      entity: 'questionCondition',
      qid: 1,
      props: {
        qid: 2,
        scenarios: [
          {
            scid: 2,
            conditions: [
              {
                cid: 3,
                action: 'invalidAction',
              },
            ],
          },
        ],
      },
    }

    const { error } = questionConditionDeleteJoi.validate(invalidData)
    expect(error).toBeDefined()
  })
})
