import { replaceValuesInObject } from './replaceValuesInObject'

describe('replaceValuesInObject', () => {
  test('replaces exact values and temp IDs embedded in strings', () => {
    const result = replaceValuesInObject(
      { qid: 'temp__12', cfieldname: 'Qtemp__12_Stemp__5#0' },
      'temp__12',
      34
    )

    expect(result).toEqual({ qid: 34, cfieldname: 'Q34_Stemp__5#0' })
  })

  test('does not replace a temp ID that is a prefix of a longer one', () => {
    const result = replaceValuesInObject(
      { cid: 'temp__12_10', cfieldname: 'Qtemp__123_S1' },
      'temp__12',
      34
    )

    expect(result).toEqual({ cid: 'temp__12_10', cfieldname: 'Qtemp__123_S1' })
  })

  test('does not replace a temp condition ID inside a longer one', () => {
    const result = replaceValuesInObject(
      { cids: ['temp__9_1', 'temp__9_10'], note: 'temp__9_10' },
      'temp__9_1',
      77
    )

    expect(result).toEqual({ cids: [77, 'temp__9_10'], note: 'temp__9_10' })
  })

  test('does not replace numeric IDs embedded in strings', () => {
    const result = replaceValuesInObject(
      { qid: 5, title: 'Q5', relevance: 'Q55 == 5' },
      5,
      6
    )

    expect(result).toEqual({ qid: 6, title: 'Q5', relevance: 'Q55 == 5' })
  })
})
