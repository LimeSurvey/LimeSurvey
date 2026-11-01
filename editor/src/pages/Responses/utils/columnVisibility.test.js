import {
  applyStoredColumnVisibility,
  readColumnVisibility,
  writeColumnVisibility,
} from './columnVisibility'

describe('columnVisibility', () => {
  beforeEach(() => {
    localStorage.removeItem('responses-table-column-visibility:123')
    localStorage.removeItem('responses-table-column-visibility:456')
    localStorage.removeItem('responses-table-timing-column-visibility:123')
  })

  test('stores normal and timing column visibility by survey', () => {
    writeColumnVisibility(123, [
      { id: 'question-time', isTiming: true, checked: true },
      { id: 'question', isTiming: false, checked: false },
    ])

    expect(readColumnVisibility(123)).toEqual({
      'question-time': true,
      'question': false,
    })
    expect(readColumnVisibility(456)).toEqual({})
  })

  test('stores visibility for surveys without timing columns', () => {
    writeColumnVisibility(123, [
      { id: 'id', checked: true },
      { id: 'question-1', checked: false },
      { id: 'question-2', checked: false },
    ])

    expect(readColumnVisibility(123)).toEqual({
      'id': true,
      'question-1': false,
      'question-2': false,
    })
  })

  test('restores only columns that still exist', () => {
    const columns = [
      { id: 'question-time', meta: { columnCategory: 'timing' } },
      { id: 'question' },
    ]

    expect(
      applyStoredColumnVisibility(
        columns,
        { 'question-time': false },
        {
          'question-time': true,
          'question': false,
          'obsolete': true,
        }
      )
    ).toEqual({ 'question-time': true, 'question': false })
  })

  test('ignores invalid stored data', () => {
    localStorage.setItem(
      'responses-table-column-visibility:123',
      'invalid json'
    )

    expect(readColumnVisibility(123)).toEqual({})
  })

  test('reads previously stored timing visibility', () => {
    localStorage.setItem(
      'responses-table-timing-column-visibility:123',
      JSON.stringify({ 'question-time': true })
    )

    expect(readColumnVisibility(123)).toEqual({ 'question-time': true })
  })
})
