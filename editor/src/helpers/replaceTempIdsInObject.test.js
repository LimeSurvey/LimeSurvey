import { getTempIdMap, replaceTempIdsInObject } from './replaceTempIdsInObject'

describe('replaceTempIdsInObject', () => {
  const idMap = getTempIdMap([
    { tempId: 'temp__12', id: 40 },
    { tempId: 'temp__12_1', id: 900 },
    { tempId: 'temp__34', id: 41 },
  ])

  it('replaces values, keys and embedded temp IDs in one pass', () => {
    const value = {
      qid: 'temp__12',
      temp__34: { cqid: 'temp__34' },
      conditions: [{ cid: 'temp__12_1', cfieldname: 'Qtemp__12_Stemp__34#0' }],
    }

    expect(replaceTempIdsInObject(value, idMap)).toEqual({
      qid: 40,
      41: { cqid: 41 },
      conditions: [{ cid: 900, cfieldname: 'Q40_S41#0' }],
    })
  })

  it('only replaces whole temp IDs', () => {
    expect(
      replaceTempIdsInObject(
        { a: 'Qtemp__123', b: 'temp__12_2', c: 'xtemp__12' },
        idMap
      )
    ).toEqual({ a: 'Qtemp__123', b: 'temp__12_2', c: 'x40' })
  })

  it('keeps non-string values and does not mutate the input', () => {
    const value = { n: 5, b: true, nil: null, list: ['temp__12', 3] }

    expect(replaceTempIdsInObject(value, idMap)).toEqual({
      n: 5,
      b: true,
      nil: null,
      list: [40, 3],
    })
    expect(value.list[0]).toBe('temp__12')
  })
})

describe('replaceTempIdsInObject structural sharing', () => {
  const idMap = getTempIdMap([{ tempId: 'temp__12', id: 40 }])

  it('keeps unchanged parts and returns the value itself when unchanged', () => {
    const untouched = { qid: 3 }
    const value = [untouched, { qid: 'temp__12' }]

    const result = replaceTempIdsInObject(value, idMap)
    expect(result).not.toBe(value)
    expect(result[0]).toBe(untouched)
    expect(replaceTempIdsInObject([untouched], idMap)[0]).toBe(untouched)
    const same = [untouched]
    expect(replaceTempIdsInObject(same, idMap)).toBe(same)
  })
})
