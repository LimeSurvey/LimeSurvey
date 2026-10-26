import 'tests/mocks'

import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { RowQuestion } from './RowQuestion'

let mockShowCode = true
let mockShowNumber = true
const mockUpdate = jest.fn()
jest.mock('hooks/useSurvey', () => ({
  useSurvey: () => ({
    survey: {
      ...require('helpers/data/survey-detail.json').survey,
      showQNumCode: { showCode: mockShowCode, showNumber: mockShowNumber },
    },
    update: mockUpdate,
  }),
}))

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
    assignNumericAnswerCodes: jest.fn(() => true),
  }
  await renderWithProviders(
    <RowQuestion
      question={question}
      language="en"
      provided={{}}
      snapshot={{}}
      focused={{}}
      {...handlers}
      {...props}
    />
  )
  const toggle = await screen.findByRole('menu', { name: 'Question menu' })
  await userEvent.click(toggle)
  return { toggle, ...handlers }
}

describe('RowQuestion menu', () => {
  beforeEach(() => {
    mockShowCode = true
    mockShowNumber = true
    mockUpdate.mockClear()
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
          'Show/hide question codes',
          'Assign numeric answer codes',
        ].includes(t)
      )
    expect(labels).toEqual([
      'Duplicate question',
      'Delete question',
      'Check logic',
      'Show/hide question codes',
      'Assign numeric answer codes',
    ])
    expect(document.querySelectorAll('.dropdown-divider')).toHaveLength(1)
    expect(screen.getByTestId('delete-button')).toHaveClass('text-danger')
  })

  test('trailing eye icon reflects code visibility', async () => {
    await setup()
    expect(
      screen.getByTestId('toggle-question-codes-button-trailing-icon')
    ).toHaveClass('ri-eye-line')
  })

  test('trailing icon is crossed out when codes are hidden', async () => {
    mockShowCode = false
    await setup()
    expect(
      screen.getByTestId('toggle-question-codes-button-trailing-icon')
    ).toHaveClass('ri-eye-off-line')
  })

  test('toggle updates the survey and menu clicks do not activate the row', async () => {
    await setup()
    await userEvent.click(screen.getByTestId('toggle-question-codes-button'))
    expect(mockUpdate).toHaveBeenCalledWith({
      showQNumCode: { showCode: false, showNumber: false },
    })
    expect(mockSetFocused).not.toHaveBeenCalled()
  })

  test('toggle sets showCode and showNumber to the same value when diverged', async () => {
    mockShowCode = false
    mockShowNumber = true
    await setup()
    await userEvent.click(screen.getByTestId('toggle-question-codes-button'))
    expect(mockUpdate).toHaveBeenCalledWith({
      showQNumCode: { showCode: true, showNumber: true },
    })
  })

  test('Escape closes the menu and focuses the toggle', async () => {
    const { toggle } = await setup()
    await userEvent.keyboard('{Escape}')
    await waitFor(() =>
      expect(toggle).toHaveAttribute('aria-expanded', 'false')
    )
    expect(toggle).toHaveFocus()
  })

  test('assign numeric codes calls the handler; disabled when survey active', async () => {
    const { assignNumericAnswerCodes } = await setup()
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    expect(assignNumericAnswerCodes).toHaveBeenCalledWith(question)
  })

  test('assign numeric codes is disabled for active surveys', async () => {
    await setup({ isSurveyActive: true })
    expect(screen.getByTestId('assign-numeric-codes-button')).toHaveClass(
      'disabled'
    )
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
    const [a, b] = await screen.findAllByRole('menu', {
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
