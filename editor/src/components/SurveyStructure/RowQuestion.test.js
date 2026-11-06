import 'tests/mocks'

import { act, renderHook, screen, waitFor } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'
import { Toast } from 'helpers/Alert'
import { STATES } from 'helpers'
import { queryClient } from 'queryClient'
import { useOperationCallback } from 'hooks/useOperationCallback'

import { RowQuestion } from './RowQuestion'

jest.mock('helpers/Alert', () => ({
  ...jest.requireActual('helpers/Alert'),
  Toast: jest.fn(),
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
    queryClient.setQueryData([STATES.OPERATION_FINISH_SUBSCRIPTIONS], new Map())
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
          'Assign numeric answer codes',
        ].includes(t)
      )
    expect(labels).toEqual([
      'Duplicate question',
      'Delete question',
      'Check logic',
      'Hide question codes',
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
    await setup({ areCodesVisible: false })
    expect(screen.getByText('Show question codes')).toBeInTheDocument()
    expect(
      screen.getByTestId('toggle-question-codes-button-trailing-icon')
    ).toHaveClass('ri-eye-off-line')
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

  const answerUpdateSubscriptions = () =>
    queryClient
      .getQueryData([STATES.OPERATION_FINISH_SUBSCRIPTIONS])
      ?.get('answer_update') ?? []

  test('assign numeric codes asks for confirmation before changing anything', async () => {
    const { assignNumericAnswerCodes } = await setup()
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    expect(
      await screen.findByText(/Conditions, expressions, default answers/)
    ).toBeInTheDocument()
    expect(assignNumericAnswerCodes).not.toHaveBeenCalled()
    await userEvent.click(screen.getByTestId('confirm-modal-confirm-button'))
    expect(assignNumericAnswerCodes).toHaveBeenCalledWith(question)
  })

  test('the confirmation lists questions whose logic uses the code', async () => {
    await setup()
    const loaded = queryClient.getQueryData([STATES.SURVEY])
    queryClient.setQueryData([STATES.SURVEY], {
      ...loaded,
      survey: {
        ...loaded.survey,
        language: 'en',
        questionGroups: [
          {
            gid: 1,
            gRelevance: '1',
            l10ns: { en: { groupName: 'G1' } },
            questions: [
              question,
              { qid: 2, title: 'Q2', relevance: 'Q1 == "AO01"', l10ns: {} },
              { qid: 3, title: 'Q3', relevance: 'Q10 == "1"', l10ns: {} },
            ],
          },
        ],
      },
    })
    try {
      await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
      expect(
        await screen.findByTestId('numeric-codes-references')
      ).toHaveTextContent(/^Q2$/)
    } finally {
      queryClient.setQueryData([STATES.SURVEY], loaded)
    }
  })

  test('the success toast waits for the save result', async () => {
    Toast.mockClear()
    await setup()
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )
    expect(Toast).not.toHaveBeenCalled()
    expect(answerUpdateSubscriptions()).toHaveLength(1)
    const [subscription] = answerUpdateSubscriptions()
    expect(subscription.id).toBe(question.qid)
    act(() => subscription.callback())
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({
        message: 'Numeric answer codes assigned.',
        className: 'import-survey-toast import-survey-toast--success',
      })
    )
  })

  test('an earlier answer save of the question does not trigger the toast', async () => {
    Toast.mockClear()
    await setup()
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )
    const answerUpdate = (code) => ({
      id: question.qid,
      entity: 'answer',
      op: 'update',
      props: [{ aid: 1, code, sortOrder: 0 }],
    })
    const { result } = renderHook(() => useOperationCallback(), {
      wrapper: ({ children }) => (
        <QueryClientProvider client={queryClient}>
          {children}
        </QueryClientProvider>
      ),
    })

    act(() =>
      result.current.triggerCallbacks([answerUpdate('AO01')], {
        operationsApplied: 1,
      })
    )
    expect(Toast).not.toHaveBeenCalled()
    expect(answerUpdateSubscriptions()).toHaveLength(1)

    act(() =>
      result.current.triggerCallbacks([answerUpdate('1')], {
        operationsApplied: 1,
      })
    )
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({ message: 'Numeric answer codes assigned.' })
    )
    expect(answerUpdateSubscriptions()).toHaveLength(0)
  })

  test('no save subscription is left when nothing was queued', async () => {
    await setup({ assignNumericAnswerCodes: jest.fn(() => false) })
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )
    expect(answerUpdateSubscriptions()).toHaveLength(0)
  })

  test('a throwing assignment shows an error and leaves no subscription', async () => {
    Toast.mockClear()
    const consoleError = jest.spyOn(console, 'error').mockImplementation()
    await setup({
      assignNumericAnswerCodes: jest.fn(() => {
        throw new Error('boom')
      }),
    })
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )
    expect(answerUpdateSubscriptions()).toHaveLength(0)
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({
        message: 'Numeric answer codes could not be assigned.',
      })
    )
    consoleError.mockRestore()
  })

  test('a failed save shows an error toast and logs the error', async () => {
    Toast.mockClear()
    const consoleError = jest.spyOn(console, 'error').mockImplementation()
    await setup()
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )
    const [subscription] = answerUpdateSubscriptions()
    act(() => subscription.onError({ error: 'boom' }))
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({
        message: 'Numeric answer codes could not be assigned.',
      })
    )
    expect(consoleError).toHaveBeenCalled()
    consoleError.mockRestore()
  })

  test('assign numeric codes shows an info toast when codes are already numeric', async () => {
    Toast.mockClear()
    const { assignNumericAnswerCodes } = await setup({
      question: { ...question, answers: [{ aid: 1, code: '1', sortOrder: 0 }] },
    })
    await userEvent.click(screen.getByTestId('assign-numeric-codes-button'))
    expect(Toast).toHaveBeenCalledWith(
      expect.objectContaining({
        message: 'Answer options already use numeric codes.',
        className: 'import-survey-toast import-survey-toast--info',
      })
    )
    expect(assignNumericAnswerCodes).not.toHaveBeenCalled()
    expect(screen.queryByTestId('confirm-modal-confirm-button')).toBeNull()
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
