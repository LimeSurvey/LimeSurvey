import { removeConditionsWithUnknownTempIdsFromSurveyAndBuffer } from './removeConditionsWithUnknownTempIds'

describe('removeConditionsWithUnknownTempIdsFromSurveyAndBuffer', () => {
  const conditionUsing = (cqid) => ({
    id: 10,
    qid: 10,
    entity: 'questionCondition',
    op: 'update',
    props: {
      qid: 10,
      scenarios: [{ scid: 1, conditions: [{ cid: 1, cqid, cfieldname: 'Q' }] }],
    },
  })

  it('removes the conditions using a deleted unsaved question', () => {
    const survey = {
      questionGroups: [
        {
          gid: 1,
          questions: [
            {
              qid: 10,
              scenarios: [
                { scid: 1, conditions: [{ cid: 1, cqid: 'temp__7' }] },
              ],
            },
          ],
        },
      ],
    }
    const operations = [conditionUsing('temp__7')]

    const result = removeConditionsWithUnknownTempIdsFromSurveyAndBuffer(
      survey,
      operations
    )

    expect(result.changedQids).toEqual(['10'])
    expect(result.operations).toEqual([])
  })

  it('returns the given survey and operations when every temp ID used still exists', () => {
    const survey = {
      questionGroups: [
        {
          gid: 1,
          questions: [
            { qid: 'temp__7' },
            {
              qid: 10,
              scenarios: [
                { scid: 1, conditions: [{ cid: 1, cqid: 'temp__7' }] },
              ],
            },
          ],
        },
      ],
    }
    const operations = [conditionUsing('temp__7')]

    const result = removeConditionsWithUnknownTempIdsFromSurveyAndBuffer(
      survey,
      operations
    )

    expect(result.survey).toBe(survey)
    expect(result.operations).toBe(operations)
    expect(result.changedQids).toEqual([])
  })

  it('returns the given survey and operations when no condition uses a temp ID', () => {
    const survey = { questionGroups: [{ gid: 1, questions: [{ qid: 10 }] }] }
    const operations = [conditionUsing(4)]

    const result = removeConditionsWithUnknownTempIdsFromSurveyAndBuffer(
      survey,
      operations
    )

    expect(result.survey).toBe(survey)
    expect(result.operations).toBe(operations)
    expect(result.changedQids).toEqual([])
  })

  it('removes the conditions using a deleted unsaved subquestion from the survey only, without mutating', () => {
    const other = { qid: 3, scenarios: [] }
    const question = {
      qid: 10,
      relevance: 'X',
      scenarios: [
        {
          scid: 1,
          conditions: [{ cid: 1, cqid: 4, cfieldname: 'Q4_Stemp__34' }],
        },
      ],
    }
    const survey = {
      questionGroups: [{ gid: 1, questions: [other, question] }],
    }
    const operations = []

    const result = removeConditionsWithUnknownTempIdsFromSurveyAndBuffer(
      survey,
      operations
    )

    expect(result.changedQids).toEqual(['10'])
    // The relevance the server has (e.g. an expression script) is kept.
    expect(result.survey.questionGroups[0].questions[1]).toMatchObject({
      scenarios: [],
      relevance: 'X',
    })
    expect(result.survey.questionGroups[0].questions[0]).toBe(other)
    expect(question.scenarios).toHaveLength(1)
    expect(result.operations).toBe(operations)
  })
})
