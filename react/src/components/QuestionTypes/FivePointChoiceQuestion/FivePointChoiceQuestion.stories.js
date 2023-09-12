import React from 'react'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { Language } from 'helpers'
import { QuestionTypeInfo } from 'components/Survey/QuestionTypes'

import { Question } from '../../Questions/Question'
import { FivePointChoiceQuestion } from './FivePointChoiceQuestion'
import { FivePointChoiceAnswerType } from './FivePointChoiceAnswertype'

const _question = {
  gid: 1,
  qid: 1,
  sid: 1,
  type: QuestionTypeInfo.FIVE_POINT_CHOICE,
  l10ns: {
    en: {
      id: 1,
      language: Language.EN,
      qid: 1,
      question: 'Hello there',
      script: '',
    },
  },
  answers: {
    aid: 1,
    assessmentValue: -1,
    code: 'A1',
    qid: 1,
    scaleId: 1,
  },
}

export default {
  title: 'QuestionTypes/FivePointChoiceQuestion',
  component: FivePointChoiceQuestion,
}

export const QuestionPreview = () => {
  const [question, setQuestion] = React.useState(_question)

  const handleUpdate = (change) => {
    const updatedQuestion = change

    question.answers.assessmentValue = change.answers.assessmentValue

    setQuestion({ ...question, ...updatedQuestion })
  }

  return (
    <Question
      handleRemove={() => {}}
      question={question}
      update={handleUpdate}
      language={Language.EN}
    />
  )
}

export const Basic = () => {
  const [question, setQuestion] = React.useState({
    ..._question,
    mandatory: false,
  })

  const handleUpdate = (change) => {
    const updatedQuestion = change

    question.answers.assessmentValue = change.answers.assessmentValue

    setQuestion({ ...question, ...updatedQuestion })
  }

  return (
    <FivePointChoiceQuestion question={question} handleUpdate={handleUpdate} />
  )
}

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputFields = canvas.getAllByTestId('five-point-choice-question-answer')

  await step('Expect the question to have 6 answer options', () => {
    expect(inputFields.length).toBe(6)
  })

  await step('Expect the last answer option to have value NO_ANSWER', () => {
    expect(+inputFields[5].value).toBe(FivePointChoiceAnswerType.NO_ANSWER)
  })

  await fivePointChoiceQuestionsTests(canvas, step)
}

export const Mandatory = () => {
  const [question, setQuestion] = React.useState({
    ..._question,
    mandatory: true,
  })

  const handleUpdate = (change) => {
    const updatedQuestion = change

    question.answers.assessmentValue = change.answers.assessmentValue

    setQuestion({ ...question, ...updatedQuestion })
  }

  return (
    <FivePointChoiceQuestion question={question} handleUpdate={handleUpdate} />
  )
}

Mandatory.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const inputFields = canvas.getAllByTestId('five-point-choice-question-answer')

  await step('Expect the question to have 5 answer options', () => {
    expect(inputFields.length).toBe(5)
  })

  await step('Expect the last answer option to have value FIVE_POINTS', () => {
    expect(+inputFields[4].value).toBe(FivePointChoiceAnswerType.FIVE_POINTS)
  })

  await fivePointChoiceQuestionsTests(canvas, step)
}

const fivePointChoiceQuestionsTests = async (canvas, step) => {
  const inputFields = canvas.getAllByTestId('five-point-choice-question-answer')
  const firstInputFieldName = inputFields[0]?.name

  await step('Expect input fields to be of type radio', () => {
    inputFields.forEach((inputField) => {
      expect(inputField.type).toBe('radio')
    })
  })

  await step('Expect input fields to have the same name', () => {
    inputFields.forEach((inputField) => {
      expect(inputField.name).toBe(firstInputFieldName)
    })
  })

  await step('Should be able to choose the first choice', () => {
    userEvent.click(inputFields[0])
    expect(inputFields[0].checked).toBe(true)

    inputFields.forEach((inputField, index) => {
      if (index !== 0) {
        expect(inputField.checked).toBe(false)
      }
    })
  })

  await step(
    'Should be able to choose the last choice after choosing an option',
    () => {
      const lastInptField = inputFields[inputFields.length - 1]
      userEvent.click(lastInptField)
      expect(lastInptField.checked).toBe(true)

      inputFields.forEach((inputField, index) => {
        if (index !== inputFields.length - 1) {
          expect(inputField.checked).toBe(false)
        }
      })
    }
  )
}
