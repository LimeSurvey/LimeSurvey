import React, { useEffect } from 'react'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { useAppState, useSurvey } from 'hooks'

import { SurveyStructure } from './SurveyStructure'
import { RowQuestionGroup as RowQuestionGroupComponent } from './RowQuestionGroup'

export default {
  title: 'General/SurveyStructure',
  component: SurveyStructure,
}

const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'
let Survey

export const Basic = () => {
  const { survey, update } = useSurvey(surveyId)
  Survey = survey

  const [, editorStructurePanelOpen] = useAppState(
    'editorStructurePanelOpen',
    true
  )

  const handleUpdate = (questionGroups) => {
    update({ questionGroups })
  }

  useEffect(() => {
    editorStructurePanelOpen(true)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <SurveyStructure
      survey={survey}
      update={(questionGroups) => handleUpdate(questionGroups)}
    />
  )
}

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  const header = await canvas.findByTestId('survey-structure-header')
  const footer = await canvas.findByTestId('survey-structure-footer')
  const questionGroups = await canvas.findAllByTestId(
    'survey-structure-question-group'
  )

  await step('Should have the Header and Footer.', () => {
    expect(header).toBeDefined()
    expect(footer).toBeDefined()
  })

  await step('Should have all of the survey question groups.', () => {
    expect(questionGroups.length).toBe(Survey?.questionGroups.length)
  })

  await step('Should have the question groups listed in order', () => {
    questionGroups.forEach((questionGroup, index) => {
      expect(+questionGroup.dataset.ordervalue).toBe(index + 1)
    })
  })
}

export const RowQuestionGroup = () => {
  const { survey, update } = useSurvey(surveyId)
  Survey = survey

  const handleUpdate = (changes) => {
    survey.questionGroups[0] = changes
    update({ ...survey.questionGroups })
  }

  return (
    survey?.questionGroups && (
      <RowQuestionGroupComponent
        questionGroup={survey.questionGroups[0]}
        language={survey.language}
        update={(changes) => {
          handleUpdate(changes)
        }}
      />
    )
  )
}

RowQuestionGroup.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const togglerButton = await canvas.findByTestId('sidebar-row-toggler-button')

  await step('Should be able to expand the list', async () => {
    expect(
      canvas.queryAllByTestId('row-question-group-question', {
        exact: false,
      }).length
    ).toBe(0)

    await userEvent.click(togglerButton)

    expect(
      canvas.queryAllByTestId('row-question-group-question', {
        exact: false,
      }).length
    ).toBe(Survey.questionGroups[0].questions.length)
  })

  await step('Should have the questions listed in order', async () => {
    const questions = await canvas.findAllByTestId(
      'row-question-group-question',
      {
        exact: false,
      }
    )

    questions.forEach((question, index) => {
      expect(+question.parentElement.dataset.questionorder).toBe(index + 1)
    })
  })

  await step('Should be able to collapse the list', async () => {
    expect(
      canvas.queryAllByTestId('row-question-group-question', {
        exact: false,
      }).length
    ).toBe(Survey.questionGroups[0].questions.length)

    await userEvent.click(togglerButton)

    expect(
      canvas.queryAllByTestId('row-question-group-question', {
        exact: false,
      }).length
    ).toBe(0)
  })
}
