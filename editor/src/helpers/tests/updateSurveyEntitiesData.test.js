import { EntitiesType } from 'helpers'
import { updateSurveyEntitiesData } from 'helpers/entityHelpers'

const getSurvey = () => ({
  questionGroups: [
    {
      gid: 1,
      questions: [
        { qid: 10, relevance: '((Q1.NAOK == "A1"))', scenarios: [{ scid: 1 }] },
        {
          qid: 20,
          relevance: '((Q1.NAOK == "-oth-"))',
          scenarios: [{ scid: 1 }],
        },
        { qid: 30, relevance: '1', scenarios: [] },
      ],
    },
  ],
})

describe('updateSurveyEntitiesData', () => {
  test('updates one question from an object', () => {
    const { survey } = updateSurveyEntitiesData(
      EntitiesType.question,
      getSurvey(),
      null,
      { qid: 10, relevance: '1' }
    )
    expect(survey.questionGroups[0].questions[0].relevance).toBe('1')
    expect(survey.questionGroups[0].questions[1].relevance).toBe(
      '((Q1.NAOK == "-oth-"))'
    )
  })

  test('updates several questions from a list', () => {
    const { survey } = updateSurveyEntitiesData(
      EntitiesType.question,
      getSurvey(),
      null,
      [
        { qid: 10, relevance: '1', scenarios: [] },
        { qid: 20, relevance: '1', scenarios: [] },
      ]
    )
    const [q10, q20, q30] = survey.questionGroups[0].questions
    expect(q10).toMatchObject({ relevance: '1', scenarios: [] })
    expect(q20).toMatchObject({ relevance: '1', scenarios: [] })
    expect(q30).toMatchObject({ relevance: '1', scenarios: [] })
  })

  test('changes nothing for an empty list', () => {
    const survey = getSurvey()
    const result = updateSurveyEntitiesData(
      EntitiesType.question,
      survey,
      null,
      []
    )
    expect(result.survey).toBe(survey)
  })
})
