import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { getStatisticsPresets } from 'helpers/dateRangeUtils'

import { DateRangePanel } from './DateRangePanel'

global.t = (key) => key

const renderPanel = (props = {}) => {
  const handlers = {
    onConfirm: jest.fn(),
    onCancel: jest.fn(),
    onClear: jest.fn(),
  }
  render(
    <DateRangePanel
      initialMonth={new Date(2026, 0, 1)}
      minDate={new Date(2000, 0, 1)}
      maxDate={new Date(2050, 0, 1)}
      dateFormat="yyyy-MM-dd"
      {...handlers}
      {...props}
    />
  )
  return handlers
}

const clickDay = (label) =>
  fireEvent.click(screen.getByRole('button', { name: new RegExp(label) }))

const confirmedDays = (onConfirm) => {
  const { from, to } = onConfirm.mock.calls[0][0]
  return [from.toDateString(), to.toDateString()]
}

describe('DateRangePanel', () => {
  test('keeps the old presets by default', () => {
    renderPanel()

    expect(screen.getByRole('button', { name: 'Yesterday' })).toBeVisible()
    expect(screen.getByText('Days starting today')).toBeVisible()
    expect(screen.queryByText('Custom range')).not.toBeInTheDocument()
  })

  test('selects two clicked days as a range', () => {
    const { onConfirm } = renderPanel()

    clickDay('January 14th, 2026')
    clickDay('January 20th, 2026')
    fireEvent.click(screen.getByRole('button', { name: 'Confirm' }))

    expect(confirmedDays(onConfirm)).toEqual([
      new Date(2026, 0, 14).toDateString(),
      new Date(2026, 0, 20).toDateString(),
    ])
  })

  test('Select week turns a clicked day into its week', () => {
    const { onConfirm } = renderPanel(getStatisticsPresets())

    fireEvent.click(screen.getByRole('button', { name: 'Select week' }))
    clickDay('January 14th, 2026')
    fireEvent.click(screen.getByRole('button', { name: 'Confirm' }))

    expect(confirmedDays(onConfirm)).toEqual([
      new Date(2026, 0, 12).toDateString(),
      new Date(2026, 0, 18).toDateString(),
    ])
  })

  test('Select month expands the selected day', () => {
    const { onConfirm } = renderPanel({
      ...getStatisticsPresets(),
      initialRange: { from: new Date(2026, 0, 14), to: new Date(2026, 0, 14) },
    })

    fireEvent.click(screen.getByRole('button', { name: 'Select month' }))
    fireEvent.click(screen.getByRole('button', { name: 'Confirm' }))

    expect(confirmedDays(onConfirm)).toEqual([
      new Date(2026, 0, 1).toDateString(),
      new Date(2026, 0, 31).toDateString(),
    ])
  })

  test('a single day shows one date input when asked', () => {
    renderPanel({ ...getStatisticsPresets(), collapseSingleDay: true })

    fireEvent.click(screen.getByRole('button', { name: 'Today' }))
    expect(screen.queryByLabelText('End date')).not.toBeInTheDocument()

    fireEvent.click(screen.getByRole('button', { name: 'Select week' }))
    expect(screen.getByLabelText('End date')).toBeInTheDocument()
  })

  test('starts with one input and no preset when nothing is picked', () => {
    renderPanel({ ...getStatisticsPresets(), collapseSingleDay: true })

    expect(screen.queryByLabelText('End date')).not.toBeInTheDocument()
    expect(document.querySelector('.date-range-preset.active')).toBeNull()

    clickDay('January 14th, 2026')
    expect(screen.queryByLabelText('End date')).not.toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Custom range' })).toHaveClass(
      'active'
    )

    clickDay('January 20th, 2026')
    expect(screen.getByLabelText('End date')).toBeInTheDocument()
  })

  test('Confirm needs a date when asked', () => {
    renderPanel({ confirmRequiresDate: true })

    expect(screen.getByRole('button', { name: 'Confirm' })).toBeDisabled()
  })
})
