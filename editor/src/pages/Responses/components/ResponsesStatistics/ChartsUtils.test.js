import {
  BAR_MIN_CATEGORY_WIDTH,
  BAR_SCROLL_THRESHOLD,
  getBarChartMinWidth,
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
