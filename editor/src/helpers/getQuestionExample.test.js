import { getQuestionExample } from './getQuestionExample'

describe('getQuestionExample', () => {
  test('includes user default attributes and their availability state', () => {
    const attributes = { random_order: { '': '1' } }
    const question = getQuestionExample({
      attributes,
      hasDefaultAttributeValues: true,
    })

    expect(question.attributes).toEqual(attributes)
    expect(question.hasDefaultAttributeValues).toBe(true)
  })
})
