import { merge } from 'lodash'

import { Operations } from 'helpers'
import { handleQuestionOperation } from '../handleQuestionOperation/handleQuestionOperation'
import {
  getKnownTempIds,
  handleQuestionConditionOperation,
  isConditionOperationWaiting,
  removeConditionOpsWithUnknownTempIds,
  removeOrphanConditionDeletes,
} from './handleQuestionConditionOperation'

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

    test('merging drops the older versions of the conditions from the other operations', () => {
      const older = {
        ...scenarioUpdate(1, [condition(5, 'A1'), condition(8)]),
        error: true,
      }
      const last = scenarioUpdate(2, [condition(6)])
      const buffer = [older, scenarioUpdate(1, [condition(5, 'A1')], 30), last]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioUpdate(1, [condition(5, 'A2')])
      )

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations).toEqual([
        {
          ...older,
          props: {
            qid: 20,
            scenarios: [{ scid: 1, conditions: [condition(8)] }],
          },
        },
        buffer[1],
        {
          ...last,
          props: {
            qid: 20,
            scenarios: [
              { scid: 2, conditions: [condition(6)] },
              { scid: 1, conditions: [condition(5, 'A2')] },
            ],
          },
        },
      ])
    })

    test('merging recomputes the target index when an older operation is dropped', () => {
      const older = { ...scenarioUpdate(1, [condition(5, 'A1')]), error: true }
      const last = scenarioUpdate(2, [condition(6)])
      const result = handleQuestionConditionOperation(
        [older, last],
        scenarioUpdate(1, [condition(5, 'A2')])
      )

      expect(result.bufferOperations).toHaveLength(1)
      expect(
        result.bufferOperations[0].props.scenarios.map((s) => s.scid)
      ).toEqual([2, 1])
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

    test('a restored expression script is discarded when a newer script replaces it', () => {
      const newerScript = {
        ...script(),
        props: { ...script().props, script: 'Q10 == "A2"' },
      }
      const buffer = [newerScript]
      const result = handleQuestionConditionOperation(buffer, {
        ...script(),
        error: true,
      })

      expect(result.bufferOperations).toEqual([newerScript])
      expect(result.addToBuffer).toBe(false)
    })

    test('a restored expression script keeps the newer scenario changes and resets the conditions on the server before them', () => {
      const otherQuestionUpdate = scenarioUpdate(1, [condition(5)], 30)
      const newerCreate = {
        ...scenarioUpdate(1, [condition('temp__1_1')]),
        op: Operations.create,
      }
      const buffer = [otherQuestionUpdate, newerCreate]
      const result = handleQuestionConditionOperation(buffer, {
        ...script(),
        error: true,
      })

      expect(result.bufferOperations).toEqual([
        otherQuestionUpdate,
        {
          id: 20,
          qid: 20,
          entity: 'questionCondition',
          op: Operations.delete,
          props: { qid: 20, action: 'deleteAllConditions' },
        },
        newerCreate,
      ])
      expect(result.addToBuffer).toBe(false)
    })

    test('a restored expression script is still added when nothing newer is buffered for the question', () => {
      const otherQuestionUpdate = scenarioUpdate(1, [condition(5)], 30)
      const restored = { ...script(), error: true }
      const result = handleQuestionConditionOperation(
        [otherQuestionUpdate],
        restored
      )

      expect(result.bufferOperations).toEqual([otherQuestionUpdate])
      expect(result.newOperation).toEqual(restored)
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

    test('deleting a temp condition whose create is already being sent queues the delete', () => {
      const otherCondition = scenarioUpdate(1, [condition('temp__1_1')])
      const operation = conditionDelete('temp__1_2')
      const result = handleQuestionConditionOperation(
        [otherCondition],
        operation,
        null
      )

      expect(result.bufferOperations).toEqual([otherCondition])
      expect(result.newOperation).toEqual(operation)
      expect(result.addToBuffer).toBe(true)
      expect(isConditionOperationWaiting(operation)).toBe(true)
    })

    test('removeOrphanConditionDeletes drops a delete left on a temp cid, with the create and edits of its condition', () => {
      const restoredCreate = {
        ...scenarioUpdate(1, [condition('temp__1_2'), condition('temp__1_3')]),
        error: true,
      }
      const otherQuestion = scenarioUpdate(1, [condition(5)], 30)
      const buffer = [
        restoredCreate,
        conditionDelete('temp__1_2'),
        otherQuestion,
        conditionDelete(9),
      ]

      expect(removeOrphanConditionDeletes(buffer)).toEqual([
        {
          ...restoredCreate,
          props: {
            ...restoredCreate.props,
            scenarios: [{ scid: 1, conditions: [condition('temp__1_3')] }],
          },
        },
        otherQuestion,
        conditionDelete(9),
      ])
    })

    test('removeOrphanConditionDeletes keeps the buffer as is without such a delete', () => {
      const buffer = [conditionDelete(9), scenarioUpdate(1, [condition(5)])]
      expect(removeOrphanConditionDeletes(buffer)).toBe(buffer)
    })

    test('editing a condition still waiting to be created keeps it a create', () => {
      const created = {
        ...condition('temp__1_1'),
        tempId: 'temp__1_1',
        tempcids: ['temp__1_1'],
      }
      const buffer = [scenarioUpdate(1, [created])]
      const edited = {
        ...condition('temp__1_1', 'A2'),
        action: 'updateCondition',
      }
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioUpdate(1, [edited]),
        buffer[0]
      )

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations[0].props.scenarios[0].conditions).toEqual([
        {
          ...edited,
          action: 'insertCondition',
          tempId: 'temp__1_1',
          tempcids: ['temp__1_1'],
        },
      ])
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

  describe('edits of conditions whose create is still buffered', () => {
    const create = (cid, value = 'A1', scid = 1) => ({
      cid,
      tempId: cid,
      tempcids: [cid],
      qid: 20,
      cqid: 10,
      cfieldname: 'Q10',
      method: '==',
      value,
      scenario: scid,
      action: 'insertCondition',
    })
    const edit = (cid, value = 'A2', scid = 1) => ({
      cid,
      qid: 20,
      cqid: 10,
      cfieldname: 'Q10',
      method: '==',
      value,
      scenario: scid,
      action: 'updateCondition',
    })
    const scenarioUpdate = (scenarios, qid = 20) => ({
      id: qid,
      qid,
      entity: 'questionCondition',
      op: Operations.update,
      props: { qid, scenarios },
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

    test('are applied to the create even when it is not the latest operation', () => {
      const buffer = [
        scenarioUpdate([{ scid: 1, conditions: [create('temp__1_1')] }]),
        conditionDelete(55),
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioUpdate([{ scid: 1, conditions: [edit('temp__1_1')] }])
      )

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations).toHaveLength(2)
      expect(result.bufferOperations[0].props.scenarios).toEqual([
        {
          scid: 1,
          conditions: [{ ...edit('temp__1_1'), ...create('temp__1_1', 'A2') }],
        },
      ])
    })

    test('move the create to the new scenario', () => {
      const buffer = [
        scenarioUpdate([{ scid: 1, conditions: [create('temp__1_1')] }]),
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioUpdate([{ scid: 2, conditions: [edit('temp__1_1', 'A1', 2)] }])
      )

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations).toHaveLength(1)
      const [scenario] = result.bufferOperations[0].props.scenarios
      expect(result.bufferOperations[0].props.scenarios).toHaveLength(1)
      expect(scenario.scid).toBe(2)
      expect(scenario.conditions).toHaveLength(1)
      expect(scenario.conditions[0]).toMatchObject({
        cid: 'temp__1_1',
        tempId: 'temp__1_1',
        action: 'insertCondition',
        scenario: 2,
      })
    })

    test('a saved condition moved to another scenario leaves its previous one', () => {
      const buffer = [
        scenarioUpdate([{ scid: 1, conditions: [edit(7, 'A1', 1)] }]),
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        scenarioUpdate([{ scid: 2, conditions: [edit(7, 'A1', 2)] }])
      )

      expect(result.bufferOperations[0].props.scenarios).toEqual([
        { scid: 2, conditions: [edit(7, 'A1', 2)] },
      ])
    })

    test('the rest of the operation is still queued', () => {
      const buffer = [
        scenarioUpdate([{ scid: 1, conditions: [create('temp__1_1')] }]),
        conditionDelete(55),
      ]
      const operation = scenarioUpdate([
        { scid: 1, conditions: [edit('temp__1_1'), edit(8)] },
      ])
      const result = handleQuestionConditionOperation(buffer, operation)

      expect(result.addToBuffer).toBe(true)
      expect(result.newOperation.props.scenarios).toEqual([
        { scid: 1, conditions: [edit(8)] },
      ])
    })
  })
})

describe('condition operations waiting for a temp ID', () => {
  const condition = (cid, extra = {}) => ({
    cid,
    qid: 10,
    cqid: 4,
    cfieldname: 'Q4',
    action: 'updateCondition',
    value: 'A1',
    ...extra,
  })
  const scenarioUpdate = (conditions, qid = 10) => ({
    id: qid,
    op: Operations.update,
    entity: 'questionCondition',
    qid,
    props: { qid, scenarios: [{ scid: 1, conditions }] },
  })

  it('detects the operations that must wait', () => {
    expect(isConditionOperationWaiting(scenarioUpdate([condition(7)]))).toBe(
      false
    )
    expect(
      isConditionOperationWaiting(
        scenarioUpdate([condition(7, { cfieldname: 'Q4_Stemp__34' })])
      )
    ).toBe(true)
    expect(
      isConditionOperationWaiting(
        scenarioUpdate([condition(7, { cqid: 'temp__5' })])
      )
    ).toBe(true)
    expect(
      isConditionOperationWaiting(scenarioUpdate([condition('temp__9_1')]))
    ).toBe(true)
    expect(
      isConditionOperationWaiting({
        ...scenarioUpdate([condition(7)]),
        error: true,
      })
    ).toBe(true)
  })

  it('does not merge a change using real IDs into a waiting operation', () => {
    const waiting = scenarioUpdate([
      condition(7, { value: 'old' }),
      condition('temp__1_1', {
        action: 'insertCondition',
        tempId: 'temp__1_1',
        tempcids: ['temp__1_1'],
        cqid: 'temp__5',
        cfieldname: 'Qtemp__5',
      }),
    ])

    const result = handleQuestionConditionOperation(
      [waiting],
      scenarioUpdate([condition(7, { value: 'new' })])
    )

    expect(result.addToBuffer).toBe(true)
    expect(result.newOperation.props.scenarios[0].conditions).toEqual([
      condition(7, { value: 'new' }),
    ])
    // The older version of condition 7 can't overwrite the new one later.
    expect(
      result.bufferOperations[0].props.scenarios[0].conditions.map((c) => c.cid)
    ).toEqual(['temp__1_1'])
  })

  it('still merges operations waiting for the same reason', () => {
    const waiting = scenarioUpdate([condition(7, { cqid: 'temp__5' })])

    const result = handleQuestionConditionOperation(
      [waiting],
      scenarioUpdate([condition(8, { cqid: 'temp__5' })])
    )

    expect(result.addToBuffer).toBe(false)
    expect(
      result.bufferOperations[0].props.scenarios[0].conditions.map((c) => c.cid)
    ).toEqual([7, 8])
  })

  it('removes conditions using a temp ID that no longer exists', () => {
    const survey = {
      questionGroups: [
        {
          gid: 1,
          questions: [
            {
              qid: 4,
              subquestions: [{ qid: 'temp__35' }],
            },
          ],
        },
      ],
    }
    const operations = [
      scenarioUpdate([
        condition(7),
        condition('temp__1_1', {
          action: 'insertCondition',
          tempId: 'temp__1_1',
          cfieldname: 'Q4_Stemp__34',
        }),
        condition('temp__1_2', {
          action: 'insertCondition',
          tempId: 'temp__1_2',
          cfieldname: 'Q4_Stemp__35',
        }),
      ]),
      scenarioUpdate([condition(8)], 'temp__99'),
    ]

    const knownTempIds = getKnownTempIds(survey, operations)
    expect([...knownTempIds]).toEqual(['temp__35'])

    const result = removeConditionOpsWithUnknownTempIds(
      operations,
      knownTempIds
    )
    expect(result).toHaveLength(1)
    expect(result[0].props.scenarios[0].conditions.map((c) => c.cid)).toEqual([
      7,
      'temp__1_2',
    ])
  })

  it('knows the temp IDs of entities only in the buffer', () => {
    const operations = [
      {
        id: 4,
        op: Operations.update,
        entity: 'subquestion',
        props: [{ qid: 'temp__36', parentQid: 4 }],
      },
    ]
    expect(
      getKnownTempIds({ questionGroups: [] }, operations).has('temp__36')
    ).toBe(true)
  })

  it('only looks for the wanted temp IDs, and stops once they are found', () => {
    const survey = {
      questionGroups: [
        { gid: 1, questions: [{ qid: 'temp__5' }, { qid: 'temp__6' }] },
      ],
    }
    const operation = {
      id: 4,
      op: Operations.update,
      entity: 'subquestion',
      props: [{ qid: 'temp__36', parentQid: 4 }],
    }
    const stringify = jest.spyOn(JSON, 'stringify')

    const found = getKnownTempIds(survey, [operation], new Set(['temp__5']))

    expect([...found]).toEqual(['temp__5'])
    expect(stringify).not.toHaveBeenCalled()

    const missing = getKnownTempIds(
      survey,
      [operation],
      new Set(['temp__36', 'temp__99'])
    )

    expect([...missing]).toEqual(['temp__36'])
    stringify.mockRestore()
  })
})

describe('condition operations put back after a failed request', () => {
  const condition = (cid, extra = {}) => ({
    cid,
    qid: 10,
    cqid: 4,
    cfieldname: 'Q4',
    action: 'updateCondition',
    value: 'A1',
    ...extra,
  })
  const createCondition = (cid, extra = {}) =>
    condition(cid, {
      action: 'insertCondition',
      tempId: cid,
      tempcids: [cid],
      ...extra,
    })
  const scenarioUpdate = (scenarios, extra = {}) => ({
    id: 10,
    op: Operations.update,
    entity: 'questionCondition',
    qid: 10,
    props: { qid: 10, scenarios },
    ...extra,
  })

  it('keeps the newer buffered edit of the same condition', () => {
    const newer = scenarioUpdate([
      { scid: 1, conditions: [condition(7, { value: 'new' })] },
    ])
    const restored = scenarioUpdate(
      [{ scid: 1, conditions: [condition(7, { value: 'old' })] }],
      { error: true }
    )

    const result = handleQuestionConditionOperation([newer], restored)

    expect(result.addToBuffer).toBe(false)
    expect(result.bufferOperations).toHaveLength(1)
    expect(result.bufferOperations[0].props.scenarios).toEqual([
      { scid: 1, conditions: [condition(7, { value: 'new' })] },
    ])
  })

  it('applies a newer edit to a restored create, which stays a create', () => {
    const newer = scenarioUpdate([
      { scid: 1, conditions: [condition('temp__1_1', { value: 'new' })] },
    ])
    const restored = scenarioUpdate(
      [{ scid: 1, conditions: [createCondition('temp__1_1')] }],
      { error: true }
    )

    const result = handleQuestionConditionOperation([newer], restored)

    expect(result.bufferOperations[0].props.scenarios[0].conditions).toEqual([
      createCondition('temp__1_1', { value: 'new' }),
    ])
  })

  it('does not bring back a scenario deleted meanwhile', () => {
    const scenarioDelete = {
      id: 10,
      op: Operations.delete,
      entity: 'questionCondition',
      qid: 10,
      props: { qid: 10, scenarios: [{ scid: 2, action: 'deleteScenario' }] },
    }
    const restored = scenarioUpdate(
      [
        { scid: 1, conditions: [createCondition('temp__1_1')] },
        { scid: 2, conditions: [createCondition('temp__1_2')] },
      ],
      { error: true }
    )

    const result = handleQuestionConditionOperation([scenarioDelete], restored)

    expect(result.addToBuffer).toBe(true)
    expect(result.newOperation.props.scenarios).toEqual([
      { scid: 1, conditions: [createCondition('temp__1_1')] },
    ])
  })

  it('does not bring back a condition deleted meanwhile', () => {
    const conditionDelete = {
      id: 7,
      op: Operations.delete,
      entity: 'questionCondition',
      qid: 10,
      props: {
        qid: 10,
        scenarios: [
          { scid: 1, conditions: [{ cid: 7, action: 'deleteCondition' }] },
        ],
      },
    }
    const restored = scenarioUpdate([{ scid: 1, conditions: [condition(7)] }], {
      error: true,
    })

    const result = handleQuestionConditionOperation([conditionDelete], restored)

    expect(result.addToBuffer).toBe(false)
    expect(result.bufferOperations).toEqual([conditionDelete])
  })
})
