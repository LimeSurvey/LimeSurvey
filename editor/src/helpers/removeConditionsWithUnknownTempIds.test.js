import {
  removeConditionsWithUnknownTempIds,
  removeConditionsWithUnknownTempIdsFromSurveyAndBuffer,
} from './removeConditionsWithUnknownTempIds'

describe('removeConditionsWithUnknownTempIds', () => {
  it('removes conditions using an unknown temp ID without mutating', () => {
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

    const result = removeConditionsWithUnknownTempIds(survey, new Set())

    expect(result.changedQids).toEqual(['10'])
    expect(result.survey.questionGroups[0].questions[1]).toMatchObject({
      scenarios: [],
      relevance: '1',
    })
    expect(result.survey.questionGroups[0].questions[0]).toBe(other)
    expect(question.scenarios).toHaveLength(1)
  })

  it('returns the same survey when nothing changes', () => {
    const survey = { questionGroups: [{ gid: 1, questions: [{ qid: 3 }] }] }
    expect(removeConditionsWithUnknownTempIds(survey, new Set()).survey).toBe(
      survey
    )
  })
})

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
})
