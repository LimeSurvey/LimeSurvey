import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { DragDropContext } from '@hello-pangea/dnd'
import { renderWithProviders } from 'tests/testUtils'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

import { RowQuestionsList } from './RowQuestionsList'

const APP_STATE_KEY = ['appState', STATES.SHOW_QUESTION_CODES_IN_EDITOR]

const questions = [
  { qid: 1, title: 'Q1', l10ns: { en: { question: 'One' } }, answers: [] },
]

const openMenuAndToggle = async () => {
  await renderWithProviders(
    <DragDropContext onDragEnd={() => {}}>
      <RowQuestionsList
        questions={questions}
        language="en"
        groupIndex={0}
        handleUpdate={() => {}}
      />
    </DragDropContext>
  )
  await userEvent.click(
    await screen.findByRole('button', { name: 'Question menu' })
  )
  await userEvent.click(screen.getByTestId('toggle-question-codes-button'))
}

describe('RowQuestionsList question codes toggle', () => {
  afterEach(() => {
    queryClient.removeQueries({ queryKey: APP_STATE_KEY, exact: true })
  })

  test('shows question codes in the editor', async () => {
    queryClient.setQueryData(APP_STATE_KEY, false)
    await openMenuAndToggle()
    expect(queryClient.getQueryData(APP_STATE_KEY)).toBe(true)
  })

  test('hides question codes in the editor', async () => {
    queryClient.setQueryData(APP_STATE_KEY, true)
    await openMenuAndToggle()
    expect(queryClient.getQueryData(APP_STATE_KEY)).toBe(false)
  })
})
