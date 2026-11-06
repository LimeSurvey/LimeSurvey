import 'tests/mocks'

import { useState } from 'react'
import { fireEvent, render, screen } from '@testing-library/react'

import { useDateTimeValues } from 'hooks'

import { EncryptedDateTimeCalendar } from './EncryptedDateTimeCalendar.js'

global.t = (key) => key

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useDateTimeValues: jest.fn(),
}))

const BUTTON = 'Decrypt response data and show statistics'
const values = {
  hasTime: true,
  total: 2,
  days: { '2026-01-14': { '09:00:00': 2 } },
}

const Card = () => {
  const [calendarState, setCalendarState] = useState({})
  return (
    <EncryptedDateTimeCalendar
      surveyId={1}
      question={{ qid: 5, title: 'Title', hasTime: true, encrypted: true }}
      calendarState={calendarState}
      onCalendarStateChange={setCalendarState}
    />
  )
}

const mockValues = (result) =>
  useDateTimeValues.mockReturnValue({
    values: undefined,
    isLoading: false,
    isError: false,
    ...result,
  })

const lastOptions = () => useDateTimeValues.mock.lastCall[3]

describe('EncryptedDateTimeCalendar', () => {
  test('nothing is decrypted until asked', () => {
    mockValues({})
    render(<Card />)

    expect(
      screen.getByText(
        'The date/time data for this question was set up to be encrypted.'
      )
    ).toBeInTheDocument()
    expect(lastOptions()).toMatchObject({ enabled: false, decrypt: true })
  })

  test('the button loads the values and keeps the focus nearby', () => {
    mockValues({ isLoading: true })
    render(<Card />)

    fireEvent.click(screen.getByRole('button', { name: BUTTON }))

    expect(lastOptions()).toMatchObject({ enabled: true, decrypt: true })
    expect(screen.queryByRole('button', { name: BUTTON })).toBeNull()
    expect(screen.getByRole('status', { name: 'Loading' })).toBeInTheDocument()
    expect(document.activeElement).toHaveClass(
      'responses-statistics-calendar-decrypted'
    )
  })

  test('the decrypted values feed the calendar', () => {
    mockValues({ values })
    render(<Card />)

    fireEvent.click(screen.getByRole('button', { name: BUTTON }))

    expect(
      screen.getByRole('button', { name: /January 14, 2026, 2 responses/ })
    ).toBeInTheDocument()
    expect(screen.getByLabelText('Earliest date')).toHaveValue('Jan 14, 2026')
  })

  test('a failed request is announced', () => {
    mockValues({ isError: true })
    render(<Card />)

    fireEvent.click(screen.getByRole('button', { name: BUTTON }))

    expect(screen.getByRole('alert')).toHaveTextContent(
      'The responses could not be loaded.'
    )
  })
})
