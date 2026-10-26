import {
  formatDay,
  formatResponseCount,
  formatTime,
  parseAnswerDateTime,
  sliceByRange,
  sumCounts,
  toDate,
  toDayKey,
} from './dateTimeUtils'

describe('parseAnswerDateTime', () => {
  test('splits a stored datetime into day and time', () => {
    expect(parseAnswerDateTime('2026-10-22 14:10:00')).toEqual({
      date: '2026-10-22',
      time: '14:10:00',
    })
  })

  test('accepts decrypted answers, which have no seconds', () => {
    expect(parseAnswerDateTime('2026-10-16 23:59')).toEqual({
      date: '2026-10-16',
      time: '23:59:00',
    })
  })

  test('keeps midnight as a real time', () => {
    expect(parseAnswerDateTime('2026-10-02 00:00:00')?.time).toBe('00:00:00')
  })

  test('has no time for a bare date', () => {
    expect(parseAnswerDateTime('2026-10-01')).toEqual({
      date: '2026-10-01',
      time: null,
    })
  })

  test.each(['', null, undefined, 'not a date', '2026-02-30 10:00:00'])(
    'rejects %p',
    (value) => {
      expect(parseAnswerDateTime(value)).toBeNull()
    }
  )
})

describe('toDate / toDayKey', () => {
  test('round-trips a day without a timezone shift', () => {
    expect(toDayKey(toDate('2026-01-12'))).toBe('2026-01-12')
  })

  test('accepts a leap day only in a leap year', () => {
    expect(toDate('2024-02-29')).toBeInstanceOf(Date)
    expect(toDate('2026-02-29')).toBeUndefined()
  })
})

describe('formatDay / formatTime', () => {
  test('formats a day in the given locale', () => {
    expect(formatDay('2023-01-08', 'en-US')).toBe('Jan 8, 2023')
  })

  test('formats a time in the given locale', () => {
    expect(formatTime('08:00:00', 'en-US')).toBe('08:00 AM')
  })

  test('returns an empty string without a value', () => {
    expect(formatDay('', 'en-US')).toBe('')
    expect(formatTime(null, 'en-US')).toBe('')
  })
})

describe('formatResponseCount', () => {
  const originalT = globalThis.t

  beforeAll(() => {
    globalThis.t = (text) => text
  })

  afterAll(() => {
    globalThis.t = originalT
  })

  test('uses the singular for one response', () => {
    expect(formatResponseCount(1)).toBe('1 response')
  })

  test('uses the plural otherwise', () => {
    expect(formatResponseCount(27)).toBe('27 responses')
  })
})

describe('sliceByRange / sumCounts', () => {
  const days = {
    '2025-12-31': { '23:59:00': 1 },
    '2026-01-12': { '': 5, '10:00:00': 2, '09:00:00': 12 },
    '2026-01-13': { '00:01:00': 3 },
  }

  test('returns one day, times sorted, without a time last', () => {
    expect(sliceByRange(days, '2026-01-12', '2026-01-12')).toEqual([
      { date: '2026-01-12', time: '09:00:00', count: 12 },
      { date: '2026-01-12', time: '10:00:00', count: 2 },
      { date: '2026-01-12', time: null, count: 5 },
    ])
  })

  test('includes both boundary days, across years', () => {
    const rows = sliceByRange(days, '2025-12-31', '2026-01-13')

    expect(rows.map((row) => row.date)).toEqual([
      '2025-12-31',
      '2026-01-12',
      '2026-01-12',
      '2026-01-12',
      '2026-01-13',
    ])
    expect(sumCounts(rows)).toBe(23)
  })

  test('sums a day to its calendar count', () => {
    expect(sumCounts(sliceByRange(days, '2026-01-12', '2026-01-12'))).toBe(19)
  })

  test('returns nothing for a range without answers', () => {
    expect(sliceByRange(days, '2026-02-01', '2026-02-28')).toEqual([])
    expect(sliceByRange(undefined, '2026-02-01', '2026-02-28')).toEqual([])
  })
})
