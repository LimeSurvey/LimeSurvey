import { getSyncedFocusState } from 'helpers'

describe('getSyncedFocusState', () => {
  const survey = {
    questionGroups: [
      { gid: 1, questions: [{ qid: 11 }] },
      { gid: 2, questions: [{ qid: 21 }, { qid: 22 }] },
      { gid: 3, questions: [] },
    ],
  }

  test('returns undefined when the question indexes are still correct', () => {
    const state = { focused: { qid: 22 }, groupIndex: 1, questionIndex: 1 }
    expect(getSyncedFocusState(state, survey)).toBeUndefined()
  })

  test('updates the indexes when the question moved', () => {
    // e.g. a group before it was deleted
    const state = { focused: { qid: 22 }, groupIndex: 2, questionIndex: 1 }
    expect(getSyncedFocusState(state, survey)).toEqual({
      focused: { qid: 22 },
      groupIndex: 1,
      questionIndex: 1,
    })
  })

  test('returns null when the focused question no longer exists', () => {
    const state = { focused: { qid: 99 }, groupIndex: 3, questionIndex: 0 }
    expect(getSyncedFocusState(state, survey)).toBeNull()
  })

  test('updates the index of a focused group', () => {
    const state = { focused: { gid: 3 }, groupIndex: 3 }
    expect(getSyncedFocusState(state, survey)).toEqual({
      focused: { gid: 3 },
      groupIndex: 2,
    })
  })

  test('returns undefined when the group index is still correct', () => {
    const state = { focused: { gid: '2' }, groupIndex: 1 }
    expect(getSyncedFocusState(state, survey)).toBeUndefined()
  })

  test('returns null when the focused group no longer exists', () => {
    const state = { focused: { gid: 4 }, groupIndex: 3 }
    expect(getSyncedFocusState(state, survey)).toBeNull()
  })

  test('ignores the welcome / end screen and an empty focus', () => {
    expect(
      getSyncedFocusState({ focused: { info: { type: 'welcome' } } }, survey)
    ).toBeUndefined()
    expect(getSyncedFocusState({ focused: {} }, survey)).toBeUndefined()
    expect(getSyncedFocusState({}, survey)).toBeUndefined()
    expect(getSyncedFocusState(undefined, survey)).toBeUndefined()
  })

  test('ignores a survey that is not loaded yet', () => {
    const state = { focused: { qid: 11 }, groupIndex: 0, questionIndex: 0 }
    expect(getSyncedFocusState(state, {})).toBeUndefined()
  })
})
