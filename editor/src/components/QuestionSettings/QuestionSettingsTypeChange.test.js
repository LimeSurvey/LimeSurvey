import 'tests/mocks'

import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'
import { STATES } from 'helpers'
import { queryClient } from 'queryClient'

import { QuestionSettings } from './QuestionSettings'

const question = {
  qid: 10,
  gid: 1,
  type: 'L',
  questionThemeName: 'listradio',
  title: 'Q1',
  attributes: {},
  l10ns: { en: { question: 'Question' } },
  answers: [
    { aid: 1, code: 'A1' },
    { aid: 2, code: 'A2' },
  ],
}

const mockSurvey = {
  sid: 1,
  answerOptionLayouts: { 'L': '1-0', '!': '1-0' },
  questionGroups: [{ gid: 1, questions: [question] }],
}
const mockUpdate = jest.fn()
const mockAddToBuffer = jest.fn()
const mockRemoveFromBuffer = jest.fn()

jest.mock('hooks/useSurvey', () => ({
  useSurvey: () => ({ survey: mockSurvey, update: mockUpdate, language: 'en' }),
}))
jest.mock('hooks/useFocused', () => ({
  useFocused: () => ({
    focused: mockSurvey.questionGroups[0].questions[0],
    groupIndex: 0,
    questionIndex: 0,
    setFocused: jest.fn(),
    unFocus: jest.fn(),
  }),
}))
jest.mock('hooks/useBuffer', () => ({
  useBuffer: () => ({
    addToBuffer: mockAddToBuffer,
    removeFromBuffer: mockRemoveFromBuffer,
  }),
}))

const changeTypeTo = async (theme) => {
  await userEvent.click(
    within(screen.getByTestId('question-type')).getByRole('button')
  )
  await userEvent.click(await screen.findByTestId(`question-type-${theme}`))
}

const updatedQuestion = () =>
  mockUpdate.mock.calls.at(-1)[0].questionGroups[0].questions[0]

describe('QuestionSettings type change', () => {
  beforeEach(async () => {
    jest.clearAllMocks()
    mockSurvey.questionGroups[0].questions[0] = { ...question }
    queryClient.setQueryData(
      ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
      true
    )
    queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], false)
    queryClient.setQueryData(
      ['appState', STATES.SURVEY_REFRESH_REQUIRED],
      false
    )
    await renderWithProviders(<QuestionSettings surveyId={1} />)
  })

  test('keeps the answer options without asking for a compatible type', async () => {
    await changeTypeTo('bootstrap_dropdown')

    expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
    expect(updatedQuestion().type).toBe('!')
    expect(updatedQuestion().answers).toHaveLength(2)
    expect(mockRemoveFromBuffer).not.toHaveBeenCalled()
  })

  test('changes the type without asking when there are no answer options', async () => {
    mockSurvey.questionGroups[0].questions[0].answers = []

    await changeTypeTo('shortfreetext')

    expect(screen.queryByRole('dialog')).not.toBeInTheDocument()
    expect(updatedQuestion().type).toBe('S')
    expect(mockRemoveFromBuffer).not.toHaveBeenCalled()
    expect(
      queryClient.getQueryData(['appState', STATES.SURVEY_REFRESH_REQUIRED])
    ).not.toBe(true)
  })

  test('cancel leaves the question unchanged', async () => {
    await changeTypeTo('shortfreetext')

    const dialog = await screen.findByRole('dialog', {
      name: 'Change question type',
    })
    await userEvent.click(
      within(dialog).getByRole('button', { name: 'Cancel' })
    )

    expect(mockUpdate).not.toHaveBeenCalled()
    expect(mockAddToBuffer).not.toHaveBeenCalled()
  })

  test('confirm clears the answer options and queues the type change', async () => {
    await changeTypeTo('shortfreetext')

    const dialog = await screen.findByRole('dialog', {
      name: 'Change question type',
    })
    await userEvent.click(
      within(dialog).getByRole('button', { name: 'Confirm' })
    )

    expect(updatedQuestion().type).toBe('S')
    expect(updatedQuestion().answers).toEqual([])
    expect(mockRemoveFromBuffer).toHaveBeenCalledWith(10, 'answer')
    expect(mockAddToBuffer).toHaveBeenCalledWith(
      expect.objectContaining({
        id: 10,
        props: expect.objectContaining({ type: 'S' }),
      })
    )
  })
})
