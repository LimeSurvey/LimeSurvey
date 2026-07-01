import { merge } from 'lodash'

import { Operations } from 'helpers'
import { handleQuestionOperation } from '../handleQuestionOperation/handleQuestionOperation'
import { handleQuestionConditionOperation } from './handleQuestionConditionOperation'

describe('handleQuestionConditionOperation Tests', () => {
  const questionConditionCreateOperation = {
    id: 108,
    entity: 'questionCondition',
    op: Operations.create,
    props: {
      qid: 108,
      scenarios: [
        {
          scid: 1,
          conditions: [
            {
              qid: 108,
              cqid: 4,
              cfieldname: 'Q108',
              cquestions: 'Q108',
              method: '<',
              value: 'test123',
              scenario: 1,
              action: 'insertCondition',
              editSourceTab: '#SRCPREVQUEST',
              editTargetTab: '#CONST',
              tempId: 'temp__602899_1',
              cid: 'temp__602899_1',
              tempcids: ['temp__602899_1'],
              ConditionConst: 'test123',
            },
          ],
        },
      ],
    },
  }

  const questionConditionCreateOperation2 = {
    id: 109,
    entity: 'questionCondition',
    op: Operations.create,
    props: {
      qid: 109,
      scenarios: [
        {
          scid: 1,
          conditions: [
            {
              qid: 109,
              cqid: 2,
              cfieldname: 'Q109',
              cquestions: 'Q109',
              method: '<',
              value: 'AO602',
              scenario: 1,
              action: 'insertCondition',
              editSourceTab: '#SRCPREVQUEST',
              editTargetTab: '#CANSWERSTAB',
              tempId: 'temp__28946_1',
              cid: 'temp__28946_1',
              tempcids: ['temp__28946_1'],
              canswers: ['AO602'],
            },
            {
              qid: 109,
              cqid: 2,
              cfieldname: 'Q109',
              cquestions: 'Q109',
              method: '<',
              value: 'AO4',
              scenario: 1,
              action: 'insertCondition',
              editSourceTab: '#SRCPREVQUEST',
              editTargetTab: '#CANSWERSTAB',
              tempId: 'temp__28946_2',
              cid: 'temp__28946_2',
              tempcids: ['temp__28946_2'],
              canswers: ['AO4'],
            },
          ],
        },
      ],
    },
  }

  const updateQuestionConditionOperation = {
    id: 110,
    entity: 'questionCondition',
    op: Operations.update,
    props: {
      qid: 110,
      scenarios: [
        {
          scid: 1,
          conditions: [
            {
              qid: 110,
              cqid: 2,
              cfieldname: 'Q110',
              cquestions: 'Q110',
              method: '<',
              value: 'update123',
              scenario: 1,
              action: 'updateCondition',
              editSourceTab: '#SRCPREVQUEST',
              editTargetTab: '#CONST',
              cid: 881,
              ConditionConst: 'update123',
            },
          ],
        },
      ],
    },
  }

  const updateQuestionConditionOperation2 = {
    id: 108,
    entity: 'questionCondition',
    op: Operations.update,
    props: {
      qid: 108,
      action: 'conditionScript',
      script:
        '(((!is_empty(Q766461.NAOK) && (Q766461.NAOK < "AO4")) or (!is_empty(Q766461.NAOK)',
    },
  }

  const deleteConditionOperation = {
    id: 883,
    entity: 'questionCondition',
    op: 'delete',
    qid: 108,
    props: {
      qid: 108,
      scenarios: [
        {
          scid: 1,
          conditions: [
            {
              cid: 883,
              action: 'deleteCondition',
            },
          ],
        },
      ],
    },
  }

  const deleteScenarioOperation = {
    id: 108,
    entity: 'questionCondition',
    op: 'delete',
    qid: 108,
    props: {
      qid: 108,
      scenarios: [
        {
          scid: 1,
          action: 'deleteScenario',
        },
      ],
    },
  }

  test('Handles create condition with value of type Constant', () => {
    const bufferOperations = []
    const result = handleQuestionConditionOperation(
      bufferOperations,
      questionConditionCreateOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(questionConditionCreateOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles create condition with value of type Answer', () => {
    const bufferOperations = []
    const result = handleQuestionConditionOperation(
      bufferOperations,
      questionConditionCreateOperation2,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(questionConditionCreateOperation2)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles update condition', () => {
    const bufferOperations = []
    const result = handleQuestionConditionOperation(
      bufferOperations,
      updateQuestionConditionOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(updateQuestionConditionOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles update condition with expression script', () => {
    const bufferOperations = []
    const result = handleQuestionConditionOperation(
      bufferOperations,
      updateQuestionConditionOperation2,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(updateQuestionConditionOperation2)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles delete condition', () => {
    const bufferOperations = []
    const result = handleQuestionConditionOperation(
      bufferOperations,
      deleteConditionOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(deleteConditionOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles delete scenario', () => {
    const bufferOperations = []
    const result = handleQuestionConditionOperation(
      bufferOperations,
      deleteScenarioOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(deleteScenarioOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Merges props of current and new update operations without adding to buffer when a currentUpdateOperation exists', () => {
    const currentUpdateOperation = {
      id: 112,
      entity: 'questionCondition',
      op: Operations.update,
      props: {
        qid: 112,
        scenarios: [
          {
            scid: 1,
            conditions: [
              {
                qid: 112,
                cqid: 2,
                cfieldname: 'Q112',
                cquestions: 'Q112',
                method: '<',
                value: 'update123',
                scenario: 1,
                action: 'updateCondition',
                editSourceTab: '#SRCPREVQUEST',
                editTargetTab: '#CONST',
                cid: 881,
                ConditionConst: 'update123',
              },
            ],
          },
        ],
      },
    }

    const newUpdateOperation = {
      id: 115,
      entity: 'questionCondition',
      op: Operations.update,
      props: {
        qid: 115,
        scenarios: [
          {
            scid: 1,
            conditions: [
              {
                qid: 115,
                cqid: 2,
                cfieldname: 'Q115',
                cquestions: 'Q115',
                method: '<',
                value: 'update456',
                scenario: 1,
                action: 'updateCondition',
                editSourceTab: '#SRCPREVQUEST',
                editTargetTab: '#CONST',
                cid: 881,
                ConditionConst: 'new update123',
              },
            ],
          },
        ],
      },
    }

    const bufferOperations = [currentUpdateOperation]

    const result = handleQuestionOperation(
      bufferOperations,
      newUpdateOperation,
      currentUpdateOperation
    )

    expect(result.newOperation).toEqual({
      ...currentUpdateOperation,
      props: merge({}, currentUpdateOperation.props, newUpdateOperation.props),
    })

    expect(result.addToBuffer).toBe(false)
    expect(result.bufferOperations).toEqual(bufferOperations)
  })
})
