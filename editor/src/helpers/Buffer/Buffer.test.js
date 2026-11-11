import { createBufferOperation, entity, operation } from './Operation'
import { OperationsBuffer } from './OperationsBuffer'
import { Entities } from './Entities.js'
import { Operations } from './Operations'
import { removeDeletesOfFailedCreates } from './operationsHandlers/index.js'

describe('Buffer utility functions', () => {
  const id = 123

  test('createBufferOperation should return an object with entity functions', () => {
    const operation = createBufferOperation(id)
    Object.keys(Entities).forEach((key) => {
      expect(typeof operation[key]).toBe('function')
    })
  })

  test('entity should return an object with create, update, and delete functions', () => {
    Object.keys(Entities).forEach((key) => {
      const surveyEntity = entity(id, Entities[key])
      expect(typeof surveyEntity.create).toBe('function')
      expect(typeof surveyEntity.update).toBe('function')
      expect(typeof surveyEntity.delete).toBe('function')
      expect(Object.keys(surveyEntity).length).toBe(3)
    })
  })

  test('create and update operations should return objects with the correct properties', () => {
    const createOp = operation(id, Entities.question, Operations.create, {
      title: 'New Survey',
    })

    const updateOp = operation(id, Entities.question, Operations.update, {
      description: 'Updated Description',
    })

    const deleteOp = operation(id, Entities.question, Operations.delete)

    expect(createOp.id).toBe(id)
    expect(createOp.entity).toBe(Entities.question)
    expect(createOp.op).toBe(Operations.create)
    expect(createOp.props).toEqual({ title: 'New Survey' })

    expect(updateOp.id).toBe(id)
    expect(updateOp.entity).toBe(Entities.question)
    expect(updateOp.op).toBe(Operations.update)
    expect(updateOp.props).toEqual({ description: 'Updated Description' })

    expect(deleteOp.id).toBe(id)
    expect(deleteOp.entity).toBe(Entities.question)
    expect(deleteOp.op).toBe(Operations.delete)
  })

  test('functions chains should return the coorect properties', () => {
    const operation = createBufferOperation(id)
      .survey()
      .create({ title: 'New Title' })

    expect(operation.id).toBe(id)
    expect(operation.entity).toBe(Entities.survey)
    expect(operation.op).toBe(Operations.create)
    expect(operation.props).toEqual({ title: 'New Title' })
  })
})

describe('OperationsBuffer', () => {
  const id = 123
  const id2 = 321

  let operation
  let operation2
  let operation3
  let operationBuffer = new OperationsBuffer()

  beforeEach(() => {
    // Initialize a new OperationsBuffer instance before each test
    operationBuffer = new OperationsBuffer()
    operation = createBufferOperation(id)
      .question()
      .create({ question: {}, questionL10n: {} })
    operation2 = createBufferOperation(id)
      .question()
      .create({ question: {}, questionL10n: {} })
    operation3 = createBufferOperation(id2)
      .questionGroup()
      .create({ questionGroup: {}, questionGroupL10n: {} })
  })

  test('constructor should initialize with an empty array', () => {
    expect(operationBuffer.getOperations()).toEqual([])
  })

  test('addToBuffer should add a operation to the buffer', () => {
    operationBuffer.addOperation(operation)

    expect(operationBuffer.getOperations()).toEqual([
      {
        id: operation.id,
        entity: operation.entity,
        op: operation.op,
        props: operation.props,
      },
    ])
  })

  test('addToBuffer should update the props if the id, entity, and operation already exist', () => {
    operationBuffer.addOperation(operation)
    operationBuffer.addOperation(operation2)

    expect(operationBuffer.getOperations()).toEqual([
      {
        id: operation.id,
        entity: operation.entity,
        op: operation.op,
        props: { ...operation.props, ...operation2.props },
      },
    ])
  })

  test('setItems should set the buffer items', () => {
    const operations = [{ ...operation }]
    operationBuffer.setOperations(operations)
    const items = operationBuffer.getOperations()
    expect(items).toEqual(operations)
  })

  test('getItems should return a copy of the operations', () => {
    const operations = [{ ...operation }]
    operationBuffer.setOperations(operations)
    const items = operationBuffer.getOperations()

    // Verify that items is a copy, not the same array reference
    expect(items).toEqual(operations)
    expect(items).not.toBe(operations)
  })

  test('clearBuffer should empty the buffer', () => {
    operationBuffer.addOperation(operation)
    expect(operationBuffer.getOperations().length).toEqual(1)
    operationBuffer.clearBuffer()
    expect(operationBuffer.getOperations()).toEqual([])
  })

  test('removeFromBuffer should remove an operation by ID', () => {
    operationBuffer.addOperation(operation)
    operationBuffer.addOperation(operation3)

    operationBuffer.removeOperation(operation.id)

    delete operation3.error
    expect(operationBuffer.getOperations()).toEqual([{ ...operation3 }])
  })

  test('findIndex should find an item by id', () => {
    operationBuffer.addOperation(operation)
    operationBuffer.addOperation(operation3)

    expect(operationBuffer.findIndex(operation.id)).toBe(0)
    expect(operationBuffer.findIndex(operation3.id)).toBe(1)
    expect(operationBuffer.findIndex(3)).toBe(-1)
  })

  test('findIndex should find an item by id and operation', () => {
    operationBuffer.addOperation(operation)
    operationBuffer.addOperation(operation3)

    expect(operationBuffer.findIndex(operation.id, operation.op)).toBe(0)
    expect(operationBuffer.findIndex(operation3.id, operation.op)).toBe(1)
    expect(operationBuffer.findIndex(3)).toBe(-1)
  })

  test('findIndex should find the index of an operation by id, op, and entity', () => {
    operationBuffer.addOperation(operation)
    operationBuffer.addOperation(operation3)

    expect(
      operationBuffer.findIndex(operation.id, operation.op, operation.entity)
    ).toBe(0)
    expect(
      operationBuffer.findIndex(operation3.id, operation3.op, operation3.entity)
    ).toBe(1)
    expect(operationBuffer.findIndex(3, 'delete', 'order')).toBe(-1)
  })

  describe('condition operations readiness', () => {
    // Keyed by the cid, as buildConditionOperations does.
    const conditionOperation = (condition) => ({
      id: condition.cid,
      qid: 20,
      entity: Entities.questionCondition,
      op: Operations.update,
      props: { qid: 20, scenarios: [{ scid: 1, conditions: [condition] }] },
    })
    const isReady = (operation) =>
      new OperationsBuffer().isOperationReadyForPatch(operation, false, false)

    test('a create with a temp cid is ready', () => {
      expect(
        isReady(
          conditionOperation({
            cid: 'temp__1_1',
            tempId: 'temp__1_1',
            cqid: 10,
            cfieldname: 'Q10',
            action: 'insertCondition',
          })
        )
      ).toBe(true)
    })

    test('an edit of a condition whose create is being sent waits', () => {
      expect(
        isReady(
          conditionOperation({
            cid: 'temp__1_1',
            cqid: 10,
            cfieldname: 'Q10',
            action: 'updateCondition',
          })
        )
      ).toBe(false)
    })

    test('a condition on an unsaved subquestion waits', () => {
      expect(
        isReady(
          conditionOperation({
            cid: 'temp__1_1',
            tempId: 'temp__1_1',
            cqid: 10,
            cfieldname: 'Q10_Stemp__55',
            action: 'insertCondition',
          })
        )
      ).toBe(false)
    })
  })
})

describe('OperationsBuffer ready operations', () => {
  const createQuestion = (id, gid = 5) =>
    createBufferOperation(id)
      .question()
      .create({ question: { gid }, questionL10n: {} })
  const deleteQuestion = (id) => createBufferOperation(id).question().delete()
  const updateQuestion = (id) =>
    createBufferOperation(id)
      .question()
      .update({ question: { mandatory: 'Y' } })

  const readyOf = (operations) =>
    new OperationsBuffer(operations).getOperations({ ready: true })
  const notReadyOf = (operations) =>
    new OperationsBuffer(operations).getOperations({ ready: false })

  test('a create and a delete in the buffer do not block each other', () => {
    const operations = [createQuestion('temp__1'), deleteQuestion(10)]

    // The delete goes first, the create follows once the delete is gone.
    expect(readyOf(operations)).toEqual([deleteQuestion(10)])
    expect(notReadyOf(operations)).toEqual([createQuestion('temp__1')])
    expect(readyOf(notReadyOf(operations))).toEqual([createQuestion('temp__1')])
  })

  test('a delete waiting for a real ID does not hold back the creates', () => {
    const operations = [createQuestion('temp__1'), deleteQuestion('temp__2')]

    expect(readyOf(operations)).toEqual([createQuestion('temp__1')])
  })

  test('other operations wait for the creates and the deletes', () => {
    expect(
      readyOf([
        createQuestion('temp__1'),
        deleteQuestion(10),
        updateQuestion(11),
      ])
    ).toEqual([deleteQuestion(10)])
    expect(readyOf([createQuestion('temp__1'), updateQuestion(11)])).toEqual([
      createQuestion('temp__1'),
    ])
    expect(readyOf([deleteQuestion('temp__2'), updateQuestion(11)])).toEqual([])
    expect(readyOf([updateQuestion(11)])).toEqual([updateQuestion(11)])
  })

  test('a create that is not ready itself stays in the buffer', () => {
    const operations = [
      createQuestion('temp__1', 'temp__9'),
      { ...createQuestion('temp__3'), error: true },
      deleteQuestion(10),
    ]

    expect(readyOf(operations)).toEqual([deleteQuestion(10)])
    expect(readyOf(notReadyOf(operations))).toEqual([])
  })
})

describe('OperationsBuffer question delete and condition operations', () => {
  const conditionOperation = (cid, qid) => ({
    id: cid,
    qid,
    entity: Entities.questionCondition,
    op: Operations.update,
    props: {
      qid,
      scenarios: [
        {
          scid: 1,
          conditions: [
            {
              cid,
              qid,
              cqid: 3,
              cfieldname: 'Q3',
              cquestions: 'Q3',
              method: '==',
              value: 'A1',
              scenario: 1,
              action: 'updateCondition',
              editSourceTab: '#SRCPREVQUEST',
              editTargetTab: '#CANSWERSTAB',
              canswers: ['A1'],
            },
          ],
        },
      ],
    },
  })

  test('deleting a question drops its buffered condition operations', () => {
    const buffer = new OperationsBuffer()
    buffer.addOperation(conditionOperation(77, 10))
    buffer.addOperation(createBufferOperation(10).question().delete())

    expect(buffer.getOperations().map((op) => [op.entity, op.id])).toEqual([
      [Entities.question, 10],
    ])
  })

  test('deleting a question keeps the condition of another question whose cid equals its qid', () => {
    const buffer = new OperationsBuffer()
    buffer.addOperation(conditionOperation(10, 20))
    buffer.addOperation(createBufferOperation(10).question().delete())

    expect(buffer.getOperations().map((op) => [op.entity, op.id])).toEqual([
      [Entities.questionCondition, 10],
      [Entities.question, 10],
    ])
  })
})

describe('OperationsBuffer delete of a new question while its create is sent', () => {
  // The save loop took the create out of the buffer and sent it.
  const bufferAfterSendingCreate = () => new OperationsBuffer([])
  const deleteNew = (entity, id) =>
    entity === Entities.question
      ? createBufferOperation(id).question().delete()
      : createBufferOperation(id).questionGroup().delete()

  test.each([Entities.question, Entities.questionGroup])(
    '%s: the delete waits for the real ID, then is sent with it',
    (entity) => {
      const buffer = bufferAfterSendingCreate()
      buffer.addOperation(deleteNew(entity, 'temp__1'))

      expect(buffer.getOperations()).toHaveLength(1)
      expect(buffer.getOperations({ ready: true })).toEqual([])

      // The response of the create brings the real ID (replaceTempIdsInSurveyAndBuffer)
      buffer.setOperations(
        buffer.getOperations().map((o) => ({ ...o, id: 1000 }))
      )
      expect(buffer.getOperations({ ready: true })).toEqual([
        { entity, op: Operations.delete, id: 1000 },
      ])
    }
  )

  test('the create failed: the delete is dropped, the updates go on', () => {
    const buffer = bufferAfterSendingCreate()
    buffer.addOperation(deleteNew(Entities.question, 'temp__1'))
    const update = createBufferOperation(5)
      .question()
      .update({ mandatory: 'Y' })
    buffer.addOperation(update)
    // Held back by the waiting delete
    expect(buffer.getOperations({ ready: true })).toEqual([])

    buffer.setOperations(removeDeletesOfFailedCreates(buffer.getOperations()))

    // eslint-disable-next-line no-unused-vars
    const { error, ...expected } = update
    expect(buffer.getOperations({ ready: true })).toEqual([expected])
  })
})
