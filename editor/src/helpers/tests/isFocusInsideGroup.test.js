import { isFocusInsideGroup } from 'helpers'

describe('isFocusInsideGroup', () => {
  const group = {
    gid: 10,
    questions: [{ qid: 100 }, { qid: 101 }],
  }

  test('returns true for a question inside the group', () => {
    expect(isFocusInsideGroup({ qid: 101, gid: 10 }, group)).toBe(true)
  })

  test('returns false for a question in another group', () => {
    expect(isFocusInsideGroup({ qid: 200, gid: 20 }, group)).toBe(false)
  })

  test('returns true for the group itself', () => {
    expect(isFocusInsideGroup({ gid: 10 }, group)).toBe(true)
  })

  test('returns false for another group', () => {
    expect(isFocusInsideGroup({ gid: 20 }, group)).toBe(false)
  })

  test('compares string and number ids', () => {
    expect(isFocusInsideGroup({ qid: '100' }, group)).toBe(true)
    expect(isFocusInsideGroup({ gid: '10' }, group)).toBe(true)
  })

  test('returns false for a focus without qid and gid (welcome / end screen)', () => {
    expect(isFocusInsideGroup({ info: { type: 'welcome' } }, group)).toBe(false)
    expect(isFocusInsideGroup({}, group)).toBe(false)
  })

  test('returns false for an undefined focus or group', () => {
    expect(isFocusInsideGroup(undefined, group)).toBe(false)
    expect(isFocusInsideGroup({ gid: 10 }, undefined)).toBe(false)
  })

  test('handles a group without questions', () => {
    expect(isFocusInsideGroup({ qid: 100 }, { gid: 10 })).toBe(false)
  })
})
