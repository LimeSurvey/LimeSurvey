import { Operations } from '../Operations'
import {
  isCreateBuffered,
  removeDeletesOfFailedCreates,
} from './removeDeletesOfFailedCreates'

const op = (entity, operation, id) => ({ entity, op: operation, id })

describe('isCreateBuffered', () => {
  test('finds the buffered create of the entity only', () => {
    const buffer = [op('question', Operations.create, 'temp__1')]

    expect(isCreateBuffered(buffer, 'question', 'temp__1')).toBe(true)
    expect(isCreateBuffered(buffer, 'questionGroup', 'temp__1')).toBe(false)
    expect(isCreateBuffered(buffer, 'question', 'temp__2')).toBe(false)
  })
})

describe('removeDeletesOfFailedCreates', () => {
  test('drops the deletes of new questions and groups left without their create', () => {
    const update = op('questionL10n', Operations.update, 5)
    const buffer = [
      op('question', Operations.delete, 'temp__1'),
      op('questionGroup', Operations.delete, 'temp__2'),
      update,
    ]

    expect(removeDeletesOfFailedCreates(buffer)).toEqual([update])
  })

  test('keeps the deletes of saved entities, and of creates still buffered', () => {
    const buffer = [
      op('question', Operations.delete, 7),
      op('question', Operations.create, 'temp__1'),
      op('question', Operations.delete, 'temp__1'),
      op('answer', Operations.delete, 'temp__3'),
    ]

    expect(removeDeletesOfFailedCreates(buffer)).toBe(buffer)
  })
})
