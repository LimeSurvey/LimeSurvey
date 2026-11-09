import { getRefreshedFocus } from './getRefreshedFocus'

describe('getRefreshedFocus', () => {
  const question = { qid: 5, relevance: 'Q1 == "A1"', scenarios: [] }
  const survey = {
    questionGroups: [{ gid: 1, questions: [{ qid: 4 }, question] }],
  }

  it('refreshes the focused question data and position', () => {
    const focusData = {
      focused: { qid: 5, relevance: '1' },
      groupIndex: 0,
      questionIndex: 0,
    }

    const result = getRefreshedFocus(focusData, survey)

    expect(result).toEqual({
      focused: question,
      groupIndex: 0,
      questionIndex: 1,
    })
    expect(result.focused).not.toBe(question)
  })

  it('returns null when nothing changed', () => {
    const focusData = {
      focused: { ...question },
      groupIndex: 0,
      questionIndex: 1,
    }

    expect(getRefreshedFocus(focusData, survey)).toBeNull()
  })

  it('returns null without a focused question in the survey', () => {
    expect(getRefreshedFocus({ focused: {} }, survey)).toBeNull()
    expect(getRefreshedFocus({ focused: { qid: 99 } }, survey)).toBeNull()
  })
})
