import { render, screen } from '@testing-library/react'
import {
  BAR_MIN_CATEGORY_WIDTH,
  CustomTooltip,
  BAR_SCROLL_THRESHOLD,
  getBarChartMinWidth,
  getCategoryAxisWidth,
  getStatisticsGraphsForType,
  hasOtherAnswers,
  LABEL_CHAR_WIDTH,
  statisticsGraphs,
} from './ChartsUtils'
import { QT_Q_MULTIPLE_SHORT_TEXT } from 'helpers'

global.t = (key) => key

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

describe('hasOtherAnswers', () => {
  it('is true only for the "other" item that carries its answers', () => {
    expect(hasOtherAnswers({ key: 'other', otherAnswers: [] })).toBe(true)
    expect(hasOtherAnswers({ key: 'other' })).toBe(false)
    expect(hasOtherAnswers({ key: 'A1', otherAnswers: [] })).toBe(false)
  })
})

describe('CustomTooltip', () => {
  const OTHER_HINT = 'Click to view all "other" answers'
  const otherPayload = [
    {
      payload: { key: 'other', value: 31, percentage: 23.1, otherAnswers: [] },
    },
  ]

  it('shows the "other" hint on the other item when enabled', () => {
    render(<CustomTooltip active payload={otherPayload} showOtherHint />)
    expect(screen.getByText(OTHER_HINT)).toBeInTheDocument()
  })

  it('hides the "other" hint when not enabled', () => {
    render(<CustomTooltip active payload={otherPayload} />)
    expect(screen.queryByText(OTHER_HINT)).not.toBeInTheDocument()
  })

  it('hides the "other" hint on regular items', () => {
    render(
      <CustomTooltip
        active
        payload={[{ payload: { key: 'A1', value: 5, percentage: 10 } }]}
        showOtherHint
      />
    )
    expect(screen.queryByText(OTHER_HINT)).not.toBeInTheDocument()
  })

  it('shows the "other" hint instead of the comments hint on the other item', () => {
    render(
      <CustomTooltip
        active
        payload={otherPayload}
        showOtherHint
        showCommentsHint
      />
    )
    expect(screen.getByText(OTHER_HINT)).toBeInTheDocument()
    expect(screen.queryByText('Click to view comments')).not.toBeInTheDocument()
  })
})
