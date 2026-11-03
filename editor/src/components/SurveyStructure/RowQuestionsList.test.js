import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { DragDropContext } from '@hello-pangea/dnd'
import { renderWithProviders } from 'tests/testUtils'

import { RowQuestionsList } from './RowQuestionsList'

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
  beforeEach(() => {
    mockShowCode = true
    mockShowNumber = true
    mockUpdate.mockClear()
  })

  test('hides codes when they are visible', async () => {
    await openMenuAndToggle()
    expect(mockUpdate).toHaveBeenCalledWith({
      showQNumCode: { showCode: false, showNumber: true },
    })
  })

  test('only flips showCode and keeps question numbers shown', async () => {
    mockShowCode = false
    mockShowNumber = true
    await openMenuAndToggle()
    expect(mockUpdate).toHaveBeenCalledWith({
      showQNumCode: { showCode: true, showNumber: true },
    })
  })

  test('only flips showCode and keeps question numbers hidden', async () => {
    mockShowCode = true
    mockShowNumber = false
    await openMenuAndToggle()
    expect(mockUpdate).toHaveBeenCalledWith({
      showQNumCode: { showCode: false, showNumber: false },
    })
  })
})
