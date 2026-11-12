import { getGroupIndexForDeletion, NEW_OBJECT_ID_PREFIX } from 'helpers'

describe('getGroupIndexForDeletion', () => {
  const groups = [{ gid: 1 }, { gid: 2 }, { gid: 3 }]

  test('finds the group by gid when its index changed', () => {
    expect(getGroupIndexForDeletion(groups, 3, 0)).toBe(2)
  })

  test('compares string and number gids', () => {
    expect(getGroupIndexForDeletion(groups, '2', 0)).toBe(1)
  })

  test('returns -1 when a saved group no longer exists', () => {
    // Never guess by index: That could delete another group
    expect(getGroupIndexForDeletion(groups, 4, 1)).toBe(-1)
  })

  test('falls back to the index when a temporary gid was replaced', () => {
    expect(
      getGroupIndexForDeletion(groups, `${NEW_OBJECT_ID_PREFIX}123`, 1)
    ).toBe(1)
  })

  test('returns -1 when the fallback index is out of range', () => {
    expect(
      getGroupIndexForDeletion(groups, `${NEW_OBJECT_ID_PREFIX}123`, 5)
    ).toBe(-1)
  })
})
