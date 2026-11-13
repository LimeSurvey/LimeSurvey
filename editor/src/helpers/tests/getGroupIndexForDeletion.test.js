import {
  getGroupIndexForDeletion,
  NEW_OBJECT_ID_PREFIX,
  rememberResolvedTempId,
} from 'helpers'

describe('getGroupIndexForDeletion', () => {
  const groups = [{ gid: 1 }, { gid: 2 }, { gid: 3 }]

  test('finds the group by gid when its index changed', () => {
    expect(getGroupIndexForDeletion(groups, 3)).toBe(2)
  })

  test('compares string and number gids', () => {
    expect(getGroupIndexForDeletion(groups, '2')).toBe(1)
  })

  test('returns -1 when a saved group no longer exists', () => {
    expect(getGroupIndexForDeletion(groups, 4)).toBe(-1)
  })

  test('finds the group by its real gid after a temporary gid was replaced', () => {
    const tempId = `${NEW_OBJECT_ID_PREFIX}123`
    rememberResolvedTempId(tempId, 3)
    // The list was reordered after saving: The real gid still points to the right group
    const reordered = [{ gid: 3 }, { gid: 1 }, { gid: 2 }]
    expect(getGroupIndexForDeletion(reordered, tempId)).toBe(0)
  })

  test('returns -1 for an unknown temporary gid instead of guessing', () => {
    expect(getGroupIndexForDeletion(groups, `${NEW_OBJECT_ID_PREFIX}456`)).toBe(
      -1
    )
  })

  test('returns -1 without groups', () => {
    expect(getGroupIndexForDeletion(undefined, 1)).toBe(-1)
  })
})
