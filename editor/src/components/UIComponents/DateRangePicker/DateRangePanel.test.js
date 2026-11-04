import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { DateRangePanel } from './DateRangePanel'

global.t = (key) => key

const renderPanel = (props = {}) => {
  const handlers = { onConfirm: jest.fn(), onCancel: jest.fn() }
  render(
    <DateRangePanel
      initialMonth={new Date(2026, 0, 1)}
      minDate={new Date(2000, 0, 1)}
      maxDate={new Date(2050, 0, 1)}
      {...handlers}
      {...props}
    />
  )
  return handlers
}

const clickDay = (label) =>
  fireEvent.click(screen.getByRole('button', { name: new RegExp(label) }))

const clickButton = (name) =>
  fireEvent.click(screen.getByRole('button', { name }))

const typeDate = (label, value) => {
  const input = screen.getByLabelText(label)
  fireEvent.change(input, { target: { value } })
  fireEvent.blur(input)
}

const confirmedDays = (onConfirm) => {
  const { from, to } = onConfirm.mock.calls[0][0]
  return [from.toDateString(), to.toDateString()]
}

const days = (...dates) =>
  dates.map(([year, month, day]) =>
    new Date(year, month - 1, day).toDateString()
  )

describe('DateRangePanel', () => {
  test('offers the statistics presets', () => {
    renderPanel()

    ;[
      'Today',
      'Select week',
      'Select month',
      'Select year',
      'Custom range',
    ].forEach((name) =>
      expect(screen.getByRole('button', { name })).toBeVisible()
    )
    expect(screen.getByText('Days up to today')).toBeVisible()
  })

  test('selects two clicked days as a range', () => {
    const { onConfirm } = renderPanel()

    clickDay('January 14th, 2026')
    clickDay('January 20th, 2026')
    clickButton('Confirm')

    expect(confirmedDays(onConfirm)).toEqual(days([2026, 1, 14], [2026, 1, 20]))
  })

  test('Select week turns a clicked day into its week', () => {
    const { onConfirm } = renderPanel()

    clickButton('Select week')
    clickDay('January 14th, 2026')
    clickButton('Confirm')

    expect(confirmedDays(onConfirm)).toEqual(days([2026, 1, 12], [2026, 1, 18]))
  })

  test('Select month expands the selected day', () => {
    const { onConfirm } = renderPanel({
      initialRange: { from: new Date(2026, 0, 14), to: new Date(2026, 0, 14) },
    })

    clickButton('Select month')
    clickButton('Confirm')

    expect(confirmedDays(onConfirm)).toEqual(days([2026, 1, 1], [2026, 1, 31]))
  })

  test('Days up to today ends today and counts today', () => {
    const { onConfirm } = renderPanel()

    fireEvent.change(screen.getByRole('spinbutton'), { target: { value: '1' } })
    clickButton('Confirm')

    const today = new Date().toDateString()
    expect(confirmedDays(onConfirm)).toEqual([today, today])
  })

  test('a single day shows one date input', () => {
    renderPanel()

    clickButton('Today')
    expect(screen.queryByLabelText('End date')).not.toBeInTheDocument()

    clickButton('Select year')
    expect(screen.getByLabelText('End date')).toBeInTheDocument()
  })

  test('starts with one input and no preset when nothing is picked', () => {
    renderPanel()

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

  test('Confirm needs a date', () => {
    renderPanel()

    expect(screen.getByRole('button', { name: 'Confirm' })).toBeDisabled()
  })

  test('typing a new date on a single day stays a single day', () => {
    const { onConfirm } = renderPanel({
      initialRange: { from: new Date(2026, 0, 14), to: new Date(2026, 0, 14) },
    })

    typeDate('Start date', 'Jan 5, 2026')
    expect(screen.queryByLabelText('End date')).not.toBeInTheDocument()
    clickButton('Confirm')

    expect(confirmedDays(onConfirm)).toEqual(days([2026, 1, 5], [2026, 1, 5]))
  })

  test('emptying the start date clears the whole range', () => {
    renderPanel({
      initialRange: { from: new Date(2026, 0, 14), to: new Date(2026, 0, 20) },
    })

    typeDate('Start date', '')

    expect(screen.queryByLabelText('End date')).not.toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Confirm' })).toBeDisabled()
  })

  test('an invalid end date no longer counts once its input is gone', () => {
    const { onConfirm } = renderPanel({
      initialRange: { from: new Date(2026, 0, 14), to: new Date(2026, 0, 20) },
    })

    typeDate('End date', 'nonsense')
    expect(screen.getByRole('alert')).toBeInTheDocument()

    clickDay('January 8th, 2026')
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()
    clickButton('Confirm')

    expect(confirmedDays(onConfirm)).toEqual(days([2026, 1, 8], [2026, 1, 8]))
  })

  test('Clear stays open and removes the range and invalid text', () => {
    const { onCancel } = renderPanel()

    typeDate('Start date', 'nonsense')
    expect(screen.getByRole('alert')).toBeInTheDocument()

    clickButton('Clear')

    expect(screen.getByLabelText('Start date')).toHaveValue('')
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()
    expect(onCancel).not.toHaveBeenCalled()
  })
})
