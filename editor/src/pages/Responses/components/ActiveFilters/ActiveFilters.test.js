import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import {
  INCLUDED,
  SOURCE,
  SURVEY_FIELD,
  createEmptyFilter,
} from '../ResponsesStatistics/StatisticsFiltersModal/utils'
import { ActiveFilters } from './ActiveFilters'

const questionOptions = [
  {
    value: 1,
    code: 'Q001',
    text: 'What is your current role in the organization?',
    kind: 'answers',
    answerOptions: [
      { value: 'A1', label: 'Product management' },
      { value: 'A2', label: 'Product design' },
    ],
  },
]

const questionFilter = () => ({
  ...createEmptyFilter(),
  questionQid: 1,
  questionKind: 'answers',
  answerCodes: ['A1', 'A2'],
})

const includedFilter = () => ({
  ...createEmptyFilter(),
  source: SOURCE.SURVEY_DATA,
  surveyField: SURVEY_FIELD.INCLUDED,
  included: INCLUDED.COMPLETE,
})

const renderChips = (appliedFilters) =>
  render(
    <ActiveFilters
      appliedFilters={appliedFilters}
      questionOptions={questionOptions}
      survey={{}}
    />
  )

const chipList = () => screen.getByRole('list', { name: 'Active filters' })

describe('ActiveFilters', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  test('renders nothing when no filter is applied', () => {
    const { container } = renderChips([])

    expect(container).toBeEmptyDOMElement()
  })

  test('shows one chip per applied filter row', () => {
    renderChips([questionFilter(), includedFilter()])

    const chips = within(chipList()).getAllByRole('listitem')
    expect(chips.map((chip) => chip.textContent)).toEqual([
      'Q001 (2)',
      'Included responses: Complete',
    ])
  })

  test('skips rows that are not complete, as they are not applied', () => {
    renderChips([questionFilter(), createEmptyFilter()])

    expect(within(chipList()).getAllByRole('listitem')).toHaveLength(1)
  })

  test('shows the question and the chosen values on hover', async () => {
    const user = userEvent.setup()
    renderChips([questionFilter()])

    await user.hover(within(chipList()).getByText('Q001 (2)'))

    const tooltip = await screen.findByRole('tooltip')
    expect(tooltip).toHaveTextContent(
      'What is your current role in the organization?'
    )
    expect(
      within(tooltip)
        .getAllByRole('listitem')
        .map((item) => item.textContent)
    ).toEqual(['Product management', 'Product design'])
  })

  test('has no "Show more" when every chip fits', () => {
    renderChips([questionFilter(), includedFilter()])

    expect(
      within(chipList()).queryByRole('button', { name: 'Show more' })
    ).not.toBeInTheDocument()
  })

  describe('when the chips do not fit on one line', () => {
    beforeEach(() => {
      jest
        .spyOn(HTMLElement.prototype, 'offsetWidth', 'get')
        .mockReturnValue(100)
      jest
        .spyOn(HTMLElement.prototype, 'clientWidth', 'get')
        .mockReturnValue(250)
    })

    afterEach(() => {
      jest.restoreAllMocks()
    })

    test('shows what fits and "Show more", which shows the rest and "Show less"', async () => {
      const user = userEvent.setup()
      renderChips([questionFilter(), includedFilter(), questionFilter()])

      expect(within(chipList()).getAllByText(/Q001|Included/)).toHaveLength(1)

      await user.click(
        within(chipList()).getByRole('button', { name: 'Show more' })
      )
      expect(within(chipList()).getAllByText(/Q001|Included/)).toHaveLength(3)

      await user.click(
        within(chipList()).getByRole('button', { name: 'Show less' })
      )
      expect(within(chipList()).getAllByText(/Q001|Included/)).toHaveLength(1)
    })
  })
})
