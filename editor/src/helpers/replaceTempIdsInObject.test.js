import { getTempIdMap, replaceTempIdsInObject } from './replaceValuesInObject'

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
