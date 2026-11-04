import 'tests/mocks'

import { useState } from 'react'
import {
  fireEvent,
  render,
  screen,
  waitFor,
  within,
} from '@testing-library/react'

import { useDateTimeValues } from 'hooks'

import { DateTimeCalendar } from './DateTimeCalendar.js'

global.t = (key) => key

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useDateTimeValues: jest.fn(),
}))

const values = {
  hasTime: true,
  total: 4,
  days: {
    '2026-01-14': { '09:00:00': 2, '10:00:00': 1 },
    '2026-01-16': { '08:00:00': 1 },
  },
}
const stats = {
  earliest: '2026-01-14',
  latest: '2026-01-16',
  days: { '2026-01-14': 3, '2026-01-16': 1 },
}

const Card = ({ question }) => {
  const [calendarState, setCalendarState] = useState({})
  return (
    <DateTimeCalendar
      surveyId={1}
      question={{ qid: 5, title: 'When did you start?', ...question }}
      stats={stats}
      calendarState={calendarState}
      onCalendarStateChange={setCalendarState}
    />
  )
}

const renderCard = (question = { hasTime: true }) =>
  render(<Card question={question} />)

const dayButton = (day) =>
  screen.getByRole('button', { name: new RegExp(`January ${day}, 2026`) })

const openLink = () => {
  const link = screen.getByRole('button', {
    name: 'View responses by date range',
  })
  link.focus()
  fireEvent.click(link)
  return link
}

const inDialog = (name) => within(screen.getByRole('dialog', { name }))

const columnHeaders = (dialog) =>
  dialog.getAllByRole('columnheader').map((cell) => cell.textContent)

describe('DateTimeCalendar breakdown', () => {
  beforeEach(() => {
    useDateTimeValues.mockReturnValue({
      values,
      isLoading: false,
      isError: false,
    })
  })

  test('a day with responses opens its times in a named dialog', async () => {
    renderCard()

    fireEvent.click(dayButton(14))

    const dialog = inDialog('When did you start?')
    expect(columnHeaders(dialog)).toEqual(['Time', 'Responses'])
    expect(dialog.getByLabelText('Date')).toHaveValue('Jan 14, 2026')
    expect(dialog.queryByLabelText('End date')).not.toBeInTheDocument()
    expect(dialog.getAllByRole('row')).toHaveLength(3)
  })

  test('a day without responses and a date-only day only select', () => {
    const { unmount } = renderCard()
    fireEvent.click(dayButton(15))
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
    unmount()

    renderCard({ hasTime: false })
    fireEvent.click(dayButton(14))
    expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
  })

  test('the link picks a range and shows it with dates', async () => {
    renderCard()

    openLink()
    const picker = inDialog('View responses by date range')
    fireEvent.click(picker.getByRole('button', { name: 'Select week' }))
    fireEvent.click(picker.getByRole('button', { name: /January 14th, 2026/ }))
    fireEvent.click(picker.getByRole('button', { name: 'Confirm' }))

    const dialog = await waitFor(() => inDialog('When did you start?'))
    expect(dialog.getByLabelText('Start date')).toHaveValue('Jan 12, 2026')
    expect(dialog.getByLabelText('End date')).toHaveValue('Jan 18, 2026')
    expect(dialog.getByText('until')).toBeInTheDocument()
    expect(columnHeaders(dialog)).toEqual(['Date', 'Time', 'Responses'])
    expect(dialog.getAllByRole('row')).toHaveLength(4)
  })

  test('a date-only question shows dates without a time column', async () => {
    renderCard({ hasTime: false })

    openLink()
    const picker = inDialog('View responses by date range')
    fireEvent.click(picker.getByRole('button', { name: 'Select week' }))
    fireEvent.click(picker.getByRole('button', { name: /January 14th, 2026/ }))
    fireEvent.click(picker.getByRole('button', { name: 'Confirm' }))

    const dialog = await waitFor(() => inDialog('When did you start?'))
    expect(columnHeaders(dialog)).toEqual(['Date', 'Responses'])
  })

  test('Cancel in the picker goes back to the breakdown it came from', async () => {
    renderCard()

    fireEvent.click(dayButton(14))
    fireEvent.click(
      inDialog('When did you start?').getByRole('button', {
        name: 'Open calendar',
      })
    )
    const picker = await waitFor(() => inDialog('View responses by date range'))
    expect(picker.getByLabelText('Start date')).toHaveValue('Jan 14, 2026')
    fireEvent.click(picker.getByRole('button', { name: 'Cancel' }))

    const dialog = await waitFor(() => inDialog('When did you start?'))
    expect(dialog.getByLabelText('Date')).toHaveValue('Jan 14, 2026')
  })

  test('focus returns to the day after going through the picker', async () => {
    renderCard()

    const day = dayButton(14)
    day.focus()
    fireEvent.click(day)
    fireEvent.click(
      inDialog('When did you start?').getByRole('button', {
        name: 'Open calendar',
      })
    )
    const picker = await waitFor(() => inDialog('View responses by date range'))
    fireEvent.click(picker.getByRole('button', { name: 'Cancel' }))
    const dialog = await waitFor(() => inDialog('When did you start?'))
    fireEvent.click(dialog.getByRole('button', { name: 'Close' }))

    await waitFor(() =>
      expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
    )
    await waitFor(() => expect(dayButton(14)).toHaveFocus())
  })

  test('focus returns to the link after the range flow', async () => {
    renderCard()

    const link = openLink()
    const picker = inDialog('View responses by date range')
    fireEvent.click(picker.getByRole('button', { name: 'Today' }))
    fireEvent.click(picker.getByRole('button', { name: 'Confirm' }))
    const dialog = await waitFor(() => inDialog('When did you start?'))
    fireEvent.click(dialog.getByRole('button', { name: 'Close' }))

    await waitFor(() =>
      expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
    )
    await waitFor(() => expect(link).toHaveFocus())
  })
})
