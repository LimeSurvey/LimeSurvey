import { Entities } from './Buffer/Entities'
import {
  applyQuestionRelevance,
  getConditionLogicQids,
  getQidsWithPendingConditionOperations,
  hasPendingConditionLogicOperations,
  isConditionLogicOperation,
} from './conditionRelevance'

const conditionOperation = (props, extra = {}) => ({
  id: props.qid,
  entity: Entities.questionCondition,
  op: 'update',
  props,
  ...extra,
})

describe('conditionRelevance helpers', () => {
  const operations = [
    conditionOperation({ qid: 1, scenarios: [] }),
    conditionOperation({ qid: 'temp__2', scenarios: [] }),
    conditionOperation({ qid: 3, action: 'conditionScript', script: 'x' }),
    conditionOperation({ qid: 4, scenarios: [] }, { error: true }),
    { id: 5, entity: Entities.question, op: 'update', props: { qid: 5 } },
    conditionOperation({ qid: 1, scenarios: [] }),
  ]

  it('detects condition logic operations', () => {
    expect(isConditionLogicOperation(operations[0])).toBe(true)
    expect(isConditionLogicOperation(operations[2])).toBe(false)
    expect(isConditionLogicOperation(operations[4])).toBe(false)
  })

  it('returns unique qids of pending condition logic operations', () => {
    expect(getConditionLogicQids(operations)).toEqual(['1', 'temp__2'])
    expect(getConditionLogicQids(undefined)).toEqual([])
  })

  it('checks if a question has pending condition logic operations', () => {
    expect(hasPendingConditionLogicOperations(operations, 1)).toBe(true)
    expect(hasPendingConditionLogicOperations(operations, 'temp__2')).toBe(true)
    expect(hasPendingConditionLogicOperations(operations, 3)).toBe(false)
    expect(hasPendingConditionLogicOperations(operations, 4)).toBe(false)
  })
})

describe('getQidsWithPendingConditionOperations', () => {
  it('includes the questions with a pending expression script', () => {
    const operations = [
      {
        entity: 'questionCondition',
        op: 'update',
        props: { qid: 5, action: 'conditionScript', script: '1' },
      },
      { entity: 'questionCondition', op: 'update', props: { qid: 6 } },
      {
        entity: 'questionCondition',
        op: 'update',
        props: { qid: 7 },
        error: true,
      },
      { entity: 'question', op: 'update', id: 8, props: { qid: 8 } },
    ]

    expect(getQidsWithPendingConditionOperations(operations)).toEqual([
      '5',
      '6',
    ])
  })
})

describe('applyQuestionRelevance', () => {
  const survey = () => ({
    questionGroups: [
      {
        gid: 1,
        questions: [
          { qid: 10, relevance: '1' },
          { qid: 11, relevance: '1' },
          { qid: 12, relevance: '1' },
        ],
      },
    ],
  })
  const buffer = (operations) => ({ getOperations: () => operations })
  const relevanceOf = (result) =>
    result.survey.questionGroups[0].questions.map((q) => q.relevance)

  test('applies the returned relevance', () => {
    const result = applyQuestionRelevance(
      { survey: survey(), operationsBuffer: buffer([]) },
      { 10: 'A', 11: 'B' }
    )

    expect(relevanceOf(result)).toEqual(['A', 'B', '1'])
  })

  test('skips the questions with newer condition operations to send, not those waiting for a temp ID', () => {
    const result = applyQuestionRelevance(
      {
        survey: survey(),
        operationsBuffer: buffer([
          conditionOperation({ qid: 10, scenarios: [] }),
          conditionOperation({ qid: 11, cqid: 'temp__5', scenarios: [] }),
        ]),
      },
      { 10: 'A', 11: 'B', 12: 'C' }
    )

    expect(relevanceOf(result)).toEqual(['1', 'B', 'C'])
  })

  test('keeps the result as is without returned relevance', () => {
    const mapResult = { survey: survey(), operationsBuffer: buffer([]) }
    expect(applyQuestionRelevance(mapResult, undefined)).toBe(mapResult)
  })
})
