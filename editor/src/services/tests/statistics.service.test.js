import { encodeFilterSet } from '../statistics.service'

// PHP parses these back into the array the endpoint validates, so the bracket
// shape is the contract: filterSet[<row>][<field>], with [] for list values.
describe('encodeFilterSet', () => {
  it.each([
    ['nothing applied', []],
    ['a missing filter', undefined],
    ['a filter that is not a list', {}],
  ])('sends nothing for %s', (unused, filterSet) => {
    expect(encodeFilterSet(filterSet)).toBe('')
  })

  it('names each row by its position', () => {
    expect(
      encodeFilterSet([
        { source: 'surveyData', field: 'id' },
        { join: 'or', source: 'surveyData', field: 'seed' },
      ])
    ).toBe(
      'filterSet[0][source]=surveyData&filterSet[0][field]=id' +
        '&filterSet[1][join]=or&filterSet[1][source]=surveyData&filterSet[1][field]=seed'
    )
  })

  it('repeats a list value under the same key', () => {
    expect(
      encodeFilterSet([
        { source: 'question', qid: 12, answerCodes: ['A1', 'A2'] },
      ])
    ).toBe(
      'filterSet[0][source]=question&filterSet[0][qid]=12' +
        '&filterSet[0][answerCodes][]=A1&filterSet[0][answerCodes][]=A2'
    )
  })

  it('escapes values that would otherwise end the param', () => {
    expect(encodeFilterSet([{ source: 'participant', value: 'a&b=c d' }])).toBe(
      'filterSet[0][source]=participant&filterSet[0][value]=a%26b%3Dc%20d'
    )
  })

  // An absent value and an empty one mean different things to the server, so
  // only the absent ones are dropped.
  it('drops empty values but keeps blank strings', () => {
    expect(
      encodeFilterSet([{ source: 'question', text: '', numberMin: null }])
    ).toBe('filterSet[0][source]=question&filterSet[0][text]=')
  })
})
