import { expandToUnit, getStatisticsPresets } from './dateRangeUtils'

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

test('statistics presets follow the ticket', () => {
  const { presets, trailingPresets, dayCountKeys } = getStatisticsPresets()

  expect(presets.map(({ key }) => key)).toEqual([
    'today',
    'week',
    'month',
    'year',
  ])
  expect(trailingPresets.map(({ key }) => key)).toEqual(['custom'])
  expect(dayCountKeys).toEqual(['upTo'])
})
