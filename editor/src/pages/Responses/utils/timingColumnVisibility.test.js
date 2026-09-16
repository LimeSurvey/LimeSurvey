import {
  applyStoredTimingColumnVisibility,
  readTimingColumnVisibility,
  writeTimingColumnVisibility,
} from './timingColumnVisibility'

describe('timingColumnVisibility', () => {
  beforeEach(() => {
    localStorage.removeItem('responses-table-timing-column-visibility:123')
    localStorage.removeItem('responses-table-timing-column-visibility:456')
  })

  test('stores timing visibility by survey', () => {
    writeTimingColumnVisibility(123, [
      { id: 'question-time', isTiming: true, checked: true },
      { id: 'question', isTiming: false, checked: false },
    ])

    expect(readTimingColumnVisibility(123)).toEqual({
      'question-time': true,
    })
    expect(readTimingColumnVisibility(456)).toEqual({})
  })

  test('restores only timing columns that still exist', () => {
    const columns = [
      { id: 'question-time', meta: { columnCategory: 'timing' } },
      { id: 'question' },
    ]

    expect(
      applyStoredTimingColumnVisibility(
        columns,
        { 'question-time': false },
        {
          'question-time': true,
          'question': false,
          'obsolete': true,
        }
      )
    ).toEqual({ 'question-time': true })
  })

  test('ignores invalid stored data', () => {
    localStorage.setItem(
      'responses-table-timing-column-visibility:123',
      'invalid json'
    )

    expect(readTimingColumnVisibility(123)).toEqual({})
  })
})
