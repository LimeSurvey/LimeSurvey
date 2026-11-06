import { Entities } from './Buffer/Entities'
import {
  getConditionLogicQids,
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
