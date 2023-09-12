import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { Language } from 'helpers'
import { QuestionTypeInfo, TextQuestion } from 'components/Survey/QuestionTypes'

import { Question } from '../../../Questions/Question'

export default {
  title: 'QuestionTypes/TextQuestion/LongText',
}

let longTextQuestion = {
  gid: 1,
  qid: 1,
  sid: 1,
  l10ns: {
    en: {
      id: 1,
      language: Language.EN,
      qid: 1,
      question: 'Long Text',
      script: '',
    },
  },
  type: QuestionTypeInfo.LONG_FREE_TEXT,
}

export const QuestionPreview = () => {
  return (
    <Question
      language={Language.EN}
      question={longTextQuestion}
      update={() => {}}
      handleRemove={() => {}}
    />
  )
}

export const Basic = () => {
  return <TextQuestion question={longTextQuestion} handleUpdate={() => {}} />
}

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputField = canvas.getByTestId('text-question-answer-input')

  await step('Expect the question to have only one answer field', () => {
    expect(canvas.getAllByTestId('text-question-answer-input').length).toBe(1)
  })

  await step('Expect the question answer tag to be of type TEXTAREA', () => {
    expect(inputField.tagName).toBe('TEXTAREA')
  })

  await step('Expect the answer textarea to have 4 rows', () => {
    expect(inputField.attributes.rows.value).toBe('4')
  })

  await step('Expect the input field to accept numbers and letters', () => {
    userEvent.type(
      inputField,
      'a long text that contain random words and random numbers 123456789 wrriten for the expect text to be written storybook test.'
    )

    expect(inputField.value).toBe(
      'a long text that contain random words and random numbers 123456789 wrriten for the expect text to be written storybook test.'
    )
  })
}

export const MaxFiveCharacters = () => {
  const longTextWith5MaxCharactersQuestion = {
    ...longTextQuestion,
    l10ns: {
      ...longTextQuestion.l10ns,
      en: {
        ...longTextQuestion.l10ns.en,
        question: 'Long Text With Max 5 Characters',
      },
    },
    attributes: {
      maximum_chars: {
        value: '5',
        qaid: 1,
      },
    },
  }

  return (
    <TextQuestion
      question={longTextWith5MaxCharactersQuestion}
      handleUpdate={() => {}}
    />
  )
}

MaxFiveCharacters.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputField = canvas.getByTestId('text-question-answer-input')

  await step('Expect the question input to accept no more than 5 chars', () => {
    userEvent.type(inputField, 'random text')
    expect(inputField.value).toBe('rando')
  })
}
