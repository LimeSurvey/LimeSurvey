import { formatFileSize } from './formatFileSize'

describe('formatFileSize', () => {
  test('Uses the unit that fits the size', () => {
    expect(formatFileSize(500 / 1024, 'en')).toBe('500 byte')
    expect(formatFileSize(2, 'en')).toBe('2 kB')
    expect(formatFileSize(5.8 * 1024, 'en')).toBe('5.8 MB')
  })

  test('Formats the number in the given locale', () => {
    expect(formatFileSize(5.8 * 1024, 'de')).toBe('5,8 MB')
  })

  test('An invalid locale falls back to the browser locale', () => {
    expect(formatFileSize(2, 'not a locale')).toBe(
      (2).toLocaleString(undefined, {
        maximumFractionDigits: 1,
        style: 'unit',
        unit: 'kilobyte',
        unitDisplay: 'short',
      })
    )
  })
})
