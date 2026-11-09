import { OperationsBuffer } from 'helpers/Buffer/OperationsBuffer'

import { buildScenarioConditionDeleteOperations } from './scenarioPayloadBuilder'

const condition = (cid, scenario, value = 'A1') => ({
  qid: 20,
  cqid: 10,
  cfieldname: 'Q10',
  cquestions: 'Q10',
  method: '==',
  value,
  scenario,
  editSourceTab: '#SRCPREVQUEST',
  editTargetTab: '#CANSWERSTAB',
  canswers: [value],
  action: 'updateCondition',
  cid,
})

// A condition moved to another scenario (its scenario number edited).
const moveTo = (cid, scid) => ({
  id: 20,
  qid: 20,
  entity: 'questionCondition',
  op: 'update',
  props: { qid: 20, scenarios: [{ scid, conditions: [condition(cid, scid)] }] },
})

const deletedCids = (operations) =>
  operations
    .filter((op) => op.op === 'delete')
    .flatMap((op) => op.props.scenarios.flatMap((s) => s.conditions ?? []))
    .map((c) => c.cid)

describe('buildScenarioConditionDeleteOperations', () => {
  let logSpy
  beforeEach(() => {
    logSpy = jest.spyOn(console, 'log').mockImplementation(() => {})
  })
  afterEach(() => logSpy.mockRestore())

  test('deletes each condition of the scenario by cid', () => {
    const operations = buildScenarioConditionDeleteOperations(20, {
      scid: 1,
      conditions: [{ cid: 77 }, { cid: 'temp__5_1' }],
    })

    expect(operations).toHaveLength(2)
    expect(operations.map((op) => op.id)).toEqual([77, 'temp__5_1'])
    expect(deletedCids(operations)).toEqual([77, 'temp__5_1'])
    expect(operations.every((op) => op.props.qid === 20)).toBe(true)
  })

  test('deletes nothing for a scenario without conditions', () => {
    expect(buildScenarioConditionDeleteOperations(20, { scid: 1 })).toEqual([])
    expect(buildScenarioConditionDeleteOperations(20, undefined)).toEqual([])
  })

  test('a condition moved out of the deleted scenario is kept', () => {
    // 77 moved from scenario 1 to 2: scenario 1 now only shows 78.
    const buffer = new OperationsBuffer([moveTo(77, 2)])
    buildScenarioConditionDeleteOperations(20, {
      scid: 1,
      conditions: [{ cid: 78 }],
    }).forEach((op) => buffer.addOperation(op))

    expect(deletedCids(buffer.getOperations())).toEqual([78])
    expect(buffer.getOperations()).toContainEqual(moveTo(77, 2))
  })

  test('a condition moved into the deleted scenario is deleted', () => {
    // 77 moved from scenario 2 to 1, then scenario 1 is deleted.
    const buffer = new OperationsBuffer([moveTo(77, 1)])
    buildScenarioConditionDeleteOperations(20, {
      scid: 1,
      conditions: [{ cid: 77 }],
    }).forEach((op) => buffer.addOperation(op))

    expect(deletedCids(buffer.getOperations())).toEqual([77])
    expect(buffer.getOperations().filter((op) => op.op === 'update')).toEqual(
      []
    )
  })
})
