import {
  isResolvedTempId,
  rememberResolvedTempIds,
  resolveTempIds,
} from './resolvedTempIds'

describe('resolvedTempIds', () => {
  rememberResolvedTempIds([
    { tempId: 'temp__501', id: 77 },
    { tempId: 'temp__502_1', id: 900 },
  ])

  it('recognizes a temp ID replaced by its real ID', () => {
    expect(isResolvedTempId('temp__501', 77)).toBe(true)
    expect(isResolvedTempId('temp__501', '77')).toBe(true)
  })

  it('does not treat a switch to another entity as a replacement', () => {
    expect(isResolvedTempId('temp__501', 78)).toBe(false)
    expect(isResolvedTempId('temp__999', 77)).toBe(false)
    expect(isResolvedTempId(12, 77)).toBe(false)
    expect(isResolvedTempId(undefined, 77)).toBe(false)
  })

  it('replaces resolved temp IDs in unsaved conditions', () => {
    const conditions = [
      { cid: 'temp__502_1', cqid: 'temp__501', cfieldname: 'Qtemp__501' },
    ]

    expect(resolveTempIds(conditions)).toEqual([
      { cid: 900, cqid: 77, cfieldname: 'Q77' },
    ])
  })

  it('returns the same reference when nothing is resolved', () => {
    const conditions = [{ cqid: 'temp__999', cfieldname: '' }]
    const noTempIds = [{ cqid: 12 }]

    expect(resolveTempIds(conditions)).toBe(conditions)
    expect(resolveTempIds(noTempIds)).toBe(noTempIds)
  })
})
