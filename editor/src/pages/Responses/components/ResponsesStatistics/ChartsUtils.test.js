import {
  BAR_MIN_CATEGORY_WIDTH,
  BAR_SCROLL_THRESHOLD,
  getBarChartMinWidth,
  getCategoryAxisWidth,
  LABEL_CHAR_WIDTH,
  TEXT_QUESTION_TYPES,
} from './ChartsUtils'
import { QT_J_MAP, QT_S_SHORT_FREE_TEXT } from 'helpers'

describe('getBarChartMinWidth', () => {
  test('keeps the responsive chart width through the density threshold', () => {
    expect(getBarChartMinWidth(BAR_SCROLL_THRESHOLD)).toBeUndefined()
  })

  test('grows dense charts so their categories remain readable', () => {
    const count = BAR_SCROLL_THRESHOLD + 1

    expect(getBarChartMinWidth(count)).toBe(count * BAR_MIN_CATEGORY_WIDTH)
  })
})

describe('TEXT_QUESTION_TYPES', () => {
  test('includes Map and Browser Detection question types', () => {
    expect(TEXT_QUESTION_TYPES).toEqual(
      expect.arrayContaining([QT_J_MAP, QT_S_SHORT_FREE_TEXT])
    )
  })
})

describe('getCategoryAxisWidth', () => {
  const options = { maxWidth: 150, imageWidth: 80, gap: 8 }
  const images = [
    { key: 'A1', title: 'https://example.com/a.png' },
    { key: 'A2', title: 'https://example.com/b.png' },
  ]

  test('fits short text labels', () => {
    const data = [
      { key: 'Y', title: 'Yes' },
      { key: 'N', title: 'No answer' },
    ]

    expect(getCategoryAxisWidth(data, options)).toBe(9 * LABEL_CHAR_WIDTH + 8)
  })

  test('caps long text labels at the max width', () => {
    const data = [{ key: 'A1', title: 'No answer for this particular option' }]

    expect(getCategoryAxisWidth(data, options)).toBe(150)
  })

  test('uses the image width when every image chart row shows an image', () => {
    expect(getCategoryAxisWidth(images, { ...options, isImage: true })).toBe(80)
  })

  test.each(['NoAnswer', 'other'])(
    'widens an image chart for its "%s" text label',
    (key) => {
      const data = [...images, { key, title: 'Label longer than images' }]

      expect(getCategoryAxisWidth(data, { ...options, isImage: true })).toBe(
        150
      )
    }
  )
})
