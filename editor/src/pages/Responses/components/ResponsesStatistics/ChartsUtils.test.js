import {
  BAR_MIN_CATEGORY_WIDTH,
  BAR_SCROLL_THRESHOLD,
  getBarChartMinWidth,
  getStatisticsGraphsForType,
  statisticsGraphs,
} from './ChartsUtils'
import { QT_Q_MULTIPLE_SHORT_TEXT } from 'helpers'

describe('getBarChartMinWidth', () => {
  test('keeps the responsive chart width through the density threshold', () => {
    expect(getBarChartMinWidth(BAR_SCROLL_THRESHOLD)).toBeUndefined()
  })

  test('grows dense charts so their categories remain readable', () => {
    const count = BAR_SCROLL_THRESHOLD + 1

    expect(getBarChartMinWidth(count)).toBe(count * BAR_MIN_CATEGORY_WIDTH)
  })
})

describe('getStatisticsGraphsForType', () => {
  test('uses only the response list for multiple short text', () => {
    expect(getStatisticsGraphsForType(QT_Q_MULTIPLE_SHORT_TEXT)).toEqual([
      statisticsGraphs.GRID,
    ])
  })
})
