import 'tests/mocks'

import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { StatisticsFiltersBuilder } from './StatisticsFiltersBuilder'
import { SOURCE, SURVEY_FIELD, createEmptyFilter } from './utils'

const createAppliedFilter = () => ({
  ...createEmptyFilter(),
  source: SOURCE.SURVEY_DATA,
  surveyField: SURVEY_FIELD.RESPONSE_ID,
})

const survey = { languages: ['en'] }

describe('StatisticsFiltersBuilder', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  test('starts from the applied filters so they survive reopening the modal', () => {
    render(
      <StatisticsFiltersBuilder
        survey={survey}
        value={[createAppliedFilter(), createAppliedFilter()]}
      />
    )

    expect(screen.getByText('Filter selection #1')).toBeInTheDocument()
    expect(screen.getByText('Filter selection #2')).toBeInTheDocument()
  })

  test('shows only "Add filter" when nothing is applied', () => {
    render(<StatisticsFiltersBuilder survey={survey} value={[]} />)

    expect(screen.queryByText('Filter selection #1')).not.toBeInTheDocument()
    expect(screen.getByText('Add filter')).toBeInTheDocument()
    expect(screen.queryByText('Apply filter')).not.toBeInTheDocument()
  })

  test('"Apply filter" hands the current rows back', async () => {
    const user = userEvent.setup()
    const onApply = jest.fn()
    const applied = [createAppliedFilter()]

    render(
      <StatisticsFiltersBuilder
        survey={survey}
        value={applied}
        onApply={onApply}
      />
    )

    await user.click(screen.getByText('Apply filter'))

    expect(onApply).toHaveBeenCalledWith(applied)
  })

  test('editing without applying emits nothing', async () => {
    const user = userEvent.setup()
    const onApply = jest.fn()

    render(
      <StatisticsFiltersBuilder
        survey={survey}
        value={[createAppliedFilter()]}
        onApply={onApply}
      />
    )

    await user.click(screen.getByText('Add filter'))

    expect(screen.getByText('Filter selection #2')).toBeInTheDocument()
    expect(onApply).not.toHaveBeenCalled()
  })

  test('"Apply filter" stays reachable after a reset empties the rows', async () => {
    const user = userEvent.setup()
    const onApply = jest.fn()

    render(
      <StatisticsFiltersBuilder
        survey={survey}
        value={[createAppliedFilter()]}
        onApply={onApply}
      />
    )

    await user.click(screen.getByText('Reset filter'))

    expect(screen.queryByText('Filter selection #1')).not.toBeInTheDocument()

    const apply = screen.getByText('Apply filter')
    expect(apply).toBeEnabled()

    await user.click(apply)

    expect(onApply).toHaveBeenCalledWith([])
  })
})
