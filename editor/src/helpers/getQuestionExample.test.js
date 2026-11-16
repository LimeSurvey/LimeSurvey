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

  test('omits the assessment flag when it is not provided', () => {
    const question = getQuestionExample({})

    expect(question).not.toHaveProperty('showAssessmentValue')
  })

  test('preserves an explicitly disabled assessment flag', () => {
    const question = getQuestionExample({ showAssessmentValue: false })

    expect(question.showAssessmentValue).toBe(false)
  })
})
