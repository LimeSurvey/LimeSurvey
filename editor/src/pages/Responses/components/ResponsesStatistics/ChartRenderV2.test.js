// Import shared mocks
import 'tests/mocks'

import { act, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { QT_L_LIST } from 'helpers'
import { renderWithProviders } from 'tests/testUtils'

import { ChartRendererV2 } from './ChartRenderV2'

// Recharts' d3 dependencies are ESM-only and don't load under Jest; stub the
// charts and expose the orientation they receive.
jest.mock('./Charts/index.js', () => {
  const stub = (testId) => {
    const ChartStub = ({ orientation }) => (
      <div data-testid={testId} data-orientation={orientation} />
    )
    return ChartStub
  }
  return {
    BarChart: stub('bar-chart'),
    RankingBarChart: stub('ranking-bar-chart'),
    PieChart: stub('pie-chart'),
    StackedBarChart: stub('stacked-bar-chart'),
    GroupedBarChart: stub('grouped-bar-chart'),
    DualScaleDoughnutChart: stub('dual-scale-doughnut-chart'),
    RadarChart: stub('radar-chart'),
    LineChart: stub('line-chart'),
    PolarAreaChart: stub('polar-area-chart'),
    DoughnutChart: stub('doughnut-chart'),
  }
})

const data = [
  { key: 'A1', title: 'Every day', value: 2, percentageValue: 20 },
  { key: 'A2', title: 'Rarely', value: 8, percentageValue: 80 },
]

const renderCard = (props = {}) =>
  renderWithProviders(
    <ChartRendererV2
      data={data}
      surveyId={1}
      chartId="chart-1"
      question={{ type: QT_L_LIST, code: 'Q1', title: 'How often?' }}
      {...props}
    />
  )

const getCardToggle = (index = 0) =>
  document.getElementById(`chart-view-${index}`)

const getChevron = (container = document.body) =>
  within(container).queryByRole('button', { name: 'Chart orientation' })

const getBarIcon = (index = 0) =>
  getCardToggle(index).querySelector('.responses-statistics-bar-toggle-icon i')

describe('ChartRendererV2 bar chart orientation', () => {
  // jsdom doesn't implement scrollIntoView, used when the view changes.
  beforeAll(() => {
    Element.prototype.scrollIntoView = jest.fn()
  })

  test('shows the chevron only while the bar chart is active', async () => {
    await renderCard()
    expect(getChevron()).toBeInTheDocument()

    await userEvent.click(
      screen.getByTestId('toggleButton-chart-view-0-option-2')
    )
    expect(getChevron()).not.toBeInTheDocument()
  })

  test('opens and closes the orientation flyout from the chevron', async () => {
    await renderCard()
    expect(screen.queryByRole('menu')).not.toBeInTheDocument()

    await userEvent.click(getChevron())
    expect(screen.getByRole('menu')).toBeInTheDocument()
    expect(getChevron()).toHaveAttribute('aria-expanded', 'true')

    await userEvent.click(getChevron())
    expect(screen.queryByRole('menu')).not.toBeInTheDocument()
  })

  test('closes the flyout on Escape', async () => {
    await renderCard()
    await userEvent.click(getChevron())
    await userEvent.keyboard('{Escape}')
    expect(screen.queryByRole('menu')).not.toBeInTheDocument()
  })

  test('moves between the flyout options with the arrow keys', async () => {
    await renderCard()
    await userEvent.click(getChevron())
    const vertical = screen.getByRole('menuitemradio', {
      name: 'Vertical bar chart',
    })
    const horizontal = screen.getByRole('menuitemradio', {
      name: 'Horizontal bar chart',
    })
    // Opening focuses the selected option.
    expect(vertical).toHaveFocus()

    await userEvent.keyboard('{ArrowDown}')
    expect(horizontal).toHaveFocus()
    await userEvent.keyboard('{ArrowDown}')
    expect(vertical).toHaveFocus()
    await userEvent.keyboard('{ArrowUp}')
    expect(horizontal).toHaveFocus()
    await userEvent.keyboard('{Home}')
    expect(vertical).toHaveFocus()
    await userEvent.keyboard('{End}')
    expect(horizontal).toHaveFocus()
  })

  test('closes the flyout when tabbing out of it', async () => {
    await renderCard()
    await userEvent.click(getChevron())
    await userEvent.tab()
    expect(screen.queryByRole('menu')).not.toBeInTheDocument()
  })

  test('closes the flyout when focus moves outside the toggle', async () => {
    await renderWithProviders(
      <>
        <ChartRendererV2
          data={data}
          surveyId={1}
          chartId="chart-1"
          question={{ type: QT_L_LIST, code: 'Q1', title: 'How often?' }}
        />
        <button type="button">Outside</button>
      </>
    )
    await userEvent.click(getChevron())
    act(() => screen.getByRole('button', { name: 'Outside' }).focus())
    expect(screen.queryByRole('menu')).not.toBeInTheDocument()
  })

  test('selecting an orientation updates the icon and closes the flyout', async () => {
    await renderCard()
    expect(getBarIcon()).toHaveClass('ri-bar-chart-line')

    await userEvent.click(getChevron())
    await userEvent.click(
      screen.getByRole('menuitemradio', { name: 'Horizontal bar chart' })
    )

    expect(screen.queryByRole('menu')).not.toBeInTheDocument()
    expect(getBarIcon()).toHaveClass('ri-bar-chart-horizontal-line')
    expect(screen.getByTestId('bar-chart')).toHaveAttribute(
      'data-orientation',
      'horizontal'
    )
  })

  test('restores the last orientation after switching to another view', async () => {
    await renderCard()
    await userEvent.click(getChevron())
    await userEvent.click(
      screen.getByRole('menuitemradio', { name: 'Horizontal bar chart' })
    )

    // Table, then back to the bar chart.
    await userEvent.click(
      screen.getByTestId('toggleButton-chart-view-0-option-2')
    )
    await userEvent.click(
      screen.getByTestId('toggleButton-chart-view-0-option-0')
    )

    expect(getBarIcon()).toHaveClass('ri-bar-chart-horizontal-line')
    await userEvent.click(getChevron())
    expect(
      screen.getByRole('menuitemradio', { name: 'Horizontal bar chart' })
    ).toHaveAttribute('aria-checked', 'true')
  })

  test('changes the orientation of its own card only', async () => {
    await renderWithProviders(
      <>
        <ChartRendererV2
          data={data}
          index={0}
          surveyId={1}
          chartId="chart-1"
          question={{ type: QT_L_LIST, code: 'Q1', title: 'First' }}
        />
        <ChartRendererV2
          data={data}
          index={1}
          surveyId={1}
          chartId="chart-2"
          question={{ type: QT_L_LIST, code: 'Q2', title: 'Second' }}
        />
      </>
    )

    await userEvent.click(getChevron(getCardToggle(0)))
    await userEvent.click(
      screen.getByRole('menuitemradio', { name: 'Horizontal bar chart' })
    )

    expect(getBarIcon(0)).toHaveClass('ri-bar-chart-horizontal-line')
    expect(getBarIcon(1)).toHaveClass('ri-bar-chart-line')
  })
})
