import 'tests/mocks'

import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { RowQuestion } from './RowQuestion'

const mockSetFocused = jest.fn()
jest.mock('hooks/useFocused', () => ({
  useFocused: () => ({ setFocused: mockSetFocused, focused: {} }),
}))

const question = {
  qid: 1,
  title: 'Q1',
  l10ns: { en: { question: 'Title' } },
  answers: [{ aid: 1, code: 'AO01', sortOrder: 0 }],
}

const setup = async (props = {}) => {
  const handlers = {
    duplicateQuestion: jest.fn(),
    deleteQuestion: jest.fn(),
    toggleQuestionCodes: jest.fn(),
  }
  await renderWithProviders(
    <RowQuestion
      question={question}
      language="en"
      provided={{}}
      snapshot={{}}
      focused={{}}
      areCodesVisible
      {...handlers}
      {...props}
    />
  )
  const toggle = await screen.findByRole('button', { name: 'Question menu' })
  await userEvent.click(toggle)
  return { toggle, ...handlers }
}

describe('RowQuestion menu', () => {
  beforeEach(() => {
    mockSetFocused.mockClear()
  })

  test('renders heading, items in order, and separator', async () => {
    await setup()
    expect(screen.getByText('Question actions')).toBeInTheDocument()
    const items = Array.from(document.querySelectorAll('.dropdown-item'))
    const labels = items
      .map((i) => i.textContent)
      .filter((t) =>
        [
          'Duplicate question',
          'Delete question',
          'Check logic',
          'Hide question codes',
        ].includes(t)
      )
    expect(labels).toEqual([
      'Duplicate question',
      'Delete question',
      'Check logic',
      'Hide question codes',
    ])
    expect(document.querySelectorAll('.dropdown-divider')).toHaveLength(1)
    expect(screen.getByTestId('delete-button')).toHaveClass('text-danger')
  })

  test('trailing icon is crossed out when codes are shown', async () => {
    await setup()
    expect(
      screen.getByTestId('toggle-question-codes-button-trailing-icon')
    ).toHaveClass('ri-eye-off-line')
  })

  test('trailing icon is an open eye when codes are hidden', async () => {
    await setup({ areCodesVisible: false })
    expect(screen.getByText('Show question codes')).toBeInTheDocument()
    expect(
      screen.getByTestId('toggle-question-codes-button-trailing-icon')
    ).toHaveClass('ri-eye-line')
  })

  test('toggle calls the handler and menu clicks do not activate the row', async () => {
    const { toggleQuestionCodes } = await setup()
    await userEvent.click(screen.getByTestId('toggle-question-codes-button'))
    expect(toggleQuestionCodes).toHaveBeenCalledTimes(1)
    expect(mockSetFocused).not.toHaveBeenCalled()
  })

  test('Escape closes the menu and focuses the toggle', async () => {
    const { toggle } = await setup()
    await userEvent.keyboard('{Escape}')
    await waitFor(() =>
      expect(toggle).toHaveAttribute('aria-expanded', 'false')
    )
    expect(toggle).toHaveFocus()
  })

  test('opening a second menu closes the first', async () => {
    await renderWithProviders(
      <>
        <RowQuestion
          question={question}
          language="en"
          provided={{}}
          snapshot={{}}
          focused={{}}
        />
        <RowQuestion
          question={{ ...question, qid: 2 }}
          language="en"
          provided={{}}
          snapshot={{}}
          focused={{}}
        />
      </>
    )
    const [a, b] = await screen.findAllByRole('button', {
      name: 'Question menu',
    })
    await userEvent.click(a)
    expect(a).toHaveAttribute('aria-expanded', 'true')
    await userEvent.click(b)
    expect(b).toHaveAttribute('aria-expanded', 'true')
    await waitFor(() => expect(a).toHaveAttribute('aria-expanded', 'false'))
    expect(document.querySelectorAll('.dropdown-menu.show')).toHaveLength(1)
  })
})
