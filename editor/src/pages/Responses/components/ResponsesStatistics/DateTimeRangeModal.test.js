import 'tests/mocks'

import { render, screen } from '@testing-library/react'

import { useDateTimeValues } from 'hooks'

import { DateTimeRangeModal } from './DateTimeRangeModal.js'

global.t = (key) => key

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useDateTimeValues: jest.fn(),
}))

const question = { qid: 5, title: 'When did you start?', hasTime: true }
const day = { from: '2026-01-14', to: '2026-01-14' }

describe('DateTimeRangeModal', () => {
  beforeEach(() => {
    useDateTimeValues.mockReturnValue({
      values: {
        hasTime: true,
        total: 2,
        days: { '2026-01-14': { '09:00:00': 2 } },
      },
      isLoading: false,
      isError: false,
    })
  })

  test('closing keeps the results on screen while the modal fades out', () => {
    const { rerender } = render(
      <DateTimeRangeModal surveyId={1} question={question} range={day} />
    )
    expect(screen.getAllByRole('row')).toHaveLength(2)

    rerender(
      <DateTimeRangeModal surveyId={1} question={question} range={null} />
    )

    expect(
      screen.queryByText('There are no responses for this date.')
    ).not.toBeInTheDocument()
    expect(screen.getAllByRole('row')).toHaveLength(2)
  })
})
