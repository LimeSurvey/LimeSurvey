import React, { useEffect } from 'react'
import { within } from '@storybook/test'
import { expect } from '@storybook/test'

import { useAppState, useSurvey } from 'hooks'

import { SurveyStructure } from '../../SurveyStructure'
import { sleep } from 'helpers/sleep'

export default {
  title: 'General/SurveyStructure',
  component: SurveyStructure,
}

let Survey

export const Basic = () => {
  const { survey } = useSurvey()
  Survey = survey

  const [, editorStructurePanelOpen] = useAppState(
    'editorStructurePanelOpen',
    true
  )

  useEffect(() => {
    editorStructurePanelOpen(true)
  }, [])

  return <SurveyStructure survey={survey} />
}

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  const header = await canvas.findByTestId('survey-structure-header')
  const footer = await canvas.findByTestId('survey-structure-footer')
  const questionGroups = await canvas.findAllByTestId(
    'survey-structure-question-group'
  )

  await step('Should have the Header and Footer.', async () => {
    await expect(header).toBeDefined()
    await expect(footer).toBeDefined()
    await sleep()
  })

  await step('Should have all of the survey question groups.', async () => {
    await expect(questionGroups.length).toBe(Survey?.questionGroups.length)
  })
}
