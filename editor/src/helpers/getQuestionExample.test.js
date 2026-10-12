import { getQuestionExample } from './getQuestionExample'

describe('getQuestionExample', () => {
  test('includes supplied question attributes', () => {
    const attributes = { random_order: { '': '1' } }
    const question = getQuestionExample({ attributes })

    expect(question.attributes).toEqual(attributes)
  })

  test('includes whether assessment values should be shown', () => {
    const question = getQuestionExample({ showAssessmentValue: true })

    expect(question.showAssessmentValue).toBe(true)
  })
})
