import { createBufferOperation, entity, operation } from './Operation'
import { OperationsBuffer } from './OperationsBuffer'
import { Entities } from './Entities.js'
import { Operations } from './Operations'

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
})
