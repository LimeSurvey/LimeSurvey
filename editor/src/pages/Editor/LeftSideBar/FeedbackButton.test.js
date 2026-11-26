import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { FeedbackButton } from './FeedbackButton'

const mockShowFeedbackForm = jest.fn()
jest.mock('hooks/feedback/useFeedbackForm', () => ({
  useFeedbackForm: () => ({ showFeedbackForm: mockShowFeedbackForm }),
}))

describe('FeedbackButton', () => {
  beforeEach(() => {
    mockShowFeedbackForm.mockClear()
  })

  test('is a button with an accessible name', async () => {
    await renderWithProviders(<FeedbackButton />)
    expect(
      screen.getByRole('button', { name: 'Help us with your feedback' })
    ).toBeInTheDocument()
  })

  test('opens the feedback form from the keyboard', async () => {
    await renderWithProviders(<FeedbackButton />)
    await userEvent.tab()
    expect(
      screen.getByRole('button', { name: 'Help us with your feedback' })
    ).toHaveFocus()
    await userEvent.keyboard('{Enter}')
    expect(mockShowFeedbackForm).toHaveBeenCalledTimes(1)
  })
})
