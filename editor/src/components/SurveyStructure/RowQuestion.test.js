import 'tests/mocks'

import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { RowQuestion } from './RowQuestion'

const question = {
  qid: 1,
  title: 'Q1',
  l10ns: { en: { question: 'Title' } },
  answers: [{ aid: 1, code: 'AO01', sortOrder: 0 }],
}

const setup = async (props = {}) => {
  const handlers = {
    onSelect: jest.fn(),
    duplicateQuestion: jest.fn(),
    deleteQuestion: jest.fn(),
    toggleQuestionCodes: jest.fn(),
  }
  await renderWithProviders(
    <RowQuestion
      question={question}
      language="en"
      questionIndex={0}
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
    const { toggleQuestionCodes, onSelect } = await setup()
    await userEvent.click(screen.getByTestId('toggle-question-codes-button'))
    expect(toggleQuestionCodes).toHaveBeenCalledTimes(1)
    expect(onSelect).not.toHaveBeenCalled()
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
        <RowQuestion question={question} language="en" />
        <RowQuestion question={{ ...question, qid: 2 }} language="en" />
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

describe('RowQuestion handlers', () => {
  test('pass the question and its index, so the list can share them between rows', async () => {
    const { toggle, onSelect, duplicateQuestion, deleteQuestion } = await setup(
      { questionIndex: 3 }
    )

    await userEvent.click(screen.getByTestId('duplicate-button'))
    expect(duplicateQuestion).toHaveBeenCalledWith(question, 3)

    await userEvent.click(toggle)
    await userEvent.click(screen.getByTestId('delete-button'))
    expect(deleteQuestion).toHaveBeenCalledWith(3)

    await userEvent.click(screen.getByTestId('sidebar-row-question'))
    expect(onSelect).toHaveBeenCalledWith(question, 3)
  })
})
