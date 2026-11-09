import { OperationsBuffer } from 'helpers/Buffer/OperationsBuffer'

import {
  buildConditionOperations,
  buildScenarioPayload,
  buildScenarioConditionDeleteOperations,
} from './scenarioPayloadBuilder'

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

// A condition moved to another scenario (its scenario number edited), keyed by
// its cid as buildConditionOperations builds it.
const moveTo = (cid, scid) => ({
  id: cid,
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
    // 77 moved from scenario 1 to 2: Scenario 1 now only shows 78.
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

describe('buildConditionOperations', () => {
  test('builds one operation per condition, keyed by its cid', () => {
    const created = { cid: 'temp__5_1', action: 'insertCondition' }
    const updated = { cid: 77, action: 'updateCondition' }
    const operations = buildConditionOperations(20, [
      { scid: 1, conditions: [created] },
      { scid: 2, conditions: [updated] },
    ])

    expect(operations).toEqual([
      expect.objectContaining({
        id: 'temp__5_1',
        qid: 20,
        entity: 'questionCondition',
        op: 'update',
        props: { qid: 20, scenarios: [{ scid: 1, conditions: [created] }] },
      }),
      expect.objectContaining({
        id: 77,
        op: 'update',
        props: { qid: 20, scenarios: [{ scid: 2, conditions: [updated] }] },
      }),
    ])
  })
})

describe('buildScenarioPayload', () => {
  const newCondition = (value) => ({
    qid: 20,
    cqid: 10,
    cfieldname: 'Q10',
    cquestions: 'Q10',
    method: '==',
    value,
    sourceType: 'Question',
    targetType: 'Constant',
  })
  const tempIds = ({ scenarios }) =>
    scenarios.flatMap((s) => s.conditions.map((c) => c.tempId))

  test('gives every new condition a distinct temp ID, across payloads', () => {
    const first = tempIds(
      buildScenarioPayload(
        { qid: 20 },
        [newCondition('A'), newCondition('B')],
        1
      )
    )
    const second = tempIds(
      buildScenarioPayload({ qid: 20 }, [newCondition('C')], 1)
    )
    const all = [...first, ...second]

    expect(new Set(all).size).toBe(all.length)
    all.forEach((id) => expect(id).toMatch(/^temp__\d+_\d+$/))
  })
})
