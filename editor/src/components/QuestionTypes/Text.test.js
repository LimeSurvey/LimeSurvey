// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { getQuestionTypeInfo } from './getQuestionTypeInfo'
import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { sleep } from 'helpers'

const LONG_TEXT = `This is a text that me Rami, wrote just to test the long text question. I would like to say that I,
  Rami, an Egyptian from Egypt, wrote this text to test the long text question, and I would like to say that I, Rami, an Egyptian
  from Egypt, wrote this text to say that I'm actually a robot from the future.`

describe('QuestionTypes/Text - ShortText', () => {
  const shortTextQuestion = mockQuestionType(getQuestionTypeInfo().SHORT_TEXT, {
    image: '',
  })

  test('Should test short text question', async () => {
    await renderWithProviders(<QuestionPreview question={shortTextQuestion} />)

    const answer = await waitFor(() =>
      screen.getByTestId('text-question-answer-input')
    )
    await sleep(200)
    expect(answer).toBeInTheDocument()
    await userEvent.type(answer, 'My name is Tom Riddle.', { delay: 0 })
    expect(answer.value).toBe('My name is Tom Riddle.')
  })
})

describe('QuestionTypes/Text - LongText', () => {
  const longTextQuestion = mockQuestionType(getQuestionTypeInfo().LONG_TEXT)

  test('Should test long text question', async () => {
    await renderWithProviders(<QuestionPreview question={longTextQuestion} />)

    const answer = await waitFor(() =>
      screen.getByTestId('text-question-answer-input')
    )
    await sleep(200)
    expect(answer).toBeInTheDocument()
    await userEvent.type(answer, LONG_TEXT, { delay: 0 })
    expect(answer.value).toBe(LONG_TEXT)
  })
})
