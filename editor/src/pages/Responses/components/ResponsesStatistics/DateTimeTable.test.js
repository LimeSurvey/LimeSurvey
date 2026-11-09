import 'tests/mocks'

import { useState } from 'react'
import { fireEvent, render, screen } from '@testing-library/react'

import { useAppState, useQuestionResponses } from 'hooks'

import { DateTimeTable } from './DateTimeTable.js'

global.t = (key) => key

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useAppState: jest.fn(),
  useQuestionResponses: jest.fn(),
}))

const BUTTON = 'Decrypt response data and show statistics'

const Table = ({ encrypted }) => {
  const [calendarState, setCalendarState] = useState({})
  return (
    <DateTimeTable
      surveyId={1}
      question={{ qid: 5, code: 'Q5', title: 'Title', encrypted }}
      calendarState={calendarState}
      onCalendarStateChange={setCalendarState}
    />
  )
}

describe('DateTimeTable', () => {
  beforeEach(() => {
    useAppState.mockReturnValue([true])
    useQuestionResponses.mockClear()
    useQuestionResponses.mockReturnValue({
      columns: [],
      rows: [],
      isLoading: true,
      hasNextPage: false,
      fetchNextPage: jest.fn(),
      isFetchingNextPage: false,
    })
  })

  test('an encrypted question asks before loading its answers', () => {
    render(<Table encrypted />)

    expect(
      screen.getByText(
        'The date/time data for this question was set up to be encrypted.'
      )
    ).toBeInTheDocument()
    expect(useQuestionResponses).not.toHaveBeenCalled()

    fireEvent.click(screen.getByRole('button', { name: BUTTON }))

    expect(useQuestionResponses).toHaveBeenCalled()
    expect(screen.queryByRole('button', { name: BUTTON })).toBeNull()
  })

  test('without the permission to read responses there is no button', () => {
    useAppState.mockReturnValue([false])
    render(<Table encrypted />)

    expect(screen.queryByRole('button', { name: BUTTON })).toBeNull()
    expect(useQuestionResponses).not.toHaveBeenCalled()
  })

  test('a question that is not encrypted loads right away', () => {
    render(<Table encrypted={false} />)

    expect(screen.queryByRole('button', { name: BUTTON })).toBeNull()
    expect(useQuestionResponses).toHaveBeenCalled()
  })
})
