import {
  formatDay,
  formatResponseCount,
  formatTime,
  parseAnswerDateTime,
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
