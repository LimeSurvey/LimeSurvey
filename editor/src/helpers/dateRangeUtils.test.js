import { format, isSameDay, parse } from 'date-fns'

import {
  DATE_FORMAT,
  expandToUnit,
  getDayPickerLocale,
  getPresets,
} from './dateRangeUtils'

global.t = (key) => key

const day = (y, m, d) => new Date(y, m - 1, d)
const toKeys = ({ from, to }) => [from.toDateString(), to.toDateString()]

describe('expandToUnit', () => {
  test('a week runs Monday to Sunday', () => {
    expect(toKeys(expandToUnit(day(2026, 1, 14), 'week'))).toEqual([
      day(2026, 1, 12).toDateString(),
      day(2026, 1, 18).toDateString(),
    ])
  })

  test('a week can cross the year boundary', () => {
    expect(toKeys(expandToUnit(day(2026, 1, 1), 'week'))).toEqual([
      day(2025, 12, 29).toDateString(),
      day(2026, 1, 4).toDateString(),
    ])
  })

  test('a month covers all its days', () => {
    expect(toKeys(expandToUnit(day(2024, 2, 10), 'month'))).toEqual([
      day(2024, 2, 1).toDateString(),
      day(2024, 2, 29).toDateString(),
    ])
  })

  test('a year covers January to December', () => {
    expect(toKeys(expandToUnit(day(2026, 6, 3), 'year'))).toEqual([
      day(2026, 1, 1).toDateString(),
      day(2026, 12, 31).toDateString(),
    ])
  })

  test('a range mode keeps the single day', () => {
    expect(toKeys(expandToUnit(day(2026, 6, 3), 'range'))).toEqual([
      day(2026, 6, 3).toDateString(),
      day(2026, 6, 3).toDateString(),
    ])
  })
})

test('the presets follow the ticket', () => {
  const { presets, trailingPresets } = getPresets()

  expect(presets.map(({ key }) => key)).toEqual([
    'today',
    'week',
    'month',
    'year',
  ])
  expect(trailingPresets.map(({ key }) => key)).toEqual(['custom'])
})

describe('DATE_FORMAT', () => {
  test('is the format of the Figma in English', () => {
    expect(
      format(new Date(2026, 0, 8), DATE_FORMAT, {
        locale: getDayPickerLocale('en'),
      })
    ).toBe('Jan 8, 2026')
  })

  test.each(['en', 'de', 'fr', 'es', 'nl', 'it', 'pt', 'ru', 'pl', 'tr', 'el'])(
    'a date shown in %s can be typed back',
    (language) => {
      const locale = getDayPickerLocale(language)
      const date = new Date(2026, 8, 8)

      expect(
        isSameDay(
          parse(
            format(date, DATE_FORMAT, { locale }),
            DATE_FORMAT,
            new Date(),
            { locale }
          ),
          date
        )
      ).toBe(true)
    }
  )
})
