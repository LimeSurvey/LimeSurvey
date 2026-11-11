import { Operations } from 'helpers'
import {
  getKnownTempIds,
  handleQuestionConditionOperation,
  hasUnansweredConditionCreate,
  isConditionOperationWaiting,
  removeConditionOperationsOfQuestions,
  removeConditionOpsWithUnknownTempIds,
  removeConditionOpsOfFailedCreates,
} from './handleQuestionConditionOperation'

const QID = 20

const condition = (cid, extra = {}) => ({
  cid,
  qid: QID,
  cqid: 10,
  cfieldname: 'Q10',
  cquestions: 'Q10',
  method: '==',
  value: 'A1',
  action: 'updateCondition',
  ...extra,
})

const newCondition = (cid, extra = {}) =>
  condition(cid, {
    action: 'insertCondition',
    tempId: cid,
    tempcids: [cid],
    ...extra,
  })

// One condition operation, keyed by its cid.
const conditionOperation = (cond, scid = 1, extra = {}) => ({
  id: cond.cid,
  qid: cond.qid,
  entity: 'questionCondition',
  op: Operations.update,
  props: { qid: cond.qid, scenarios: [{ scid, conditions: [cond] }] },
  ...extra,
})

const conditionDelete = (cid, qid = QID) => ({
  id: cid,
  qid,
  entity: 'questionCondition',
  op: Operations.delete,
  props: {
    qid,
    scenarios: [{ scid: 1, conditions: [{ cid, action: 'deleteCondition' }] }],
  },
})

const script = (qid = QID, extra = {}) => ({
  id: qid,
  qid,
  entity: 'questionCondition',
  op: Operations.update,
  props: { qid, action: 'conditionScript', script: 'Q10 == "A1"' },
  ...extra,
})

describe('handleQuestionConditionOperation', () => {
  test('returns the buffer as is without an operation', () => {
    const buffer = [conditionOperation(condition(5))]
    expect(handleQuestionConditionOperation(buffer, null)).toEqual({
      bufferOperations: buffer,
      newOperation: {},
      addToBuffer: false,
    })
  })

  describe('creates and edits', () => {
    test('adds the operation of a new condition', () => {
      const operation = conditionOperation(newCondition('temp__1_1'))
      const result = handleQuestionConditionOperation([], operation)

      expect(result.addToBuffer).toBe(true)
      expect(result.newOperation).toEqual(operation)
    })

    test('keeps the operations of other conditions as they are', () => {
      const other = conditionOperation(condition(5))
      const operation = conditionOperation(condition(6))
      const result = handleQuestionConditionOperation([other], operation)

      expect(result.bufferOperations).toEqual([other])
      expect(result.newOperation).toEqual(operation)
    })

    test('a newer edit replaces the buffered edit of the same condition, in place', () => {
      const other = conditionOperation(condition(6))
      const buffer = [conditionOperation(condition(5, { value: 'old' })), other]
      const edit = conditionOperation(condition(5, { value: 'new' }))
      const result = handleQuestionConditionOperation(buffer, edit)

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations).toEqual([edit, other])
    })

    test('a condition moved to another scenario keeps a single operation', () => {
      const buffer = [conditionOperation(condition(5), 1)]
      const move = conditionOperation(condition(5), 2)
      const result = handleQuestionConditionOperation(buffer, move)

      expect(result.bufferOperations).toEqual([move])
    })

    test('editing a condition whose create is still buffered keeps it a create', () => {
      const create = conditionOperation(newCondition('temp__1_1'))
      const edit = conditionOperation(
        condition('temp__1_1', { value: 'A2' }),
        2
      )
      const result = handleQuestionConditionOperation([create], edit)

      expect(result.addToBuffer).toBe(false)
      expect(result.bufferOperations).toEqual([
        conditionOperation(newCondition('temp__1_1', { value: 'A2' }), 2),
      ])
    })

    test('editing a condition whose create is being sent waits for its real cid', () => {
      const edit = conditionOperation(condition('temp__1_1', { value: 'A2' }))
      const result = handleQuestionConditionOperation([], edit)

      expect(result.addToBuffer).toBe(true)
      expect(isConditionOperationWaiting(result.newOperation)).toBe(true)
    })
  })

  describe('deletes', () => {
    test('a saved condition: replaces its buffered edit and queues the delete', () => {
      const other = conditionOperation(condition(6))
      const buffer = [conditionOperation(condition(5)), other]
      const result = handleQuestionConditionOperation(
        buffer,
        conditionDelete(5)
      )

      expect(result.bufferOperations).toEqual([other])
      expect(result.newOperation).toEqual(conditionDelete(5))
      expect(result.addToBuffer).toBe(true)
    })

    test('a local-only condition: drops its create without sending a delete', () => {
      const buffer = [
        conditionOperation(newCondition('temp__1_1')),
        conditionOperation(newCondition('temp__1_2')),
      ]
      const result = handleQuestionConditionOperation(
        buffer,
        conditionDelete('temp__1_2')
      )

      expect(result.bufferOperations).toEqual([buffer[0]])
      expect(result.addToBuffer).toBe(false)
    })

    test('a condition whose create is being sent: queues the delete, which waits', () => {
      const result = handleQuestionConditionOperation(
        [],
        conditionDelete('temp__1_1')
      )

      expect(result.addToBuffer).toBe(true)
      expect(isConditionOperationWaiting(result.newOperation)).toBe(true)
    })

    test('an older delete of the same condition is replaced', () => {
      const result = handleQuestionConditionOperation(
        [conditionDelete(5)],
        conditionDelete(5)
      )

      expect(result.bufferOperations).toEqual([])
      expect(result.addToBuffer).toBe(true)
    })

    test('a condition whose cid equals a qid does not touch that question', () => {
      const otherQuestion = conditionOperation(condition(5, { qid: 7 }))
      const result = handleQuestionConditionOperation(
        [otherQuestion, script(7)],
        conditionDelete(7)
      )

      expect(result.bufferOperations).toEqual([otherQuestion, script(7)])
    })
  })

  describe('expression scripts', () => {
    test('drop the condition operations of their question only', () => {
      const otherQuestion = conditionOperation(condition(5, { qid: 30 }))
      const result = handleQuestionConditionOperation(
        [conditionOperation(condition(6)), otherQuestion, conditionDelete(7)],
        script()
      )

      expect(result.bufferOperations).toEqual([otherQuestion])
      expect(result.newOperation).toEqual(script())
      expect(result.addToBuffer).toBe(true)
    })

    test('a condition change is not merged into a buffered script', () => {
      const operation = conditionOperation(condition(5))
      const result = handleQuestionConditionOperation([script()], operation)

      expect(result.bufferOperations).toEqual([script()])
      expect(result.newOperation).toEqual(operation)
    })
  })
})

describe('removeConditionOpsOfFailedCreates', () => {
  test('drops a delete left on a temp cid, e.g. after a refused create', () => {
    const otherQuestion = conditionOperation(condition(5, { qid: 30 }))
    const buffer = [
      conditionDelete('temp__1_2'),
      otherQuestion,
      conditionDelete(9),
    ]

    expect(removeConditionOpsOfFailedCreates(buffer)).toEqual([
      otherQuestion,
      conditionDelete(9),
    ])
  })

  test('drops an edit left on a temp cid, e.g. after a refused create', () => {
    const saved = conditionOperation(condition(5))
    const buffer = [
      conditionOperation(condition('temp__1_2', { value: 'A2' })),
      saved,
    ]

    expect(removeConditionOpsOfFailedCreates(buffer)).toEqual([saved])
  })

  test('keeps the buffered creates, and their temp cids', () => {
    const buffer = [
      conditionOperation(newCondition('temp__1_2', { value: 'A2' })),
      conditionOperation(newCondition('temp__1_3')),
    ]

    expect(removeConditionOpsOfFailedCreates(buffer)).toBe(buffer)
  })

  test('keeps the buffer as is without such an operation', () => {
    const buffer = [conditionDelete(9), conditionOperation(condition(5))]
    expect(removeConditionOpsOfFailedCreates(buffer)).toBe(buffer)
  })
})

describe('condition operations waiting for a temp ID', () => {
  test('detects the operations that must wait', () => {
    expect(isConditionOperationWaiting(conditionOperation(condition(7)))).toBe(
      false
    )
    expect(
      isConditionOperationWaiting(
        conditionOperation(condition(7, { cfieldname: 'Q4_Stemp__34' }))
      )
    ).toBe(true)
    expect(
      isConditionOperationWaiting(
        conditionOperation(condition(7, { cqid: 'temp__5' }))
      )
    ).toBe(true)
    expect(
      isConditionOperationWaiting(conditionOperation(condition('temp__9_1')))
    ).toBe(true)
    expect(
      isConditionOperationWaiting({
        ...conditionOperation(condition(7)),
        error: true,
      })
    ).toBe(true)
  })

  test('a create keyed by its temp cid does not wait', () => {
    expect(
      isConditionOperationWaiting(conditionOperation(newCondition('temp__9_1')))
    ).toBe(false)
  })

  test('removes the operations using a temp ID that no longer exists', () => {
    const survey = {
      questionGroups: [
        {
          gid: 1,
          questions: [{ qid: 4, subquestions: [{ qid: 'temp__35' }] }],
        },
      ],
    }
    const saved = conditionOperation(condition(7))
    const usingDeleted = conditionOperation(
      newCondition('temp__1_1', { cfieldname: 'Q4_Stemp__34' })
    )
    const usingExisting = conditionOperation(
      newCondition('temp__1_2', { cfieldname: 'Q4_Stemp__35' })
    )
    const ofDeletedQuestion = conditionOperation(
      condition(8, { qid: 'temp__99' })
    )
    const operations = [saved, usingDeleted, usingExisting, ofDeletedQuestion]

    const knownTempIds = getKnownTempIds(survey, operations)
    expect([...knownTempIds]).toEqual(['temp__35'])

    expect(
      removeConditionOpsWithUnknownTempIds(operations, knownTempIds)
    ).toEqual([saved, usingExisting])
  })

  test('knows the temp IDs of entities only in the buffer', () => {
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

  test('only looks for the wanted temp IDs, and stops once they are found', () => {
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

describe('hasUnansweredConditionCreate', () => {
  const operationOf = (cid, apiCondition) => ({
    id: cid,
    qid: QID,
    entity: 'questionCondition',
    op: Operations.update,
    props: { qid: QID, scenarios: [{ scid: 1, conditions: [apiCondition] }] },
  })
  const sent = [
    operationOf('temp__5_1', newCondition('temp__5_1')),
    operationOf(40, condition(40)),
  ]

  test('is false when every sent create got its real cid', () => {
    expect(
      hasUnansweredConditionCreate(sent, [{ tempId: 'temp__5_1', id: 900 }])
    ).toBe(false)
    expect(hasUnansweredConditionCreate([sent[1]], undefined)).toBe(false)
  })

  test('is true when a sent create got no real cid', () => {
    // A failed request has no mappings at all.
    expect(hasUnansweredConditionCreate(sent, undefined)).toBe(true)
    // The server skipped the condition without an error.
    expect(
      hasUnansweredConditionCreate(sent, [{ tempId: 'temp__6_1', id: 901 }])
    ).toBe(true)
  })
})

describe('removeConditionOperationsOfQuestions', () => {
  const questionUpdate = { id: QID, entity: 'question', op: Operations.update }
  const ofOtherQuestion = conditionOperation(condition(7, { qid: 30 }))
  ofOtherQuestion.qid = 30
  ofOtherQuestion.props.qid = 30

  test('removes the condition operations of the given questions only', () => {
    const buffer = [
      conditionOperation(condition(5)),
      script(),
      questionUpdate,
      ofOtherQuestion,
    ]

    expect(removeConditionOperationsOfQuestions(buffer, [QID, 99])).toEqual([
      questionUpdate,
      ofOtherQuestion,
    ])
  })

  test('keeps the buffer as is without such an operation', () => {
    const buffer = [questionUpdate, ofOtherQuestion]

    expect(removeConditionOperationsOfQuestions(buffer, [QID])).toBe(buffer)
    expect(removeConditionOperationsOfQuestions(buffer, [])).toBe(buffer)
  })
})
