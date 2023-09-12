import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { Language } from 'helpers'
import { QuestionTypeInfo, TextQuestion } from 'components/Survey/QuestionTypes'

import { Question } from '../../../Questions/Question'

export default {
  title: 'QuestionTypes/TextQuestion/ShortText',
}

let shortTextQuestion = {
  gid: 1,
  qid: 1,
  sid: 1,
  l10ns: {
    en: {
      id: 1,
      language: Language.EN,
      qid: 1,
      question: 'Short Text',
      script: '',
    },
  },
  type: QuestionTypeInfo.SHORT_FREE_TEXT,
}

export const QuestionPreview = () => {
  return (
    <Question
      language={Language.EN}
      question={shortTextQuestion}
      update={() => {}}
      handleRemove={() => {}}
    />
  )
}

export const Basic = () => {
  return (
    <>
      <h1>Basic</h1>
      <TextQuestion question={shortTextQuestion} handleUpdate={() => {}} />
    </>
  )
}

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputField = canvas.getByTestId('text-question-answer-input')

  await step('Expect the question to have only one answer field', () => {
    expect(canvas.getAllByTestId('text-question-answer-input').length).toBe(1)
  })

  await step('Expect the question answer tag to be of type INPUT', () => {
    expect(inputField.tagName).toBe('INPUT')
  })

  await step('Expect the input field to accept numbers and letters', () => {
    userEvent.type(inputField, 'random text with some random number 123456789.')
    expect(inputField.value).toBe(
      'random text with some random number 123456789.'
    )
  })
}

export const MaxFiveCharacters = () => {
  const shortTextWith5MaxCharactersQuestion = {
    ...shortTextQuestion,
    l10ns: {
      ...shortTextQuestion.l10ns,
      en: {
        ...shortTextQuestion.l10ns.en,
        question: 'Short Text With Max 5 Characters',
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
      question={shortTextWith5MaxCharactersQuestion}
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

export const NumbersOnly = () => {
  const numbersOnlyShortTextQuestion = {
    ...shortTextQuestion,
    l10ns: {
      ...shortTextQuestion.l10ns,
      en: {
        ...shortTextQuestion.l10ns.en,
        question: 'Short Text (Numbers Only)',
      },
    },
    attributes: {
      numbers_only: {
        value: '1',
        qaid: 1,
      },
    },
  }

  return (
    <TextQuestion
      question={numbersOnlyShortTextQuestion}
      handleUpdate={() => {}}
    />
  )
}

NumbersOnly.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputField = canvas.getByTestId('text-question-answer-input')

  await step('Expect the question input type to be of type number', () => {
    expect(inputField.attributes.type.value).toBe('number')
  })

  await step('Expect the question input to accept only numbers', () => {
    userEvent.type(inputField, 'random text')
    expect(inputField.value).toBe('')
    userEvent.type(inputField, '1999')
    expect(inputField.value).toBe('1999')
  })
}

export const NumbersOnlyMaxFiveNumbers = () => {
  const numbersOnlyShortTextQuestion = {
    ...shortTextQuestion,
    l10ns: {
      ...shortTextQuestion.l10ns,
      en: {
        ...shortTextQuestion.l10ns.en,
        question: 'Short Text (Numbers Only, Max 5 Numbers)',
      },
    },
    attributes: {
      numbers_only: {
        value: '1',
        qaid: 1,
      },
      maximum_chars: {
        value: '5',
        qaid: 1,
      },
    },
  }

  return (
    <TextQuestion
      question={numbersOnlyShortTextQuestion}
      handleUpdate={() => {}}
    />
  )
}

NumbersOnlyMaxFiveNumbers.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputField = canvas.getByTestId('text-question-answer-input')

  await step('Expect the question input type to be of type number', () => {
    expect(inputField.attributes.type.value).toBe('number')
  })

  await step(
    'Expect the question input to accept no more than 5 numbers',
    () => {
      userEvent.type(inputField, '123456789')
      expect(inputField.value).toBe('12345')
    }
  )
}
