import { getConditionDependentQuestions } from './getConditionDependentQuestions'

const survey = {
  questionGroups: [
    {
      questions: [
        { qid: 32, title: 'Q00', scenarios: [] },
        {
          qid: 36,
          title: 'Q002',
          scenarios: [{ scid: 1, conditions: [{ cid: 15, cqid: 32 }] }],
        },
      ],
    },
    {
      questions: [
        {
          qid: 'temp__1',
          title: 'Q003',
          scenarios: [{ scid: 1, conditions: [{ cqid: 'temp__9' }] }],
        },
        {
          qid: 'temp__9',
          title: 'Q004',
          scenarios: [{ scid: 1, conditions: [{ cqid: 'temp__9' }] }],
        },
      ],
    },
  ],
}

describe('getConditionDependentQuestions', () => {
  it('returns questions with conditions on the given question', () => {
    expect(
      getConditionDependentQuestions(survey, 32).map((q) => q.qid)
    ).toEqual([36])
  })

  it('matches temp ids and ignores the question itself', () => {
    expect(
      getConditionDependentQuestions(survey, 'temp__9').map((q) => q.qid)
    ).toEqual(['temp__1'])
  })

  it('returns an empty list when nothing depends on the question', () => {
    expect(getConditionDependentQuestions(survey, 36)).toEqual([])
    expect(getConditionDependentQuestions({}, 32)).toEqual([])
  })
})
