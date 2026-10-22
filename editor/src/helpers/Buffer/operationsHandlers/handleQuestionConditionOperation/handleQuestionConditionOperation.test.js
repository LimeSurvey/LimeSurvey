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

  describe('keeps the order of the condition operations of a question', () => {
    const condition = (cid, value = 'A1') => ({
      cid,
      qid: 20,
      cqid: 10,
      cfieldname: 'Q10',
      cquestions: 'Q10',
      method: '==',
      value,
      action: 'insertCondition',
    })
    const scenarioUpdate = (scid, conditions, qid = 20) => ({
      id: qid,
      qid,
      entity: 'questionCondition',
      op: Operations.update,
      props: { qid, scenarios: [{ scid, conditions }] },
    })
    const script = (qid = 20) => ({
      id: qid,
      qid,
      entity: 'questionCondition',
      op: Operations.update,
      props: { qid, action: 'conditionScript', script: 'Q10 == "A1"' },
    })
    const scenarioDelete = (scid, qid = 20) => ({
      id: qid,
      qid,
      entity: 'questionCondition',
      op: Operations.delete,
      props: { qid, scenarios: [{ scid, action: 'deleteScenario' }] },
    })
    const conditionDelete = (cid, qid = 20) => ({
      id: cid,
      qid,
      entity: 'questionCondition',
      op: Operations.delete,
      props: {
        qid,
        scenarios: [
          { scid: 1, conditions: [{ cid, action: 'deleteCondition' }] },
        ],
      },
    })

    test('merges scenario changes into the latest update of the question', () => {
      const buffer = [scenarioUpdate(1, [condition('temp__1_1')])]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioUpdate(2, [condition('temp__1_2')]),
        buffer[0]
      )

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations).toHaveLength(1)
      expect(
        result.bufferOperations[0].props.scenarios.map((s) => s.scid)
      ).toEqual([1, 2])
    })

    test('does not merge a scenario change into a pending expression script', () => {
      const buffer = [script()]
      const operation = scenarioUpdate(1, [condition('temp__1_1')])
      const result = handleQuestionConditionOperation(
        buffer,
        operation,
        buffer[0]
      )

      expect(result.bufferOperations).toEqual([script()])
      expect(result.newOperation).toEqual(operation)
      expect(result.addToBuffer).toBe(true)
    })

    test('an expression script drops the pending condition operations of the question only', () => {
      const otherQuestionUpdate = scenarioUpdate(1, [condition(5)], 30)
      const buffer = [
        scenarioUpdate(1, [condition('temp__1_1')]),
        otherQuestionUpdate,
        conditionDelete(7),
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        script(),
        buffer[0]
      )

      expect(result.bufferOperations).toEqual([otherQuestionUpdate])
      expect(result.newOperation).toEqual(script())
      expect(result.addToBuffer).toBe(true)
    })

    test('deleting a scenario keeps the other pending scenarios', () => {
      const buffer = [
        {
          ...scenarioUpdate(1, [condition('temp__1_1')]),
          props: {
            qid: 20,
            scenarios: [
              { scid: 1, conditions: [condition('temp__1_1')] },
              { scid: 2, conditions: [condition('temp__1_2')] },
            ],
          },
        },
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioDelete(2),
        null
      )

      expect(result.bufferOperations).toHaveLength(1)
      expect(
        result.bufferOperations[0].props.scenarios.map((s) => s.scid)
      ).toEqual([1])
      expect(result.addToBuffer).toBe(true)
    })

    test('a scenario re-created after a delete is sent after the delete', () => {
      let buffer = [scenarioUpdate(2, [condition('temp__1_1')])]
      let result = handleQuestionConditionOperation(
        buffer,
        scenarioDelete(1),
        null
      )
      buffer = [...result.bufferOperations, result.newOperation]

      const recreated = scenarioUpdate(1, [condition('temp__2_1')])
      result = handleQuestionConditionOperation(buffer, recreated, buffer[0])

      expect(result.addToBuffer).toBe(true)
      expect(result.newOperation).toEqual(recreated)
      expect(result.bufferOperations).toEqual(buffer)
    })

    test('deleting a condition does not touch operations of a question whose qid equals the cid', () => {
      const questionWithSameId = scenarioUpdate(1, [condition(99)], 7)
      const result = handleQuestionConditionOperation(
        [questionWithSameId],
        conditionDelete(7),
        null
      )

      expect(result.bufferOperations).toEqual([questionWithSameId])
      expect(result.addToBuffer).toBe(true)
    })

    test('deleting a scenario of an unsaved question does not queue a delete', () => {
      const buffer = [
        {
          ...scenarioUpdate(1, [], 'temp__77'),
          props: {
            qid: 'temp__77',
            scenarios: [
              { scid: 1, conditions: [condition('temp__1_1')] },
              { scid: 2, conditions: [condition('temp__1_2')] },
            ],
          },
        },
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioDelete(2, 'temp__77'),
        null
      )

      expect(
        result.bufferOperations[0].props.scenarios.map((s) => s.scid)
      ).toEqual([1])
      expect(result.addToBuffer).toBe(false)
    })

    test('deleting a local-only condition removes it without sending a delete', () => {
      const buffer = [
        scenarioUpdate(1, [condition('temp__1_1'), condition('temp__1_2')]),
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        conditionDelete('temp__1_2'),
        null
      )

      expect(
        result.bufferOperations[0].props.scenarios[0].conditions.map(
          (c) => c.cid
        )
      ).toEqual(['temp__1_1'])
      expect(result.addToBuffer).toBe(false)
    })
  })
})
